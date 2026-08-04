<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Retrieval;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\DTO\LastRetrievalResult;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Memory\MemoryInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;

/**
 * Class RetrievalReadProcessor
 *
 * Reads the last retrieval result from conversation memory and writes it into the current retrieval context.
 * Use this as the first memory step in follow-up pipelines that should reuse the previous retrieval.
 *
 * @internal Register custom pipeline behavior through ProcessorInterface.
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class RetrievalReadProcessor implements ProcessorInterface
{

    /**
     * Processor identifier.
     *
     * @var string
     */
    private const IDENTIFIER = 'aiassistant.memory.retrieval_read';


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
        return trim($context->getRequest()->getChatIdentifier()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(
        Context $context,
        PipelineStepConfigurationInterface $step,
        ?PipelineLogMetaData $logContext = null
    ): void {
        $chatIdentifier = $context->getRequest()->getChatIdentifier();
        $lastRetrievalResult = $this->sessionMemory->getLastRetrievalResult($chatIdentifier);

        if (!$lastRetrievalResult instanceof LastRetrievalResult) {
            throw new \RuntimeException(
                sprintf('No last retrieval result found for chat identifier "%s".', $chatIdentifier),
                1782001201
            );
        }

        $context->getRetrieval()->setProcessorIdentifier(
            $lastRetrievalResult->retrievalIdentifier !== ''
                ? $lastRetrievalResult->retrievalIdentifier
                : self::IDENTIFIER
        );
        $context->getRetrieval()->setResults($lastRetrievalResult->documents);
        $context->getRetrieval()->setRawResults($lastRetrievalResult->rawData);

        $payload = [
            'chat_identifier' => $chatIdentifier,
            'retrieval_identifier' => $lastRetrievalResult->retrievalIdentifier,
            'document_count' => $lastRetrievalResult->getDocumentCount(),
            'raw_result_count' => count($lastRetrievalResult->rawData),
            'created_at' => $lastRetrievalResult->createdAt,
        ];

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->event('memory.retrieval_read.completed', $logContext, array_merge($payload, [
                'step_title' => $step->getTitle(),
                'processor_type' => $this->getIdentifier(),
            ]));
        }

        $context->getProcessingTrace()->add(
            'memory.retrieval_read.completed',
            $step->getUid(),
            $chatIdentifier,
            $payload
        );
    }
}
