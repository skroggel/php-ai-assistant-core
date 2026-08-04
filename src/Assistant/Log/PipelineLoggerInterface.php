<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Log;

use Madj2k\AiCore\Assistant\DTO\AssistantRequest;

interface PipelineLoggerInterface
{
    public function createMetaData(AssistantRequest $assistantRequest, string $route = ''): PipelineLogMetaData;
    public function startChat(PipelineLogMetaData $logMetaData, array $payload = []): void;
    public function finishChat(PipelineLogMetaData $logMetaData, array $payload = []): void;
    public function failChat(PipelineLogMetaData $logMetaData, array $payload = []): void;
    public function startStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $payload = []): float;
    public function finishStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, float $startedAt, array $payload = []): void;
    public function logLlmRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $messages, array $options = []): void;
    public function logLlmResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, string $response, array $payload = []): void;
    public function logRetrievalRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;
    public function logRetrievalResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;
    public function event(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;
    public function error(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;
}
