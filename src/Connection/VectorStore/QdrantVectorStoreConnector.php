<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\VectorStore;

use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Factory\QdrantClientFactory;
use Madj2k\AiCore\Connection\Factory\QdrantClientFactoryInterface;
use Madj2k\AiCore\Connection\Resilience\RetryExecutor;
use Madj2k\AiCore\Connection\Resilience\RetryExhaustedException;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorWriteResult;
use Madj2k\AiCore\Exception\VectorDatabaseException;
use Psr\Log\LoggerInterface;
use Qdrant\Models\Filter\Condition\MatchString;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\PointStruct;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant as Client;
use Psr\Log\NullLogger;

/**
 * Class QdrantVectorStoreConnector
 *
 * Provides shared Qdrant vector store operations.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class QdrantVectorStoreConnector implements VectorStoreConnectorInterface
{
    /**
     * Runtime client cache.
     *
     * @var array<string, \Qdrant\Qdrant>
     */
    protected array $clients = [];

    /** @var array<string, true> */
    protected array $validatedCollections = [];

    protected QdrantClientFactoryInterface $clientFactory;

    protected RetryPolicy $retryPolicy;

    protected RetryExecutor $retryExecutor;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger Logger.
     * @param \Madj2k\AiCore\Connection\Factory\QdrantClientFactoryInterface|null $clientFactory Client factory.
     * @param \Madj2k\AiCore\Connection\Resilience\RetryPolicy|null $retryPolicy Retry and timeout policy.
     */
    public function __construct(
        ?LoggerInterface $logger = null,
        ?QdrantClientFactoryInterface $clientFactory = null,
        ?RetryPolicy $retryPolicy = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->clientFactory = $clientFactory ?? new QdrantClientFactory();
        $this->retryPolicy = $retryPolicy ?? new RetryPolicy();
        $this->retryExecutor = new RetryExecutor(
            $this->retryPolicy,
            logger: $this->logger,
        );
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'qdrant';
    }


    /**
     * Creates a Qdrant client for the given connection.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @return \Qdrant\Qdrant Qdrant client.
     */
    protected function createClient(VectorStoreConnectionConfigurationInterface $connection): Client
    {
        if ($connection->getEndpoint() === '') {
            throw new VectorDatabaseException('Missing endpoint in selected vector store connection.', 1780573201);
        }

        /** @var string $cacheKey */
        $cacheKey = sha1($connection->getEndpoint() . '|' . $connection->getApiKey());

        if (isset($this->clients[$cacheKey])) {
            return $this->clients[$cacheKey];
        }

        $this->clients[$cacheKey] = $this->clientFactory->create($connection, $this->retryPolicy);

        return $this->clients[$cacheKey];
    }


    /**
     * @inheritDoc
     */
    public function ensureCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): bool
    {
        $cacheKey = $this->createCollectionCacheKey($connection, $collection);
        if (isset($this->validatedCollections[$cacheKey])) {
            return true;
        }

        try {
            /** @var \Qdrant\Response $response */
            $response = $this->executeRequest(
                'ensureCollection.exists',
                fn () => $this->createClient($connection)->collections($collection->getName())->exists(),
            );

            if ($response->offsetExists('result')) {
                /** @var mixed $result */
                $result = $response->offsetGet('result');

                if (is_array($result) && !empty($result['exists'])) {
                    /** @var \Qdrant\Response $infoResponse */
                    $infoResponse = $this->executeRequest(
                        'ensureCollection.info',
                        fn () => $this->createClient($connection)
                            ->collections($collection->getName())
                            ->info(),
                    );
                    $this->validateCollectionConfiguration($infoResponse, $collection);
                    $this->validatedCollections[$cacheKey] = true;
                    return true;
                }

                /** @var \Qdrant\Models\Request\CreateCollection $createCollection */
                $createCollection = new CreateCollection();
                $createCollection->addVector(
                    new VectorParams(
                        $collection->getVectorSize(),
                        $collection->getDistance(),
                    ),
                    $collection->getName()
                );

                /** @var \Qdrant\Response $createResponse */
                $createResponse = $this->executeRequest(
                    'ensureCollection.create',
                    fn () => $this->createClient($connection)
                        ->collections($collection->getName())
                        ->create($createCollection),
                );

                if ($createResponse->offsetExists('result')) {
                    /** @var bool $created */
                    $created = (bool)$createResponse->offsetGet('result');

                    if ($created) {
                        $this->validatedCollections[$cacheKey] = true;
                        $this->logger->info('Qdrant collection created', [
                            'collection' => $collection->getName(),
                            'vector_size' => $collection->getVectorSize(),
                        ]);
                    }

                    return $created;
                }
            }

            $this->logger->error('Qdrant collection exists check returned no result', [
                'collection' => $collection->getName(),
            ]);

            return false;
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant ensureCollection failed', [
                'collection' => $collection->getName(),
                'vector_size' => $collection->getVectorSize(),
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('ensureCollection', $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function upsert(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, array $documents): VectorWriteResult
    {
        try {
            $this->ensureCollection($connection, $collection);

            /** @var \Qdrant\Models\PointsStruct $points */
            $points = new PointsStruct();

            /** @var int $written */
            $written = 0;

            foreach ($documents as $document) {
                if (!$document instanceof VectorDocument) {
                    continue;
                }

                /** @var string $vectorName */
                $vectorName = $document->getVectorName() !== ''
                    ? $document->getVectorName()
                    : $collection->getName();

                $points->addPoint(
                    new PointStruct(
                        $document->getId(),
                        new VectorStruct($document->getVector(), $vectorName),
                        $document->getPayload()
                    )
                );

                $written++;
            }

            if ($written === 0) {
                return new VectorWriteResult(0);
            }

            /** @var mixed $response */
            $response = $this->executeRequest(
                'upsert',
                fn () => $this->createClient($connection)
                    ->collections($collection->getName())
                    ->points()
                    ->upsert($points, ['wait' => 'true', 'ordering' => 'strong']),
            );

            return new VectorWriteResult($written, $response);
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant upsert failed', [
                'collection' => $collection->getName(),
                'vector_count' => count($documents),
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('upsert', $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function search(VectorStoreConnectionConfigurationInterface $connection, VectorSearchRequest $request): array
    {
        /** @var string $vectorName */
        $vectorName = $request->getVectorName() !== ''
            ? $request->getVectorName()
            : $request->getCollection();

        /** @var array<string, mixed> $params */
        $params = $request->getParams();

        /** @var array<string, mixed>|null $filter */
        $filter = is_array($params['filter'] ?? null) ? $params['filter'] : null;
        unset($params['filter']);

        /** @var int $requestLimit */
        $requestLimit = $filter !== null
            ? max($request->getLimit(), min(200, $request->getLimit() * 20))
            : $request->getLimit();

        try {
            /** @var \Qdrant\Models\Request\SearchRequest $searchRequest */
            $searchRequest = new SearchRequest(new VectorStruct($request->getVector(), $vectorName));
            $searchRequest
                ->setLimit($requestLimit)
                ->setParams($params)
                ->setWithPayload($request->getWithPayload())
                ->setWithVector($request->getWithVector());

            /** @var \Qdrant\Response $response */
            $response = $this->executeRequest(
                'search',
                fn () => $this->createClient($connection)
                    ->collections($request->getCollection())
                    ->points()
                    ->search($searchRequest),
            );
        } catch (\Throwable $exception) {
            if (str_contains($exception->getMessage(), 'doesn\'t exist')) {
                return [];
            }

            $this->logger->error('Qdrant search failed', [
                'collection' => $request->getCollection(),
                'limit' => $request->getLimit(),
                'vector_name' => $vectorName,
                'with_payload' => $request->getWithPayload(),
                'with_vector' => $request->getWithVector(),
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('search', $exception);
        }

        if (!$response->offsetExists('result')) {
            return [];
        }

        /** @var array<int, \Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchResult> $results */
        $results = [];

        foreach ($response['result'] as $item) {
            /** @var array<string, mixed> $payload */
            $payload = is_array($item['payload'] ?? null) ? $item['payload'] : [];

            $results[] = new VectorSearchResult(
                (string)($item['id'] ?? ''),
                (float)($item['score'] ?? 0.0),
                $payload
            );
        }

        if ($filter !== null) {
            $results = array_values(array_filter(
                $results,
                fn (VectorSearchResult $result): bool => $this->matchesFilter($result->getPayload(), $filter)
            ));
        }

        return array_slice($results, 0, $request->getLimit());
    }


    /**
     * @inheritDoc
     */
    public function listCollections(VectorStoreConnectionConfigurationInterface $connection): array
    {
        try {
            /** @var \Qdrant\Response $response */
            $response = $this->executeRequest(
                'listCollections',
                fn () => $this->createClient($connection)->collections()->list(),
            );

            /** @var mixed $result */
            $result = $response->offsetExists('result') ? $response->offsetGet('result') : [];
            /** @var mixed $collections */
            $collections = is_array($result) ? ($result['collections'] ?? []) : [];

            /** @var array<int, string> $names */
            $names = [];
            foreach ((array)$collections as $collection) {
                if (!is_array($collection)) {
                    continue;
                }

                $name = trim((string)($collection['name'] ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                }
            }

            sort($names);
            return array_values(array_unique($names));
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant list collections failed', [
                'endpoint' => $connection->getEndpoint(),
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('listCollections', $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function deleteBySourceHash(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, string $sourceHash): VectorDeleteResult
    {
        try {

            $this->ensureCollection($connection, $collection);

            /** @var string $sourceHash */
            $sourceHash = trim($sourceHash);

            if ($sourceHash === '') {
                return new VectorDeleteResult(0);
            }

            /** @var \Qdrant\Models\Filter\Filter $filter */
            $filter = (new Filter())
            ->addMust(new MatchString('meta.source_hash', $sourceHash));


            /** @var mixed $response */
            $response = $this->executeRequest(
                'deleteBySourceHash',
                fn () => $this->createClient($connection)
                    ->collections($collection->getName())
                    ->points()
                    ->deleteByFilter($filter),
            );

            return new VectorDeleteResult(0, $response);
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant delete by source hash failed', [
                'collection' => $collection->getName(),
                'source_hash' => $sourceHash,
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('deleteBySourceHash', $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function deleteObsoleteSourceGenerations(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
        string $sourceHash,
        string $indexGeneration,
    ): VectorDeleteResult {
        try {
            $this->ensureCollection($connection, $collection);

            $sourceHash = trim($sourceHash);
            $indexGeneration = trim($indexGeneration);
            if ($sourceHash === '' || $indexGeneration === '') {
                return new VectorDeleteResult(0);
            }

            $filter = (new Filter())
                ->addMust(new MatchString('meta.source_hash', $sourceHash))
                ->addMustNot(new MatchString('meta.index_generation', $indexGeneration));

            $response = $this->executeRequest(
                'deleteObsoleteSourceGenerations',
                fn () => $this->createClient($connection)
                    ->collections($collection->getName())
                    ->points()
                    ->deleteByFilter($filter),
            );

            return new VectorDeleteResult(0, $response);
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant obsolete source generation cleanup failed', [
                'collection' => $collection->getName(),
                'source_hash' => $sourceHash,
                'index_generation' => $indexGeneration,
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('deleteObsoleteSourceGenerations', $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function deleteCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): VectorDeleteResult
    {
        try {
            /** @var mixed $response */
            $response = $this->executeRequest(
                'deleteCollection',
                fn () => $this->createClient($connection)->collections($collection->getName())->delete(),
            );
            $this->validatedCollections = [];

            return new VectorDeleteResult(0, $response);
        } catch (\Throwable $exception) {
            $this->logger->error('Qdrant delete collection failed', [
                'collection' => $collection->getName(),
                'exception' => $exception,
            ]);
            throw $this->createVectorDatabaseException('deleteCollection', $exception);
        }
    }


    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    protected function executeRequest(string $operationName, callable $operation): mixed
    {
        return $this->retryExecutor->execute('qdrant', $operationName, $operation);
    }


    protected function createVectorDatabaseException(
        string $operation,
        \Throwable $exception,
    ): VectorDatabaseException {
        if ($exception instanceof VectorDatabaseException) {
            return $exception;
        }

        if ($exception instanceof RetryExhaustedException) {
            return new VectorDatabaseException(
                'Qdrant ' . $operation . ' failed: ' . $exception->getMessage(),
                1780572973,
                $exception->getPrevious() ?? $exception,
                $exception->getProvider(),
                $exception->getOperation(),
                $exception->getStatusCode(),
                $exception->isRetryable(),
                $exception->getAttempts(),
            );
        }

        return new VectorDatabaseException(
            'Qdrant ' . $operation . ' failed: ' . $exception->getMessage(),
            1780572973,
            $exception,
            'qdrant',
            $operation,
            null,
            false,
        );
    }


    /**
     * Validates the vector parameters of an existing collection.
     *
     * @param \Qdrant\Response $response Collection info response.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Expected collection.
     */
    private function validateCollectionConfiguration(\Qdrant\Response $response, VectorCollection $collection): void
    {
        $result = $response->offsetExists('result') ? $response->offsetGet('result') : null;
        $vectors = is_array($result) ? ($result['config']['params']['vectors'] ?? null) : null;
        $vectorParams = is_array($vectors) ? ($vectors[$collection->getName()] ?? null) : null;
        if (!is_array($vectorParams)) {
            throw new \UnexpectedValueException(sprintf(
                'Qdrant collection "%s" does not contain the expected named vector configuration.',
                $collection->getName(),
            ));
        }

        $actualVectorSize = (int)($vectorParams['size'] ?? 0);
        $actualDistance = trim((string)($vectorParams['distance'] ?? ''));
        if (
            $actualVectorSize !== $collection->getVectorSize()
            || strcasecmp($actualDistance, $collection->getDistance()) !== 0
        ) {
            throw new \UnexpectedValueException(sprintf(
                'Qdrant collection "%s" uses vector size %d and distance "%s"; expected %d and "%s". Recreate the collection and reindex its documents.',
                $collection->getName(),
                $actualVectorSize,
                $actualDistance,
                $collection->getVectorSize(),
                $collection->getDistance(),
            ));
        }
    }


    /** Creates the cache key for one validated collection configuration. */
    private function createCollectionCacheKey(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
    ): string {
        return sha1(implode('|', [
            $connection->getEndpoint(),
            $connection->getApiKey(),
            $collection->getName(),
            (string)$collection->getVectorSize(),
            $collection->getDistance(),
        ]));
    }


    /**
     * Checks whether a payload matches the filter.
     *
     * @param array<string, mixed> $payload Payload.
     * @param array<string, mixed> $filter Filter.
     * @return bool True if filter matches.
     */
    protected function matchesFilter(array $payload, array $filter): bool
    {
        /** @var mixed $must */
        $must = $filter['must'] ?? null;

        if (!is_array($must)) {
            return true;
        }

        foreach ($must as $condition) {
            if (!is_array($condition)) {
                continue;
            }

            /** @var string $key */
            $key = (string)($condition['key'] ?? '');

            if ($key === '') {
                continue;
            }

            /** @var mixed $value */
            $value = $this->getPayloadValueByPath($payload, $key);

            /** @var mixed $matchConfig */
            $matchConfig = $condition['match'] ?? [];

            if (is_array($matchConfig) && array_key_exists('any', $matchConfig)) {
                /** @var array<int, mixed> $allowedValues */
                $allowedValues = is_array($matchConfig['any']) ? $matchConfig['any'] : [$matchConfig['any']];

                /** @var bool $matchesAny */
                $matchesAny = false;

                foreach ($allowedValues as $allowedValue) {
                    if ((string)$value === (string)$allowedValue) {
                        $matchesAny = true;
                        break;
                    }
                }

                if (!$matchesAny) {
                    return false;
                }

                continue;
            }

            /** @var mixed $match */
            $match = is_array($matchConfig) ? ($matchConfig['value'] ?? null) : null;

            if ((string)$value !== (string)$match) {
                return false;
            }
        }

        return true;
    }


    /**
     * Returns a nested payload value by dot path.
     *
     * @param array<string, mixed> $payload Payload.
     * @param string $path Dot path.
     * @return mixed Payload value.
     */
    protected function getPayloadValueByPath(array $payload, string $path): mixed
    {
        /** @var array<int, string> $segments */
        $segments = array_filter(explode('.', $path), static fn (string $segment): bool => $segment !== '');

        /** @var mixed $current */
        $current = $payload;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }
}
