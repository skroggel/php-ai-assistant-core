<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Log;

use Madj2k\AiCore\Assistant\DTO\AssistantRequest;

/**
 * Interface PipelineLoggerInterface
 *
 * Defines structured lifecycle logging for assistant chats and pipeline steps.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface PipelineLoggerInterface
{
    /** Creates log metadata for an assistant request. */
    public function createMetaData(AssistantRequest $assistantRequest, string $route = ''): PipelineLogMetaData;

    /** Records the start of a chat turn. */
    public function startChat(PipelineLogMetaData $logMetaData, array $payload = []): void;

    /** Records successful completion of a chat turn. */
    public function finishChat(PipelineLogMetaData $logMetaData, array $payload = []): void;

    /** Records a failed chat turn. */
    public function failChat(PipelineLogMetaData $logMetaData, array $payload = []): void;

    /** Records the start of a pipeline step and returns its start timestamp. */
    public function startStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $payload = []): float;

    /** Records successful completion of a pipeline step. */
    public function finishStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, float $startedAt, array $payload = []): void;

    /** Records messages and options sent to an LLM. */
    public function logLlmRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $messages, array $options = []): void;

    /** Records a response returned by an LLM. */
    public function logLlmResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, string $response, array $payload = []): void;

    /** Records a vector retrieval request. */
    public function logRetrievalRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;

    /** Records a vector retrieval response. */
    public function logRetrievalResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;

    /** Records a named pipeline event. */
    public function event(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;

    /** Records a named pipeline error. */
    public function error(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;
}
