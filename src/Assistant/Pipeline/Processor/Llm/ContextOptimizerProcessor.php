<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Llm;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\AbstractLlmProcessor;
use Madj2k\AiCore\Assistant\Prompt\PromptBuilder;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Exception\AppException;

/**
 * Class ContextOptimizerProcessor
 *
 * Uses the LLM to condense retrieved documents into a focused answer context.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class ContextOptimizerProcessor extends AbstractLlmProcessor
{
    /**
     * Constructor.
     *
     * @inheritDoc
     * @param \Madj2k\AiCore\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     */
    public function __construct(
        AiConnectorResolver                      $aiConnectorResolver,
        PromptBuilder                             $promptBuilder,
        PipelineLoggerInterface                           $pipelineLogger,
    ) {
        parent::__construct($aiConnectorResolver, $promptBuilder, $pipelineLogger);
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.context_optimizer.default';
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::ContextOptimizer;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool
    {
        return $context->getRetrieval()->getResults() !== [];
    }


    /**
     * @inheritDoc
     * @throws \Madj2k\AiCore\Exception\ApiException
     */
    public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
    {
        $rawContext = $this->promptBuilder->getContextSectionContent(
            $context,
            $step,
            'Retrieved Context'
        );
        if ($rawContext === '') {
            return;
        }

        $messages = $this->promptBuilder->buildMessages($context, $step);

        $answerContext = $this->callAi($context, $messages, $step, $logContext);
        $context->getRetrieval()->setAnswerContext($answerContext !== '' ? $answerContext : $rawContext);

        // trace
        $context->getProcessingTrace()->add('context_optimizer.completed',
            $step->getUid(),
            $messages,
            $context->getRetrieval()->getAnswerContext(),
        );
    }
}
