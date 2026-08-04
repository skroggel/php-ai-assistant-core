<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\DTO\AssistantResponse;
use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorStreamingInterface;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;

/**
 * Class Pipeline
 *
 * Executes configured chat steps in their sorting order.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class Pipeline
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry $processorRegistry Processor registry.
     * @param \Madj2k\AiCore\Assistant\Pipeline\PipelineValidator $validator Pipeline validator.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface $pipelineLogger Logger.
     */
    public function __construct(
        private readonly ProcessorRegistry $processorRegistry,
        private readonly PipelineValidator $validator,
        private readonly PipelineLoggerInterface    $pipelineLogger,
    ) {
    }


    /**
     * Runs the pipeline and returns the final result.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Configured steps.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return \Madj2k\AiCore\Assistant\DTO\AssistantResponse
     * @throws \Madj2k\AiCore\Exception\ApiException
     * @throws \Throwable
     */
    public function run(Context $context, iterable $steps, ?PipelineLogMetaData $logContext = null): AssistantResponse
    {
        return $this->execute($context, $steps, null, $logContext);
    }


    /**
     * Runs the pipeline and streams processor output where supported.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Configured steps.
     * @param callable $onData Callback for streamed chunks.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return \Madj2k\AiCore\Assistant\DTO\AssistantResponse
     * @throws \Madj2k\AiCore\Exception\ApiException
     * @throws \Throwable
     */
    public function runStream(
        Context $context,
        iterable $steps,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): AssistantResponse {
        return $this->execute($context, $steps, $onData, $logContext);
    }


    /**
     * Executes the configured pipeline.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Configured steps.
     * @param callable|null $onData Optional streaming callback.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return \Madj2k\AiCore\Assistant\DTO\AssistantResponse
     * @throws \Throwable
     */
    private function execute(
        Context $context,
        iterable $steps,
        ?callable $onData = null,
        ?PipelineLogMetaData $logContext = null
    ): AssistantResponse {
        $steps = is_array($steps) ? array_values($steps) : iterator_to_array($steps, false);

        foreach ($this->validator->validate($steps) as $validationMessage) {
            if ($logContext instanceof PipelineLogMetaData) {
                $this->pipelineLogger->event('pipeline.validation.warning', $logContext, [
                    'message' => $validationMessage,
                ]);
            }
        }

        /** @var array<int,string> $sourceFields */
        $sourceFields = [];

        /**
         * @var \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step
         */
        foreach ($steps as $step) {
            if ($step->getPromptMetadataFieldList() !== []) {
                $sourceFields = $step->getPromptMetadataFieldList();
            }

            $processor = $this->processorRegistry->get($step->getProcessorIdentifier(), $step->getType());

            if (!$processor->canProcess($context, $step)) {
                $payload = [
                    'step_uid' => (int)$step->getUid(),
                    'step_title' => $step->getTitle(),
                    'processor_type' => $step->getType()->value,
                    'reason' => 'Required context slot missing.',
                ];

                if ($logContext instanceof PipelineLogMetaData) {
                    $this->pipelineLogger->event('step.skipped', $logContext, $payload);
                }
                continue;
            }

            $startedAt = null;
            if ($logContext instanceof PipelineLogMetaData) {
                $startedAt = $this->pipelineLogger->startStep(
                    $logContext,
                    $step->getTitle(),
                    $step->getType()->value,
                    [
                        'step_uid' => (int)$step->getUid(),
                        'stage' => $step->getStage()->value,
                    ]
                );
            }

            try {
                if ($onData !== null && $processor instanceof ProcessorStreamingInterface) {
                    $processor->processStream($context, $step, $onData, $logContext);
                } else {
                    $processor->process($context, $step, $logContext);
                }

                if ($logContext instanceof PipelineLogMetaData && is_float($startedAt)) {
                    $this->pipelineLogger->finishStep(
                        $logContext,
                        $step->getTitle(),
                        $step->getType()->value,
                        $startedAt,
                        [
                            'step_uid' => (int)$step->getUid(),
                            'stage' => $step->getStage()->value,
                        ]
                    );
                }
            } catch (\Throwable $exception) {
                if ($logContext instanceof PipelineLogMetaData) {
                    $this->pipelineLogger->error('step.failed', $logContext, [
                        'step_uid' => (int)$step->getUid(),
                        'step_title' => $step->getTitle(),
                        'processor_type' => $step->getType()->value,
                        'exception_message' => $exception->getMessage(),
                        'exception_class' => get_class($exception),
                    ]);
                }

                if ($step->getFailureStrategy() === AssistantPipelineFailureStrategy::Stop) {
                    throw $exception;
                }
            }
        }

        return $this->createResponse($context, $sourceFields);
    }


    /**
     * Creates the assistant response from the context.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param array<int,string> $sourceFields Source field names.
     * @return \Madj2k\AiCore\Assistant\DTO\AssistantResponse
     */
    private function createResponse(Context $context, array $sourceFields): AssistantResponse
    {
        $answer = $context->getAnswer()->getFinal();
        if ($answer === '') {
            $answer = $context->getAnswer()->getCandidate();
        }

        return new AssistantResponse(
            answer: $answer,
            context: [
                'currentQuery' => $context->getCurrentQuery(),
                'answerContext' => $context->getRetrieval()->getAnswerContext(),
                'retrievalCount' => count($context->getRetrieval()->getResults()),
                'sources' => $this->createFrontendSources(
                    $context->getRetrieval()->getResults(),
                    $sourceFields
                ),
            ]
        );
    }


    /**
     * Creates deduplicated presentation-layer sources from retrieved documents.
     *
     * @param array<int, \Madj2k\AiCore\Assistant\DTO\RetrievalDocument> $documents Retrieved documents.
     * @param array<int,string> $fields Source field names.
     * @return array<int,array<string,mixed>> Frontend sources.
     */
    private function createFrontendSources(array $documents, array $fields): array
    {
        if ($fields === []) {
            return [];
        }

        $sources = [];
        $seen = [];

        foreach ($documents as $document) {
            if (!$document instanceof RetrievalDocument) {
                continue;
            }

            $source = $document->toSourceArray($fields);
            if ($source === []) {
                continue;
            }

            $deduplicationKey = (string)($source['source_identifier'] ?? $source['url'] ?? md5(json_encode($source)));
            if (isset($seen[$deduplicationKey])) {
                continue;
            }

            $seen[$deduplicationKey] = true;
            $sources[] = $source;
        }

        return $sources;
    }
}
