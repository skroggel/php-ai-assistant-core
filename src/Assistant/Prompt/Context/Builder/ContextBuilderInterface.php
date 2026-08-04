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
 * Class ContextBuilderInterface
 *
 * Defines a builder that contributes structured sections to a prompt context.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ContextBuilderInterface
{
    /**
     * Returns whether this builder contributes sections for the processor type.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Pipeline processor type.
     * @return bool Supports flag.
     */
    public function supports(AssistantPipelineProcessorType $type): bool;

    /**
     * Builds prompt sections for the given context and pipeline step.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    public function build(Context $context, PipelineStepConfigurationInterface $step): array;
}
