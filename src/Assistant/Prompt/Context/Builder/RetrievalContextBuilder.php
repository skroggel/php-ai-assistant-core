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
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
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

        $answerContext = $this->buildRetrievedContext($context, $step);
        if ($answerContext === '') {
            return '';
        }

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
        if ($context->getRetrieval()->getDocumentCount() === 0) {
            return [];
        }

        return $this->filterSections([
            $this->section(
                'Retrieved Context',
                $this->buildRetrievedContext($context, $step),
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


    /**
     * Builds named prompt sections with per-retrieval and final global limits.
     *
     * @param Context $context Current assistant context.
     * @param PipelineStepConfigurationInterface $step Current pipeline step.
     * @return string Prompt-ready retrieval context.
     */
    private function buildRetrievedContext(Context $context, PipelineStepConfigurationInterface $step): string
    {
        $groups = $context->getRetrieval()->getGroups();

        $sections = [];
        foreach ($groups as $group) {
            $content = $this->answerContextBuilder->buildGroup($group);
            if ($content === '') {
                continue;
            }

            $header = sprintf('[Retrieval: %s]', $group->identifier);
            $details = array_filter([
                $group->query !== '' ? 'query: ' . $group->query : '',
                $group->collection !== '' ? 'collection: ' . $group->collection : '',
            ]);
            $sections[] = $header
                . ($details !== [] ? "\n" . implode("\n", $details) : '')
                . "\n\n" . $content;
        }

        return $this->applyGlobalLimitFairly($sections, $step->getMaxContextCharacters());
    }


    /**
     * Applies the final LLM-step safety limit without always sacrificing later retrievals.
     *
     * @param array<int, string> $sections Retrieval context sections.
     * @param int $maximumCharacters Global character limit.
     * @return string Fairly limited retrieval context.
     */
    private function applyGlobalLimitFairly(array $sections, int $maximumCharacters): string
    {
        $separator = "\n\n===\n\n";
        $complete = implode($separator, $sections);
        if ($maximumCharacters <= 0 || strlen($complete) <= $maximumCharacters || count($sections) < 2) {
            return $maximumCharacters > 0 ? rtrim(substr($complete, 0, $maximumCharacters)) : $complete;
        }

        $available = max(0, $maximumCharacters - (strlen($separator) * (count($sections) - 1)));
        $allocations = array_fill(0, count($sections), 0);
        $pending = array_keys($sections);

        while ($pending !== [] && $available > 0) {
            $share = max(1, intdiv($available, count($pending)));
            $nextPending = [];
            foreach ($pending as $index) {
                if ($available <= 0) {
                    $nextPending[] = $index;
                    continue;
                }
                $missing = strlen($sections[$index]) - $allocations[$index];
                $granted = min($missing, $share, $available);
                $allocations[$index] += $granted;
                $available -= $granted;
                if ($allocations[$index] < strlen($sections[$index])) {
                    $nextPending[] = $index;
                }
            }
            $pending = $nextPending;
        }

        return implode($separator, array_map(
            static fn (string $section, int $index): string => rtrim(substr($section, 0, $allocations[$index])),
            $sections,
            array_keys($sections),
        ));
    }
}
