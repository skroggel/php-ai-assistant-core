<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
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
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
