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

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;
use Madj2k\AiCore\Assistant\UIComponents\ProviderInterface;

/**
 * Class UIComponentsContextBuilder
 *
 * Adds UI component instructions to the central prompt builder context.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class UIComponentsContextBuilder implements ContextBuilderInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\UIComponents\ProviderInterface $provider Component definition provider.
     */
    public function __construct(private ProviderInterface $provider)
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return in_array($type, [
            AssistantPipelineProcessorType::AnswerGenerator,
            AssistantPipelineProcessorType::QualityGate,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public function build(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $instructions = $this->buildInstructions($context, $step);
        if ($instructions === '') {
            return [];
        }

        return [new PromptSection('Available UI Components', $instructions, 80)];
    }


    /**
     * Builds the component instructions included in the current LLM prompt.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Current assistant context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return string Prompt instructions.
     */
    private function buildInstructions(Context $context, PipelineStepConfigurationInterface $step): string
    {
        $definitions = $this->provider->getDefinitions($context, $step);
        if ($definitions === []) {
            return '';
        }

        $lines = [
            'Use the following UI components when they improve the answer.',
            'Never output HTML, JavaScript or framework code for a UI component.',
            'Use the component block syntax exactly as documented below. Inline link components may use :::ui link{"label":"...","url":"..."}. The component already renders its label and link; do not repeat the label as Markdown.',
            'Correct: Read more at :::ui link{"label":"Product details","url":"/products.html"}.',
            'Incorrect: Read more at [Product details](:::ui link{"label":"Product details","url":"/products.html"}).',
        ];

        foreach ($definitions as $definition) {
            $lines[] = '';
            $lines[] = 'Component: ' . $definition->identifier;
            $lines[] = 'Name: ' . ($definition->title !== '' ? $definition->title : $definition->identifier);
            if ($definition->description !== '') {
                $lines[] = 'Purpose: ' . $definition->description;
            }
            if ($definition->placeholders !== []) {
                $lines[] = 'Placeholders: ' . implode(', ', $definition->placeholders);
            }
            if ($definition->dataSchema !== []) {
                $lines[] = 'Data schema: ' . json_encode($definition->dataSchema, JSON_UNESCAPED_SLASHES);
            }
            if ($definition->actions !== []) {
                $lines[] = 'Actions:';
                foreach ($definition->actions as $action) {
                    $label = $action->label !== '' ? $action->label : $action->identifier;
                    $lines[] = '- ' . $action->identifier . ': label "' . $label . '"; prompt "' . $action->promptTemplate . '"';
                }
            }
            $lines[] = 'Syntax: :::ui ' . $definition->identifier;
            $lines[] = '{"id":"unique-id", ...component data...}';
            $lines[] = ':::';
        }

        return implode("\n", $lines);
    }
}
