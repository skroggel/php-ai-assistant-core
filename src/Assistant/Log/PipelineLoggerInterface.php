<?php
declare(strict_types=1);

/*
 * This file is part of madj2k\ai-core
 *
 * Copyright (C) 2026 Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Madj2k\AiCore\Assistant\Log;

use Madj2k\AiCore\Assistant\DTO\AssistantRequest;

/**
 * Interface PipelineLoggerInterface
 *
 * Defines structured lifecycle logging for assistant chats and pipeline steps.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface PipelineLoggerInterface
{
    /**
     * Creates log metadata for an assistant request.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\AssistantRequest $assistantRequest
     * @param string $route
     * @return \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData
     */
    public function createMetaData(AssistantRequest $assistantRequest, string $route = ''): PipelineLogMetaData;


    /**
     * Records the start of a chat turn.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param array $payload
     * @return void
     */
    public function startChat(PipelineLogMetaData $logMetaData, array $payload = []): void;


    /**
     * Records successful completion of a chat turn.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param array $payload
     * @return void
     */
    public function finishChat(PipelineLogMetaData $logMetaData, array $payload = []): void;


    /**
     * Records a failed chat turn.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param array $payload
     * @return void
     */
    public function failChat(PipelineLogMetaData $logMetaData, array $payload = []): void;


    /**
     * Records the start of a pipeline step and returns its start timestamp.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $processorType
     * @param array $payload
     * @return float
     */
    public function startStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $payload = []): float;


    /**
     * Records successful completion of a pipeline step.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $processorType
     * @param float $startedAt
     * @param array $payload
     * @return void
     */
    public function finishStep(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, float $startedAt, array $payload = []): void;


    /**
     * Records messages and options sent to an LLM.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $processorType
     * @param array $messages
     * @param array $options
     * @return void
     */
    public function logLlmRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, array $messages, array $options = []): void;


    /**
     * Records a response returned by an LLM.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $processorType
     * @param string $response
     * @param array $payload
     * @return void
     */
    public function logLlmResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $processorType, string $response, array $payload = []): void;


    /**
     * Records a vector retrieval request.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $connectorType
     * @param array $payload
     * @return void
     */
    public function logRetrievalRequest(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;


    /**
     * Records a vector retrieval response.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param string $stepTitle
     * @param string $connectorType
     * @param array $payload
     * @return void
     */
    public function logRetrievalResponse(PipelineLogMetaData $logMetaData, string $stepTitle, string $connectorType, array $payload): void;


    /**
     * Records a named pipeline event.
     *
     * @param string $eventName
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param array $payload
     * @return void
     */
    public function event(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;

    /**
     * Records a named pipeline error.
     *
     * @param string $eventName
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData
     * @param array $payload
     * @return void
     */
    public function error(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void;
}
