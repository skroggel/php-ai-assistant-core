<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

/**
 * Class PipelineValidator
 *
 * Performs lightweight structural validation for configured chat pipelines.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class PipelineValidator
{
    /**
     * Returns validation messages.
     *
     * @param iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Pipeline steps.
     * @return array<int,string>
     */
    public function validate(iterable $steps): array
    {
        $messages = [];
        $hasAnswerGeneratorOrMemory = false;

        /**
         * @var \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step
         */
        foreach ($steps as $step) {
            if ($step->getType() === AssistantPipelineProcessorType::AnswerGenerator) {
                $hasAnswerGeneratorOrMemory = true;
            }
            if ($step->getType() === AssistantPipelineProcessorType::Memory) {
                $hasAnswerGeneratorOrMemory = true;
            }
        }

        if (!$hasAnswerGeneratorOrMemory) {
            $messages[] = 'The pipeline needs at least one answer generator or memory step.';
        }

        return $messages;
    }
}
