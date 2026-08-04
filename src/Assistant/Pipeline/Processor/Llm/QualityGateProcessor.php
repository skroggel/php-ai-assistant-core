<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Llm;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\AbstractLlmProcessor;
use Madj2k\AiCore\Assistant\Prompt\PromptBuilder;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;

/**
 * Class QualityGateProcessor
 *
 * Checks and finalizes the answer candidate.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class QualityGateProcessor extends AbstractLlmProcessor
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Connection\Resolver\AiConnectorResolver $aiConnectorResolver AI connector registry.
     * @param \Madj2k\AiCore\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface $pipelineLogger Pipeline logger.
     */
    public function __construct(
        AiConnectorResolver $aiConnectorResolver,
        PromptBuilder $promptBuilder,
        PipelineLoggerInterface $pipelineLogger,
    ) {
        parent::__construct($aiConnectorResolver, $promptBuilder, $pipelineLogger);
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.quality_gate.default';
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::QualityGate;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool
    {
        return trim($context->getAnswer()->getCandidate()) !== '';
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
    {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $answer = $this->callAi($context, $messages, $step, $logContext);
        $context->getAnswer()->setFinal($answer !== '' ? $answer : $context->getAnswer()->getCandidate());

        // trace
        $context->getProcessingTrace()->add('quality_gate.completed',
            $step->getUid(),
            $messages,
            $context->getAnswer()->getFinal()
        );
    }

}
