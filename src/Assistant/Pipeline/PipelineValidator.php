<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;
use Madj2k\AiCore\Exception\AssistantException;

/**
 * Class PipelineValidator
 *
 * Validates structural dependencies and reports non-blocking configuration warnings.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class PipelineValidator
{
    /**
     * Returns warnings and throws when the pipeline is structurally invalid.
     *
     * @param iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Pipeline steps.
     * @param \Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry|null $processorRegistry Optional processor registry.
     * @return array<int, string> Validation warnings.
     * @throws \Madj2k\AiCore\Exception\AssistantException
     */
    public function validate(iterable $steps, ?ProcessorRegistry $processorRegistry = null): array
    {
        $steps = is_array($steps) ? array_values($steps) : iterator_to_array($steps, false);
        $errors = [];
        $warnings = [];
        $seenUids = [];
        $typeCounts = [];
        $hasAnswerGeneratorOrMemory = false;
        $hasRetrievalSource = false;
        $hasAnswerGenerator = false;
        $hasQualityGate = false;

        foreach ($steps as $step) {
            $type = $step->getType();
            $label = $this->createStepLabel($step);
            $typeCounts[$type->value] = ($typeCounts[$type->value] ?? 0) + 1;

            $uid = $step->getUid();
            if ($uid !== null && $uid > 0) {
                if (isset($seenUids[$uid])) {
                    $errors[] = sprintf('%s duplicates pipeline step uid %d.', $label, $uid);
                }
                $seenUids[$uid] = true;
            }

            if ($processorRegistry instanceof ProcessorRegistry) {
                try {
                    $processorRegistry->get($step->getProcessorIdentifier(), $type);
                } catch (AssistantException $exception) {
                    $errors[] = sprintf('%s is invalid: %s', $label, $exception->getMessage());
                }
            }

            $allowedStages = $this->getAllowedStages($type);
            if ($allowedStages !== [] && !in_array($step->getStage(), $allowedStages, true)) {
                $warnings[] = sprintf(
                    '%s uses stage "%s"; expected one of: %s.',
                    $label,
                    $step->getStage()->value,
                    implode(', ', array_map(static fn (AssistantPipelineStage $stage): string => $stage->value, $allowedStages)),
                );
            }

            if ($type === AssistantPipelineProcessorType::ContextOptimizer && !$hasRetrievalSource) {
                $errors[] = sprintf('%s requires a preceding retriever or memory step.', $label);
            }
            if (
                $type === AssistantPipelineProcessorType::QueryOptimizer
                && $step->getStage() === AssistantPipelineStage::PostRetrieval
                && !$hasRetrievalSource
            ) {
                $warnings[] = sprintf('%s is post-retrieval but has no preceding retriever or memory step.', $label);
            }
            if ($type === AssistantPipelineProcessorType::QualityGate && !$hasAnswerGenerator) {
                $errors[] = sprintf('%s requires a preceding answer generator.', $label);
            }
            if ($type === AssistantPipelineProcessorType::AnswerGenerator && $hasQualityGate) {
                $errors[] = sprintf('%s must not run after a quality gate.', $label);
            }

            if ($step->getFailureStrategy() === AssistantPipelineFailureStrategy::Fallback) {
                $warnings[] = sprintf('%s uses deprecated failure strategy "fallback"; use "continue" instead.', $label);
            } elseif (
                $step->getFailureStrategy() === AssistantPipelineFailureStrategy::Continue
                && in_array($type, [
                    AssistantPipelineProcessorType::AnswerGenerator,
                    AssistantPipelineProcessorType::QualityGate,
                ], true)
            ) {
                $warnings[] = sprintf('%s should normally use failure strategy "stop" because it defines the visible answer.', $label);
            }

            $hasAnswerGeneratorOrMemory = $hasAnswerGeneratorOrMemory
                || in_array($type, [
                    AssistantPipelineProcessorType::AnswerGenerator,
                    AssistantPipelineProcessorType::Memory,
                ], true);
            $hasRetrievalSource = $hasRetrievalSource
                || in_array($type, [
                    AssistantPipelineProcessorType::Retriever,
                    AssistantPipelineProcessorType::Memory,
                ], true);
            $hasAnswerGenerator = $hasAnswerGenerator || $type === AssistantPipelineProcessorType::AnswerGenerator;
            $hasQualityGate = $hasQualityGate || $type === AssistantPipelineProcessorType::QualityGate;
        }

        if (!$hasAnswerGeneratorOrMemory) {
            $errors[] = 'The pipeline needs at least one answer generator or memory step.';
        }
        if (($typeCounts[AssistantPipelineProcessorType::AnswerGenerator->value] ?? 0) > 1) {
            $warnings[] = 'The pipeline contains multiple answer generators; only the last answer stage can stream.';
        }
        if (($typeCounts[AssistantPipelineProcessorType::QualityGate->value] ?? 0) > 1) {
            $warnings[] = 'The pipeline contains multiple quality gates; only the last answer stage can stream.';
        }

        if ($errors !== []) {
            throw new AssistantException('Invalid pipeline: ' . implode(' ', $errors), 1783001001);
        }

        return array_values(array_unique($warnings));
    }

    /** @return array<int, AssistantPipelineStage> */
    private function getAllowedStages(AssistantPipelineProcessorType $type): array
    {
        return match ($type) {
            AssistantPipelineProcessorType::QueryOptimizer => [
                AssistantPipelineStage::PreRetrieval,
                AssistantPipelineStage::PostRetrieval,
            ],
            AssistantPipelineProcessorType::Retriever => [AssistantPipelineStage::Retrieval],
            AssistantPipelineProcessorType::ContextOptimizer => [AssistantPipelineStage::PostRetrieval],
            AssistantPipelineProcessorType::AnswerGenerator => [AssistantPipelineStage::PreAnswer],
            AssistantPipelineProcessorType::QualityGate => [AssistantPipelineStage::PostAnswer],
            AssistantPipelineProcessorType::Memory => [],
        };
    }

    private function createStepLabel(PipelineStepConfigurationInterface $step): string
    {
        $title = trim($step->getTitle());
        if ($title === '') {
            $title = $step->getType()->getLabel();
        }

        return $step->getUid() !== null && $step->getUid() > 0
            ? sprintf('Pipeline step "%s" (uid %d)', $title, $step->getUid())
            : sprintf('Pipeline step "%s"', $title);
    }
}
