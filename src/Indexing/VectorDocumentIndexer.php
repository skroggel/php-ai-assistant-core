<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing;

use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\Identity\SourceIdentityGenerator;

/**
 * Class VectorDocumentIndexer
 *
 * Chunks source documents, creates embeddings and replaces their vectors in a vector store.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class VectorDocumentIndexer
{
    /**
     * @param \Madj2k\AiCore\Connection\Resolver\AiConnectorResolver $aiConnectorResolver AI connector resolver.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorResolver Vector store connector resolver.
     * @param \Madj2k\AiCore\Indexing\TextChunker $textChunker Text chunker.
     * @param \Madj2k\AiCore\Indexing\Identity\SourceIdentityGenerator $sourceIdentityGenerator Source identity generator.
     */
    public function __construct(
        private AiConnectorResolver $aiConnectorResolver,
        private VectorStoreConnectorResolver $vectorStoreConnectorResolver,
        private TextChunker $textChunker,
        private SourceIdentityGenerator $sourceIdentityGenerator,
    ) {
    }

    /**
     * Resolves the collection from an explicit override, indexing configuration or connection default.
     */
    public function resolveCollection(
        IndexingConfigurationInterface $configuration,
        string $collectionOverride = '',
    ): string {
        $collectionOverride = trim($collectionOverride);
        if ($collectionOverride !== '') {
            return $collectionOverride;
        }

        $collection = trim($configuration->getCollection());
        if ($collection !== '') {
            return $collection;
        }

        return trim($configuration->getVectorStoreConnection()?->getDefaultCollection() ?? '');
    }

    /**
     * Indexes one document and returns the number of written vector chunks.
     *
     * New vectors are written before obsolete source generations are removed.
     * A dry run performs chunking only and returns the number of chunks that would be processed.
     *
     * @param array<int, string> $sourceHashesToDelete Previously stored source hashes.
     * @throws \InvalidArgumentException When the collection name is empty.
     * @throws \RuntimeException When an AI or vector store connection is missing.
     * @throws \Throwable When connector resolution or a provider request fails.
     */
    public function index(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collectionName,
        bool $dryRun = false,
        array $sourceHashesToDelete = [],
    ): int {
        $collectionName = trim($collectionName);
        if ($collectionName === '') {
            throw new \InvalidArgumentException('Collection name must not be empty.', 1781002001);
        }

        $chunks = $this->textChunker->chunk(
            $document->getContent(),
            $this->positiveOrNull($configuration->getChunkSize()),
            $this->positiveOrNull($configuration->getChunkOverlap()),
            $this->positiveOrNull($configuration->getMaxChunks()),
            $this->positiveOrNull($configuration->getMinChunkChars()),
        );

        if ($chunks === []) {
            return 0;
        }

        if ($dryRun) {
            return count($chunks);
        }

        $aiConnection = $configuration->getAiConnection();
        if ($aiConnection === null) {
            throw new \RuntimeException('No AI connection configured for indexer.', 1780573401);
        }

        $vectorStoreConnection = $configuration->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            throw new \RuntimeException('No vector store connection configured for indexer.', 1780573402);
        }

        $embeddingRequests = array_map(
            static fn (string $chunkText): EmbeddingRequest => new EmbeddingRequest($chunkText),
            $chunks,
        );
        $embeddingResponses = $this->aiConnectorResolver
            ->get($aiConnection->getConnectorIdentifier())
            ->embedBatch($aiConnection, $embeddingRequests);

        $actualVectorSize = $this->resolveVectorSize($embeddingResponses);
        if ($actualVectorSize === null) {
            return 0;
        }

        $configuredVectorSize = $vectorStoreConnection->getVectorSize();
        if ($actualVectorSize !== $configuredVectorSize) {
            throw new IndexingException(sprintf(
                'Embedding dimension mismatch: provider returned %d dimensions, vector store connection expects %d.',
                $actualVectorSize,
                $configuredVectorSize,
            ), 1781002002);
        }

        $collection = new VectorCollection(
            $collectionName,
            $actualVectorSize,
            $vectorStoreConnection->getDistance(),
        );
        $sourceHash = $this->sourceIdentityGenerator->createSourceHash($document);
        $indexGeneration = $this->createIndexGeneration($chunks);
        $vectorDocuments = [];
        foreach ($chunks as $index => $chunkText) {
            $embedding = isset($embeddingResponses[$index])
                ? $embeddingResponses[$index]->getEmbedding()
                : [];
            if ($embedding === []) {
                continue;
            }

            $vectorDocuments[] = new VectorDocument(
                id: $this->sourceIdentityGenerator->createVectorDocumentId($sourceHash, $index),
                vector: $embedding,
                payload: $document->createPayload($index, $chunkText, $sourceHash, $indexGeneration),
                vectorName: $collection->getName(),
            );
        }

        if ($vectorDocuments === []) {
            return 0;
        }

        $vectorStoreConnector = $this->vectorStoreConnectorResolver->get(
            $vectorStoreConnection->getConnectorIdentifier(),
        );
        $writeResult = $vectorStoreConnector->upsert($vectorStoreConnection, $collection, $vectorDocuments);

        $sourceHashesToDelete = array_values(array_unique(array_filter(array_map('trim', $sourceHashesToDelete))));
        foreach ($sourceHashesToDelete as $hash) {
            if ($hash === $sourceHash) {
                continue;
            }
            $vectorStoreConnector->deleteBySourceHash($vectorStoreConnection, $collection, $hash);
        }

        $vectorStoreConnector->deleteObsoleteSourceGenerations(
            $vectorStoreConnection,
            $collection,
            $sourceHash,
            $indexGeneration,
        );

        return $writeResult->getWritten();
    }

    /**
     * Returns the common dimension of all non-empty embedding responses.
     *
     * @param array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse> $embeddingResponses Embedding responses.
     * @throws \Madj2k\AiCore\Exception\IndexingException
     */
    private function resolveVectorSize(array $embeddingResponses): ?int
    {
        $vectorSize = null;
        foreach ($embeddingResponses as $index => $embeddingResponse) {
            $embedding = $embeddingResponse->getEmbedding();
            if ($embedding === []) {
                continue;
            }

            $currentSize = count($embedding);
            if ($vectorSize !== null && $currentSize !== $vectorSize) {
                throw new IndexingException(sprintf(
                    'Inconsistent embedding dimensions: response %d has %d dimensions, expected %d.',
                    $index,
                    $currentSize,
                    $vectorSize,
                ), 1781002003);
            }
            $vectorSize = $currentSize;
        }

        return $vectorSize;
    }

    /**
     * Creates a deterministic generation identifier that also changes with chunk boundaries.
     *
     * @param array<int, string> $chunks Document chunks.
     */
    private function createIndexGeneration(array $chunks): string
    {
        $hash = hash_init('sha256');
        foreach ($chunks as $chunk) {
            hash_update($hash, strlen($chunk) . ':' . $chunk);
        }

        return hash_final($hash);
    }

    private function positiveOrNull(int $value): ?int
    {
        return $value > 0 ? $value : null;
    }
}
