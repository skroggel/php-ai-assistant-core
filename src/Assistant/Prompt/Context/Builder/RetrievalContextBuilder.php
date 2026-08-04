<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt\Context\Builder;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Context\Retrieval\AnswerContextBuilder;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;

/**
 * Class RetrievalContextBuilder
 *
 * Builds prompt sections derived from retrieved documents and cached answer context.
 *
 * @internal Register custom prompt context through ContextBuilderInterface.
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class RetrievalContextBuilder extends AbstractContextBuilder
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Retrieval\AnswerContextBuilder $answerContextBuilder Answer context builder.
     */
    public function __construct(
        private readonly AnswerContextBuilder $answerContextBuilder
    ) {
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return in_array($type, [
            AssistantPipelineProcessorType::QueryOptimizer,
            AssistantPipelineProcessorType::ContextOptimizer,
            AssistantPipelineProcessorType::AnswerGenerator,
            AssistantPipelineProcessorType::QualityGate,
        ], true);
    }


    /**
     * @inheritDoc
     */
    public function build(Context $context, PipelineStepConfigurationInterface $step): array
    {
        return match ($step->getType()) {
            AssistantPipelineProcessorType::QueryOptimizer => $this->buildQueryOptimizationSections($context, $step),
            AssistantPipelineProcessorType::ContextOptimizer => $this->buildRetrievedContextSections($context, $step),
            AssistantPipelineProcessorType::AnswerGenerator,
            AssistantPipelineProcessorType::QualityGate => $this->buildAnswerContextSections($context, $step),
            default => [],
        };
    }


    /**
     * Resolves the current answer context or builds it from retrieved documents.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @param bool $storeWhenBuilt Whether a newly built context is stored in the retrieval state.
     * @return string Answer context.
     */
    public function resolveAnswerContext(Context $context, PipelineStepConfigurationInterface $step, bool $storeWhenBuilt = false): string
    {
        $answerContext = $context->getRetrieval()->getAnswerContext();
        if ($answerContext !== '') {
            return $this->answerContextBuilder->limit($answerContext, $step);
        }

        if ($context->getRetrieval()->getResults() === []) {
            return '';
        }

        $answerContext = $this->answerContextBuilder->build($context->getRetrieval()->getResults(), $step);

        if ($storeWhenBuilt) {
            $context->getRetrieval()->setAnswerContext($answerContext);
        }

        return $answerContext;
    }


    /**
     * Builds retrieved context sections for post-retrieval query optimization.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    private function buildQueryOptimizationSections(Context $context, PipelineStepConfigurationInterface $step): array
    {
        if ($step->getStage() !== AssistantPipelineStage::PostRetrieval) {
            return [];
        }

        return $this->filterSections([
            $this->section('Retrieved Context', $this->resolveAnswerContext($context, $step), 30),
        ]);
    }


    /**
     * Builds raw retrieved context sections for context optimization.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    private function buildRetrievedContextSections(Context $context, PipelineStepConfigurationInterface $step): array
    {
        if ($context->getRetrieval()->getResults() === []) {
            return [];
        }

        return $this->filterSections([
            $this->section(
                'Retrieved Context',
                $this->answerContextBuilder->build($context->getRetrieval()->getResults(), $step),
                20
            ),
        ]);
    }


    /**
     * Builds answer context sections for answer generation and quality checks.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    private function buildAnswerContextSections(Context $context, PipelineStepConfigurationInterface $step): array
    {
        return $this->filterSections([
            $this->section('Answer Context', $this->resolveAnswerContext($context, $step, true), 30),
        ]);
    }
}
