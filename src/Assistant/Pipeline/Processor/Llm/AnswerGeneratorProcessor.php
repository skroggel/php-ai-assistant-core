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
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorStreamingInterface;
use Madj2k\AiCore\Assistant\Prompt\PromptBuilder;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;

/**
 * Class AnswerGeneratorProcessor
 *
 * Generates the answer candidate for the user.
 *
 * @internal Register custom pipeline behavior through ProcessorInterface.
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AnswerGeneratorProcessor extends AbstractLlmProcessor implements ProcessorStreamingInterface
{
    /**
     * Constructor.
     *
     * @inheritDoc
     * @param \Madj2k\AiCore\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     */
    public function __construct(
        AiConnectorResolver                      $aiConnectorResolver,
        PromptBuilder                            $promptBuilder,
        PipelineLoggerInterface                           $pipelineLogger,
    ) {
        parent::__construct($aiConnectorResolver, $promptBuilder, $pipelineLogger);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.answer_generator.default';
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::AnswerGenerator;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool
    {
        return trim($context->getCurrentQuery()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
    {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $answer = $this->callAi($context, $messages, $step, $logContext);
        $context->getAnswer()->setCandidate($answer);

        // trace
        $context->getProcessingTrace()->add('answer_generator.completed',
            $step->getUid(),
            $messages,
            $answer
        );
    }


    /**
     * @inheritDoc
     */
    public function processStream(
        Context $context,
        PipelineStepConfigurationInterface $step,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): void {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $answer = $this->callAiStream($context, $messages, $step, $onData, $logContext);
        $context->getAnswer()->setCandidate($answer);

        // trace
        $context->getProcessingTrace()->add('answer_generator.completed',
            $step->getUid(),
            $messages,
            $answer
        );
    }


}
