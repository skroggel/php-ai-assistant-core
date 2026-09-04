<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai;

use GuzzleHttp\ClientInterface;
use Madj2k\AiCore\Connection\Ai\DTO\AiMessage;
use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Factory\GeminiClientFactory;
use Madj2k\AiCore\Connection\Factory\GeminiClientFactoryInterface;
use Madj2k\AiCore\Connection\Resilience\ExceptionClassifier;
use Madj2k\AiCore\Connection\Resilience\RetryExecutor;
use Madj2k\AiCore\Connection\Resilience\RetryExhaustedException;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Madj2k\AiCore\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class GeminiConnector
 *
 * Provides Gemini chat and embedding operations through the provider REST API.
 *
 * @author Maximilian Fäßer <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class GeminiConnector implements AiConnectorInterface
{
    protected const DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Runtime client cache.
     *
     * @var array<string, \GuzzleHttp\ClientInterface>
     */
    protected array $clients = [];

    protected GeminiClientFactoryInterface $clientFactory;

    protected RetryPolicy $retryPolicy;

    protected RetryExecutor $retryExecutor;

    protected ExceptionClassifier $exceptionClassifier;


    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger Logger.
     * @param \Madj2k\AiCore\Connection\Factory\GeminiClientFactoryInterface|null $clientFactory Client factory.
     * @param \Madj2k\AiCore\Connection\Resilience\RetryPolicy|null $retryPolicy Retry and timeout policy.
     */
    public function __construct(
        ?LoggerInterface $logger = null,
        ?GeminiClientFactoryInterface $clientFactory = null,
        ?RetryPolicy $retryPolicy = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->clientFactory = $clientFactory ?? new GeminiClientFactory();
        $this->retryPolicy = $retryPolicy ?? new RetryPolicy();
        $this->exceptionClassifier = new ExceptionClassifier();
        $this->retryExecutor = new RetryExecutor(
            $this->retryPolicy,
            logger: $this->logger,
        );
    }


    /** @inheritDoc */
    public function getIdentifier(): string
    {
        return 'gemini';
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\ApiException If the Gemini request fails.
     */
    public function chat(AiConnectionConfigurationInterface $connection, AiRequest $request): AiResponse
    {
        $model = $this->resolveChatModel($connection, $request);

        try {
            /** @var array<string, mixed> $rawResponse */
            $rawResponse = $this->retryExecutor->execute(
                'gemini',
                'chat',
                fn (): array => $this->sendJsonRequest(
                    $connection,
                    $this->buildEndpoint($connection, $model, 'generateContent'),
                    $this->buildChatPayload($connection, $request),
                ),
            );

            return new AiResponse(
                $this->extractContent($rawResponse),
                $this->normalizeUsage($rawResponse),
            );
        } catch (RetryExhaustedException $exception) {
            $this->logger->error('Gemini chat request failed', [
                'operation' => 'chat',
                'model' => $model,
                'message_count' => count($request->getMessages()),
                'exception' => $exception,
            ]);
            throw $this->createApiException('Chat request failed', $exception);
        }
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\ApiException If the Gemini stream fails.
     */
    public function streamChat(
        AiConnectionConfigurationInterface $connection,
        AiRequest $request,
        callable $onData,
    ): void {
        $model = $this->resolveChatModel($connection, $request);
        $streamState = new class {
            public bool $emittedData = false;
        };

        try {
            $this->retryExecutor->execute(
                'gemini',
                'streamChat',
                function () use ($connection, $request, $model, $onData, $streamState): void {
                    $response = $this->sendRequest(
                        $connection,
                        $this->buildEndpoint($connection, $model, 'streamGenerateContent') . '?alt=sse',
                        $this->buildChatPayload($connection, $request),
                        true,
                    );
                    $this->assertSuccessfulResponse($response);
                    $this->consumeEventStream(
                        $response,
                        function (array $event) use ($onData, $streamState): void {
                            $content = $this->extractContent($event);
                            if ($content !== '') {
                                $streamState->emittedData = true;
                                $onData($content);
                            }
                        },
                    );
                },
                function (\Throwable $exception) use ($streamState): bool {
                    return !$streamState->emittedData
                        && $this->exceptionClassifier->isRetryable($exception, $this->retryPolicy);
                },
            );
        } catch (RetryExhaustedException $exception) {
            $this->logger->error('Gemini chat streaming failed', [
                'operation' => 'streamChat',
                'model' => $model,
                'message_count' => count($request->getMessages()),
                'exception' => $exception,
            ]);
            throw $this->createApiException('Chat streaming failed', $exception);
        }
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\ApiException If the Gemini embedding request fails.
     */
    public function embed(
        AiConnectionConfigurationInterface $connection,
        EmbeddingRequest $request,
    ): EmbeddingResponse {
        $model = $this->resolveEmbeddingModel($connection, $request);

        try {
            /** @var array<string, mixed> $rawResponse */
            $rawResponse = $this->retryExecutor->execute(
                'gemini',
                'embed',
                fn (): array => $this->sendJsonRequest(
                    $connection,
                    $this->buildEndpoint($connection, $model, 'embedContent'),
                    $this->buildEmbeddingPayload($connection, $request, $model),
                ),
            );

            return new EmbeddingResponse(
                $this->extractEmbedding($rawResponse['embedding'] ?? []),
                $model,
                $rawResponse,
            );
        } catch (RetryExhaustedException $exception) {
            $this->logger->error('Gemini embedding failed', [
                'operation' => 'embed',
                'configured_model' => $model,
                'exception' => $exception,
            ]);
            throw $this->createApiException('Embedding failed', $exception);
        }
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\ApiException If the Gemini batch embedding request fails.
     */
    public function embedBatch(AiConnectionConfigurationInterface $connection, array $requests): array
    {
        if ($requests === []) {
            return [];
        }

        /** @var \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $firstRequest */
        $firstRequest = reset($requests);
        $model = $this->resolveEmbeddingModel($connection, $firstRequest);

        try {
            /** @var array<string, mixed> $rawResponse */
            $rawResponse = $this->retryExecutor->execute(
                'gemini',
                'embedBatch',
                fn (): array => $this->sendJsonRequest(
                    $connection,
                    $this->buildEndpoint($connection, $model, 'batchEmbedContents'),
                    [
                        'requests' => array_map(
                            fn (EmbeddingRequest $request): array => $this->buildEmbeddingPayload(
                                $connection,
                                $request,
                                $model,
                            ),
                            $requests,
                        ),
                    ],
                ),
            );

            /** @var array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse> $responses */
            $responses = [];
            foreach ($rawResponse['embeddings'] ?? [] as $embedding) {
                $responses[] = new EmbeddingResponse(
                    $this->extractEmbedding($embedding),
                    $model,
                    $rawResponse,
                );
            }

            return $responses;
        } catch (RetryExhaustedException $exception) {
            $this->logger->error('Gemini batch embedding failed', [
                'operation' => 'embedBatch',
                'configured_model' => $model,
                'exception' => $exception,
            ]);
            throw $this->createApiException('Batch embedding failed', $exception);
        }
    }


    /**
     * Builds a Gemini chat request payload.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return array<string, mixed> Gemini request payload.
     * @throws \Madj2k\AiCore\Exception\ApiException If a message role is not supported by Gemini.
     */
    protected function buildChatPayload(
        AiConnectionConfigurationInterface $connection,
        AiRequest $request,
    ): array {
        /** @var array<int, array{role: string, parts: array<int, array{text: string}>}> $contents */
        $contents = [];
        /** @var array<int, array{text: string}> $systemParts */
        $systemParts = [];

        foreach ($request->getMessages() as $message) {
            if ($message->getRole() === 'system') {
                $systemParts[] = ['text' => $message->getContent()];
                continue;
            }

            $contents[] = [
                'role' => $this->mapRole($message),
                'parts' => [['text' => $message->getContent()]],
            ];
        }

        /** @var array<string, mixed> $payload */
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->resolveChatTemperature($connection, $request),
                'maxOutputTokens' => $request->getMaxTokens(),
            ],
        ];
        if ($systemParts !== []) {
            $payload['systemInstruction'] = ['parts' => $systemParts];
        }

        return array_replace_recursive(
            $payload,
            $this->resolveConnectionOptions($connection, 'chat'),
            $request->getOptions(),
        );
    }


    /**
     * Builds a Gemini embedding request payload.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $request Embedding request.
     * @param string $model Embedding model.
     * @return array<string, mixed> Gemini request payload.
     * @throws \Madj2k\AiCore\Exception\ApiException If the embedding model is empty.
     */
    protected function buildEmbeddingPayload(
        AiConnectionConfigurationInterface $connection,
        EmbeddingRequest $request,
        string $model,
    ): array {
        return array_replace_recursive([
            'model' => 'models/' . $this->normalizeModel($model),
            'content' => [
                'parts' => [['text' => $request->getText()]],
            ],
        ], $this->resolveConnectionOptions($connection, 'embedding'), $request->getOptions());
    }


    /**
     * Resolves shared and operation-specific Gemini connection options.
     *
     * Options below a `chat` or `embedding` key are only forwarded to the
     * respective API operation. Other top-level options remain shared.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param string $operation Operation option namespace.
     * @return array<string, mixed> Provider options for the operation.
     */
    protected function resolveConnectionOptions(
        AiConnectionConfigurationInterface $connection,
        string $operation,
    ): array {
        $options = $connection->getAdditionalOptionsArray();
        $operationOptions = is_array($options[$operation] ?? null)
            ? $options[$operation]
            : [];

        unset($options['chat'], $options['embedding']);

        return array_replace_recursive($options, $operationOptions);
    }


    /**
     * Maps a normalized message role to a Gemini content role.
     *
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiMessage $message AI message.
     * @return string Gemini content role.
     * @throws \Madj2k\AiCore\Exception\ApiException If the message role is not supported by Gemini.
     */
    protected function mapRole(AiMessage $message): string
    {
        return match ($message->getRole()) {
            'assistant', 'model' => 'model',
            'user' => 'user',
            default => throw new ApiException(
                sprintf('Unsupported Gemini message role "%s".', $message->getRole()),
                1788441647,
            ),
        };
    }


    /**
     * Sends one JSON request and decodes the response.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param string $url Request URL.
     * @param array<string, mixed> $payload JSON request payload.
     * @return array<string, mixed> Decoded JSON response.
     * @throws \Madj2k\AiCore\Exception\ApiException If the API key is missing.
     * @throws \GuzzleHttp\Exception\GuzzleException If the HTTP transport fails.
     * @throws \JsonException If Gemini returns malformed JSON.
     * @throws \RuntimeException If Gemini returns an unsuccessful or invalid response.
     */
    protected function sendJsonRequest(
        AiConnectionConfigurationInterface $connection,
        string $url,
        array $payload,
    ): array {
        $response = $this->sendRequest($connection, $url, $payload);
        $this->assertSuccessfulResponse($response);

        /** @var mixed $decoded */
        $decoded = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Gemini returned an invalid JSON response.');
        }

        return $decoded;
    }


    /**
     * Sends one Gemini API request.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param string $url Request URL.
     * @param array<string, mixed> $payload JSON request payload.
     * @param bool $stream Whether the response body should be streamed.
     * @return \Psr\Http\Message\ResponseInterface HTTP response.
     * @throws \Madj2k\AiCore\Exception\ApiException If the API key is missing.
     * @throws \GuzzleHttp\Exception\GuzzleException If the HTTP transport fails.
     */
    protected function sendRequest(
        AiConnectionConfigurationInterface $connection,
        string $url,
        array $payload,
        bool $stream = false,
    ): ResponseInterface {
        if ($connection->getApiKey() === '') {
            throw new ApiException('Missing Gemini API key in selected AI connection.', 1788441644);
        }

        return $this->createClient($connection)->request('POST', $url, [
            'headers' => [
                'Accept' => $stream ? 'text/event-stream' : 'application/json',
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $connection->getApiKey(),
            ],
            'json' => $payload,
            'http_errors' => false,
            'stream' => $stream,
        ]);
    }


    /**
     * Returns a cached Gemini HTTP client for the given connection.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @return \GuzzleHttp\ClientInterface Gemini HTTP client.
     */
    protected function createClient(AiConnectionConfigurationInterface $connection): ClientInterface
    {
        $cacheKey = sha1(implode('|', [
            $connection->getApiKey(),
            $connection->getBaseUrl(),
        ]));

        if (!isset($this->clients[$cacheKey])) {
            $this->clients[$cacheKey] = $this->clientFactory->create($connection, $this->retryPolicy);
        }

        return $this->clients[$cacheKey];
    }


    /**
     * Builds a Gemini model operation URL.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param string $model Model identifier.
     * @param string $operation Gemini operation.
     * @return string Operation URL.
     * @throws \Madj2k\AiCore\Exception\ApiException If the model identifier is empty.
     */
    protected function buildEndpoint(
        AiConnectionConfigurationInterface $connection,
        string $model,
        string $operation,
    ): string {
        $baseUrl = $connection->getBaseUrl() !== ''
            ? $connection->getBaseUrl()
            : self::DEFAULT_BASE_URL;

        return rtrim($baseUrl, '/')
            . '/models/' . rawurlencode($this->normalizeModel($model))
            . ':' . $operation;
    }


    /**
     * Removes the optional Gemini model resource prefix.
     *
     * @param string $model Model identifier.
     * @return string Normalized model identifier.
     * @throws \Madj2k\AiCore\Exception\ApiException If the model identifier is empty.
     */
    protected function normalizeModel(string $model): string
    {
        $model = trim($model);
        if ($model === '') {
            throw new ApiException('Missing Gemini model in selected AI connection.', 1788441645);
        }

        return str_starts_with($model, 'models/') ? substr($model, 7) : $model;
    }


    /**
     * Resolves the chat model from request and connection defaults.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return string Chat model.
     */
    protected function resolveChatModel(
        AiConnectionConfigurationInterface $connection,
        AiRequest $request,
    ): string {
        return $request->getModel() !== '' ? $request->getModel() : $connection->getDefaultModel();
    }


    /**
     * Resolves the embedding model from request and connection defaults.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $request Embedding request.
     * @return string Embedding model.
     */
    protected function resolveEmbeddingModel(
        AiConnectionConfigurationInterface $connection,
        EmbeddingRequest $request,
    ): string {
        return $request->getModel() !== '' ? $request->getModel() : $connection->getEmbeddingModel();
    }


    /**
     * Resolves the chat temperature from request and connection defaults.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return float Chat temperature.
     */
    protected function resolveChatTemperature(
        AiConnectionConfigurationInterface $connection,
        AiRequest $request,
    ): float {
        return $request->getTemperature() ?? $connection->getDefaultTemperature();
    }


    /**
     * Ensures that Gemini returned a successful HTTP response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @return void
     * @throws \RuntimeException If Gemini returns an unsuccessful response.
     */
    protected function assertSuccessfulResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        $message = 'Gemini API request failed with HTTP status ' . $statusCode . '.';
        $body = (string)$response->getBody();
        if ($body !== '') {
            try {
                /** @var mixed $decoded */
                $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded) && is_string($decoded['error']['message'] ?? null)) {
                    $message = $decoded['error']['message'];
                }
            } catch (\JsonException) {
                // Retain the status-based message for non-JSON error responses.
            }
        }

        throw new \RuntimeException($message, $statusCode);
    }


    /**
     * Emits content chunks from a Gemini server-sent event response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response Streaming HTTP response.
     * @param callable(array<string, mixed>): void $onEvent Event callback.
     * @return void
     * @throws \JsonException If Gemini returns a malformed streaming event.
     * @throws \RuntimeException If reading or decoding the stream fails.
     */
    protected function consumeEventStream(ResponseInterface $response, callable $onEvent): void
    {
        $body = $response->getBody();
        $buffer = '';
        /** @var array<int, string> $dataLines */
        $dataLines = [];

        while (!$body->eof()) {
            $buffer .= $body->read(8192);
            while (($lineEnd = strpos($buffer, "\n")) !== false) {
                $line = rtrim(substr($buffer, 0, $lineEnd), "\r");
                $buffer = substr($buffer, $lineEnd + 1);
                $this->consumeEventLine($line, $dataLines, $onEvent);
            }
        }

        if ($buffer !== '') {
            $this->consumeEventLine(rtrim($buffer, "\r"), $dataLines, $onEvent);
        }
        $this->dispatchEvent($dataLines, $onEvent);
    }


    /**
     * Consumes one server-sent event line.
     *
     * @param string $line Event stream line.
     * @param array<int, string> $dataLines Current event data lines.
     * @param callable(array<string, mixed>): void $onEvent Event callback.
     * @return void
     * @throws \JsonException If Gemini returns a malformed streaming event.
     * @throws \RuntimeException If decoding the streaming event fails.
     */
    protected function consumeEventLine(string $line, array &$dataLines, callable $onEvent): void
    {
        if ($line === '') {
            $this->dispatchEvent($dataLines, $onEvent);
            $dataLines = [];
            return;
        }

        if (str_starts_with($line, 'data:')) {
            $dataLines[] = ltrim(substr($line, 5));
        }
    }


    /**
     * Decodes and dispatches one server-sent event.
     *
     * @param array<int, string> $dataLines Event data lines.
     * @param callable(array<string, mixed>): void $onEvent Event callback.
     * @return void
     * @throws \JsonException If Gemini returns malformed event JSON.
     * @throws \RuntimeException If the decoded streaming event is invalid.
     */
    protected function dispatchEvent(array $dataLines, callable $onEvent): void
    {
        if ($dataLines === []) {
            return;
        }

        /** @var mixed $event */
        $event = json_decode(implode("\n", $dataLines), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($event)) {
            throw new \RuntimeException('Gemini returned an invalid streaming event.');
        }

        $onEvent($event);
    }


    /**
     * Extracts generated text from a Gemini response.
     *
     * @param array<string, mixed> $response Gemini response.
     * @return string Generated text.
     */
    protected function extractContent(array $response): string
    {
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        if (!is_array($parts)) {
            return '';
        }

        $content = '';
        foreach ($parts as $part) {
            if (is_array($part) && is_string($part['text'] ?? null)) {
                $content .= $part['text'];
            }
        }

        return $content;
    }


    /**
     * Extracts an embedding vector from a Gemini embedding object.
     *
     * @param mixed $embedding Gemini embedding object.
     * @return array<int, float> Embedding vector.
     */
    protected function extractEmbedding(mixed $embedding): array
    {
        $values = is_array($embedding) ? ($embedding['values'] ?? []) : [];
        if (!is_array($values)) {
            return [];
        }

        return array_map(static fn (mixed $value): float => (float)$value, array_values($values));
    }


    /**
     * Adds normalized token usage while retaining the original Gemini metadata.
     *
     * @param array<string, mixed> $response Gemini response.
     * @return array<string, mixed> Response containing normalized token usage.
     */
    protected function normalizeUsage(array $response): array
    {
        $usage = $response['usageMetadata'] ?? null;
        if (is_array($usage)) {
            $response['usage'] = [
                'prompt_tokens' => (int)($usage['promptTokenCount'] ?? 0),
                'completion_tokens' => (int)($usage['candidatesTokenCount'] ?? 0),
                'total_tokens' => (int)($usage['totalTokenCount'] ?? 0),
            ];
        }

        return $response;
    }


    /**
     * Creates a normalized public API exception.
     *
     * @param string $message Operation error message.
     * @param \Madj2k\AiCore\Connection\Resilience\RetryExhaustedException $exception Retry failure.
     * @return \Madj2k\AiCore\Exception\ApiException Normalized API exception.
     */
    protected function createApiException(string $message, RetryExhaustedException $exception): ApiException
    {
        return new ApiException(
            $message . ': ' . $exception->getMessage(),
            1788441646,
            $exception->getPrevious() ?? $exception,
            $exception->getProvider(),
            $exception->getOperation(),
            $exception->getStatusCode(),
            $exception->isRetryable(),
            $exception->getAttempts(),
        );
    }
}
