<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai;

use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Exception\ApiException;
use OpenAI\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class OpenAiConnector
 *
 * Provides OpenAI chat and embedding operations for shared usage.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class OpenAiConnector implements AiConnectorInterface
{
    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * Runtime client cache.
     *
     * @var array<string, \OpenAI\Client>
     */
    protected array $clients = [];


    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger Logger.
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'openai';
    }


    /**
     * @inheritDoc
     */
    public function embed(AiConnectionConfigurationInterface $connection, EmbeddingRequest $request): EmbeddingResponse
    {
        /** @var string $configuredModel */
        $configuredModel = $this->resolveEmbeddingModel($connection, $request);

        try {
            /** @var object $response */
            $response = $this->createClient($connection)->embeddings()->create(array_merge([
                'model' => $configuredModel,
                'input' => $request->getText(),
                'temperature' => $this->resolveEmbeddingTemperature($connection, $request),
            ], $connection->getAdditionalOptionsArray(), $request->getOptions()));

            /** @var array<int, float> $embedding */
            $embedding = $response->embeddings[0]->embedding ?? [];

            return new EmbeddingResponse(
                $embedding,
                $configuredModel,
                method_exists($response, 'toArray') ? $response->toArray() : []
            );
        } catch (\Throwable $exception) {
            $this->logger->error('OpenAI embedding failed', [
                'operation' => 'embed',
                'configured_model' => $configuredModel,
                'exception' => $exception,
            ]);
            throw new ApiException('Embedding failed: ' . $exception->getMessage(), 1780572973, $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function embedBatch(AiConnectionConfigurationInterface $connection, array $requests): array
    {
        if ($requests === []) {
            return [];
        }

        /** @var \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $firstRequest */
        $firstRequest = reset($requests);

        /** @var string $configuredModel */
        $configuredModel = $this->resolveEmbeddingModel($connection, $firstRequest);

        /** @var array<int, string> $texts */
        $texts = array_map(
            static fn (EmbeddingRequest $request): string => $request->getText(),
            $requests
        );

        try {
            /** @var object $response */
            $response = $this->createClient($connection)->embeddings()->create(array_merge([
                'model' => $configuredModel,
                'input' => $texts,
                'temperature' => $this->resolveEmbeddingTemperature($connection, $firstRequest),
            ], $connection->getAdditionalOptionsArray(), $firstRequest->getOptions()));

            return $this->buildEmbeddingResponses($response, $configuredModel);
        } catch (\Throwable $exception) {
            $this->logger->error('OpenAI batch embedding failed', [
                'operation' => 'embedBatch',
                'configured_model' => $configuredModel,
                'exception' => $exception,
            ]);
            throw new ApiException('Batch embedding failed: ' . $exception->getMessage(), 1780572973, $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function streamChat(AiConnectionConfigurationInterface $connection, AiRequest $request, callable $onData): void
    {
        try {
            /** @var array<string, mixed> $payload */
            $payload = $this->buildChatPayload($connection, $request);

            /** @var iterable<object> $stream */
            $stream = $this->createClient($connection)->chat()->createStreamed($payload);

            foreach ($stream as $event) {
                if (isset($event->choices[0]->delta->content)) {
                    $onData($event->choices[0]->delta->content);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error('OpenAI chat streaming failed', [
                'operation' => 'streamChat',
                'model' => $this->resolveChatModel($connection, $request),
                'message_count' => count($request->getMessages()),
                'exception' => $exception,
            ]);
            throw new ApiException('Chat streaming failed: ' . $exception->getMessage(), 1780572973, $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function chat(AiConnectionConfigurationInterface $connection, AiRequest $request): AiResponse
    {
        try {
            /** @var object $response */
            $response = $this->createClient($connection)->chat()->create($this->buildChatPayload($connection, $request));

            /** @var array<string, mixed> $rawResponse */
            $rawResponse = method_exists($response, 'toArray') ? $response->toArray() : [];

            return new AiResponse(
                (string)($response->choices[0]->message->content ?? ''),
                $rawResponse
            );
        } catch (\Throwable $exception) {
            $this->logger->error('OpenAI chat request failed', [
                'operation' => 'chat',
                'model' => $this->resolveChatModel($connection, $request),
                'message_count' => count($request->getMessages()),
                'exception' => $exception,
            ]);
            throw new ApiException('Chat request failed: ' . $exception->getMessage(), 1780572973, $exception);
        }
    }


    /**
     * Creates an OpenAI client for the given connection.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @return \OpenAI\Client OpenAI client.
     */
    protected function createClient(AiConnectionConfigurationInterface $connection): Client
    {
        /** @var string $cacheKey */
        $cacheKey = sha1(implode('|', [
            $connection->getApiKey(),
            $connection->getBaseUrl(),
            $connection->getOrganization(),
            $connection->getProject(),
        ]));

        if (isset($this->clients[$cacheKey])) {
            return $this->clients[$cacheKey];
        }

        if ($connection->getApiKey() === '') {
            throw new ApiException('Missing OpenAI API key in selected AI connection.', 1780573101);
        }

        /** @var \OpenAI\Factory $factory */
        $factory = \OpenAI::factory()->withApiKey($connection->getApiKey());

        if ($connection->getBaseUrl() !== '') {
            $factory = $factory->withBaseUri($connection->getBaseUrl());
        }

        if ($connection->getOrganization() !== '') {
            $factory = $factory->withOrganization($connection->getOrganization());
        }

        if ($connection->getProject() !== '') {
            $factory = $factory->withProject($connection->getProject());
        }

        $this->clients[$cacheKey] = $factory->make();

        return $this->clients[$cacheKey];
    }


    /**
     * Builds the chat payload.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return array<string, mixed> Chat payload.
     */
    protected function buildChatPayload(AiConnectionConfigurationInterface $connection, AiRequest $request): array
    {
        return array_merge([
            'model' => $this->resolveChatModel($connection, $request),
            'messages' => $request->toMessageArray(),
            'temperature' => $this->resolveChatTemperature($connection, $request),
            'max_completion_tokens' => $request->getMaxTokens(),
        ], $connection->getAdditionalOptionsArray(), $request->getOptions());
    }


    /**
     * Resolves the chat model.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return string Chat model.
     */
    protected function resolveChatModel(AiConnectionConfigurationInterface $connection, AiRequest $request): string
    {
        if ($request->getModel() !== '') {
            return $request->getModel();
        }

        return $connection->getDefaultModel();
    }


    /**
     * Resolves the chat temperature.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return float Chat temperature.
     */
    protected function resolveChatTemperature(AiConnectionConfigurationInterface $connection, AiRequest $request): float
    {
        if ($request->getTemperature() !== null) {
            return $request->getTemperature();
        }

        return $connection->getDefaultTemperature();
    }


    /**
     * Resolves the embedding model.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $request Embedding request.
     * @return string Embedding model.
     */
    protected function resolveEmbeddingModel(AiConnectionConfigurationInterface $connection, EmbeddingRequest $request): string
    {
        if ($request->getModel() !== '') {
            return $request->getModel();
        }

        return $connection->getEmbeddingModel();
    }


    /**
     * Resolves the embedding temperature.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $request Embedding request.
     * @return float Embedding temperature.
     */
    protected function resolveEmbeddingTemperature(AiConnectionConfigurationInterface $connection, EmbeddingRequest $request): float
    {
        if ($request->getTemperature() !== null) {
            return $request->getTemperature();
        }

        return $connection->getEmbeddingTemperature();
    }


    /**
     * Builds embedding response DTOs.
     *
     * @param object $response Response object.
     * @param string $model Model.
     * @return array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse> Embedding responses.
     */
    protected function buildEmbeddingResponses(object $response, string $model): array
    {
        /** @var array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse> $responses */
        $responses = [];

        foreach ($response->embeddings ?? [] as $embeddingData) {
            $responses[] = new EmbeddingResponse(
                $embeddingData->embedding ?? [],
                $model,
                method_exists($response, 'toArray') ? $response->toArray() : []
            );
        }

        return $responses;
    }
}
