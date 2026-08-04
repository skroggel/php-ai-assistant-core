<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt\Context\Builder;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

/**
 * Class QueryContextBuilder
 *
 * Builds prompt sections for original and current user queries.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class QueryContextBuilder extends AbstractContextBuilder
{
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
            AssistantPipelineProcessorType::QueryOptimizer => $this->filterSections([
                $this->section('Current Query', $context->getCurrentQuery(), 10),
                $this->section('Original User Query', $context->getRequest()->getQuery(), 20),
            ]),
            AssistantPipelineProcessorType::ContextOptimizer,
            AssistantPipelineProcessorType::QualityGate => $this->filterSections([
                $this->section('Current Query', $context->getCurrentQuery(), 10),
            ]),
            AssistantPipelineProcessorType::AnswerGenerator => $this->filterSections([
                $this->section('Original User Query', $context->getRequest()->getQuery(), 10),
                $this->section('Current Query', $context->getCurrentQuery(), 20),
            ]),
            default => [],
        };
    }
}
