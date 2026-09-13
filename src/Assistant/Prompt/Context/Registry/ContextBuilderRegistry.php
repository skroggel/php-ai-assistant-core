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

namespace Madj2k\AiCore\Assistant\Prompt\Context\Registry;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Prompt\Context\Builder\ContextBuilderInterface;
use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;

/**
 * Class ContextBuilderRegistry
 *
 * Collects context sections from all builders that support a prompt purpose.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class ContextBuilderRegistry
{
    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiCore\Assistant\Prompt\Context\Builder\ContextBuilderInterface> $builders Context builders.
     */
    public function __construct(
        private readonly iterable $builders
    ) {
    }


    /**
     * Builds all prompt sections for the given pipeline step.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    public function buildSections(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $sections = [];

        foreach ($this->builders as $builder) {
            if (!$builder instanceof ContextBuilderInterface || !$builder->supports($step->getType())) {
                continue;
            }

            foreach ($builder->build($context, $step) as $section) {
                if ($section instanceof PromptSection && !$section->isEmpty()) {
                    $sections[] = $section;
                }
            }
        }

        usort(
            $sections,
            static fn (PromptSection $left, PromptSection $right): int => $left->priority <=> $right->priority
        );

        return $sections;
    }
}
