<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Retrieval;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Memory\MemoryInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Exception\AppException;

/**
 * Class RetrievalWriteProcessor
 *
 * Writes the current retrieval state into conversation memory as the last retrieval result.
 * This processor is explicit pipeline behavior; the orchestrator does not persist retrieval by default.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class RetrievalWriteProcessor implements ProcessorInterface
{

    /**
     * Processor identifier.
     *
     * @var string
     */
    private const IDENTIFIER = 'aiassistant.memory.retrieval_write';


    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Memory\MemoryInterface $sessionMemory Conversation memory.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface $pipelineLogger Pipeline logger.
     */
    public function __construct(
        private MemoryInterface $sessionMemory,
        private PipelineLoggerInterface $pipelineLogger,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::Memory;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool
    {
        return (bool)$context->getRetrieval();
    }


    /**
     * @inheritDoc
     * @throws AppException
     */
    public function process(
        Context $context,
        PipelineStepConfigurationInterface $step,
        ?PipelineLogMetaData $logContext = null
    ): void {
        $chatIdentifier = $context->getRequest()->getChatIdentifier();
        $retrievalResult = $context->getRetrieval();

        $storedRetrievalResult = $this->sessionMemory->setLastRetrievalResult(
            $chatIdentifier,
            $retrievalResult,
        );

        $payload = [
            'chat_identifier' => $chatIdentifier,
            'retrieval_identifier' => $retrievalResult->getProcessorIdentifier(),
            'document_count' => count($retrievalResult->getResults()),
            'raw_result_count' => count($retrievalResult->getRawResults()),
            'stored' => $storedRetrievalResult !== null,
        ];

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->event('memory.retrieval_write.completed', $logContext, array_merge($payload, [
                'step_title' => $step->getTitle(),
                'processor_type' => $this->getIdentifier(),
            ]));
        }

        $context->getProcessingTrace()->add(
            'memory.retrieval_write.completed',
            $step->getUid(),
            $chatIdentifier,
            $payload
        );
    }
}
