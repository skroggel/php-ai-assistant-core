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

namespace Madj2k\AiCore\Assistant\Export;

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;
use Madj2k\AiCore\Assistant\Configuration\FilterAwarePipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\UIComponents\Definition;

/**
 * Class PipelineMarkdownExporter
 *
 * Exports an assistant profile and its pipeline as an AI-readable Markdown document.
 * Secrets and provider credentials are deliberately excluded.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class PipelineMarkdownExporter
{
    /**
     * Exports one assistant configuration.
     *
     * @param AssistantConfigurationInterface $assistant Assistant configuration.
     * @param array<int,Definition> $uiComponents Effective UI component definitions.
     * @return string Markdown export.
     */
    public function export(AssistantConfigurationInterface $assistant, array $uiComponents = []): string
    {
        $lines = [
            '> You are reviewing the configuration of an AI assistant pipeline.',
            '> The document below is an implementation brief, not a user conversation.',
            '> Analyze the assistant identity, behavior, retrieval policy, output rules and every pipeline step together.',
            '> Pay particular attention to processor types, execution stages, include settings, step prompts, history policy and retrieval limits.',
            '> When proposing improvements, preserve the distinction between retrieval, context optimization, answer generation and quality gate responsibilities.',
            '> Do not assume that a later step can repair content that an earlier step has already emitted.',
            '',
            '# Assistant Pipeline Brief',
            '',
            '## Assistant',
            '',
            '- Title: ' . $this->value($assistant->getTitle()),
            '- Label: ' . $this->value($assistant->getAssistantLabel()),
            '',
            '## Global Prompts',
            '',
            $this->prompt('Identity', $assistant->getIdentityPrompt()),
            $this->prompt('Behavior Rules', $assistant->getBehaviorRules()),
            $this->prompt('Retrieval Rules', $assistant->getRetrievalRules()),
            $this->prompt('Output Rules', $assistant->getOutputRules()),
            '',
            '## Pipeline',
        ];

        if ($uiComponents !== []) {
            $lines[] = '';
            $lines[] = '## UI Components';
            foreach ($uiComponents as $component) {
                if (!$component instanceof Definition) {
                    continue;
                }
                $lines[] = '';
                $lines[] = '### ' . $this->value($component->title !== '' ? $component->title : $component->identifier);
                $lines[] = '';
                $lines[] = '- Identifier: `' . $component->identifier . '`';
                $lines[] = '- Description: ' . $this->value($component->description);
                $lines[] = '- Inline: ' . $this->boolean($component->inline);
                $lines[] = '- CSS class: `' . $this->value($component->cssClass) . '`';
                $lines[] = '';
                $lines[] = '#### Template';
                $lines[] = '```html';
                $lines[] = $component->template;
                $lines[] = '```';
                $lines[] = '';
                $lines[] = '#### Data schema';
                $lines[] = '```json';
                $schema = json_encode($component->dataSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $lines[] = is_string($schema) ? $schema : '{}';
                $lines[] = '```';
                $lines[] = '';
                $lines[] = '#### Placeholders';
                $lines[] = implode(', ', $component->placeholders);
                if ($component->actions !== []) {
                    $lines[] = '';
                    $lines[] = '#### Actions';
                    foreach ($component->actions as $action) {
                        $lines[] = '- `' . $action->identifier . '` — label: ' . $this->value($action->label) . ', type: `' . $action->type . '`, prompt: `' . $action->promptTemplate . '`';
                    }
                }
            }
        }

        $steps = [];
        foreach ($assistant->getChatPipelineSteps() as $candidate) {
            if ($candidate instanceof PipelineStepConfigurationInterface) {
                $steps[] = $candidate;
            }
        }

        $index = 0;
        foreach ($steps as $step) {
            $index++;
            $lines[] = '';
            $lines[] = '### ' . $index . '. ' . $this->value($step->getTitle());
            $lines[] = '';
            $lines[] = '- Type: `' . $step->getType()->value . '`';
            $lines[] = '- Type label: ' . $this->value($step->getType()->getLabel());
            $lines[] = '- Stage: `' . $step->getStage()->value . '`';
            $lines[] = '- Processor: `' . $this->value($step->getProcessorIdentifier()) . '`';
            $lines[] = '- Failure strategy: `' . $step->getFailureStrategy()->value . '`';
            $lines[] = '';
            $lines[] = '#### Include Settings';
            $lines[] = '';
            $lines[] = '- Identity prompt: ' . $this->boolean($step->getIncludeIdentityPrompt());
            $lines[] = '- Behavior rules: ' . $this->boolean($step->getIncludeBehaviorRules());
            $lines[] = '- Retrieval rules: ' . $this->boolean($step->getIncludeRetrievalRules());
            $lines[] = '- Output rules: ' . $this->boolean($step->getIncludeOutputRules());
            $lines[] = '';
            $lines[] = '#### Step Prompts';
            $lines[] = '';
            $lines[] = $this->prompt('Step Identity', $step->getStepIdentity());
            $lines[] = $this->prompt('Step Behavior Rules', $step->getStepBehaviorRules());
            $lines[] = $this->prompt('Step Retrieval Rules', $step->getStepRetrievalRules());
            $lines[] = $this->prompt('Step Output Rules', $step->getStepOutputRules());
            $lines[] = '';
            $lines[] = '#### Runtime Settings';
            $lines[] = '';
            $lines[] = '- History mode: `' . $step->getHistoryMode()->value . '`';
            $lines[] = '- History limit: `' . $step->getHistoryLimit() . '`';
            $lines[] = '- Model: `' . $this->value($step->getModel()) . '`';
            $lines[] = '- Temperature: `' . $step->getTemperature() . '`';
            $lines[] = '- Maximum tokens: `' . $step->getMaxTokens() . '`';
            $lines[] = '- Maximum retrieval results: `' . $step->getMaxRetrievalResults() . '`';
            $lines[] = '- Score threshold: `' . $step->getScoreThreshold() . '`';
            $lines[] = '- Collection: `' . $this->value($step->getRetrievalCollection()) . '`';
            $lines[] = '- Prompt metadata fields: `' . implode(', ', $step->getPromptMetadataFieldList()) . '`';

            if ($step->getType() === AssistantPipelineProcessorType::Retriever && method_exists($step, 'getRetrievalIdentifier')) {
                $lines[] = '- Retrieval identifier: `' . $this->value($step->getRetrievalIdentifier()) . '`';
            }

            if ($step->getType() === AssistantPipelineProcessorType::RetrievalSelector) {
                $lines[] = '';
                $lines[] = '#### Retrieval Selection Contract';
                $lines[] = '';
                $lines[] = 'The selector must return JSON with this fixed structure:';
                $lines[] = '';
                $lines[] = '```json';
                $lines[] = '{';
                $lines[] = '  "mode": "all|selected|all_except|none",';
                $lines[] = '  "targets": ["retrieval-identifier"],';
                $lines[] = '  "metadata": {}';
                $lines[] = '}';
                $lines[] = '```';
                $lines[] = '';
                $lines[] = 'Available retrieval targets:';
                foreach ($steps as $targetStep) {
                    if ($targetStep instanceof PipelineStepConfigurationInterface
                        && $targetStep->getType() === AssistantPipelineProcessorType::Retriever
                        && method_exists($targetStep, 'getRetrievalIdentifier')
                    ) {
                        $lines[] = '- `' . $targetStep->getRetrievalIdentifier() . '` - ' . $this->value($targetStep->getTitle());
                    }
                }
                if (method_exists($step, 'getRetrievalSelectionInstructions') && $step->getRetrievalSelectionInstructions() !== '') {
                    $lines[] = '';
                    $lines[] = 'Additional selection instructions:';
                    $lines[] = $step->getRetrievalSelectionInstructions();
                }
                if (method_exists($step, 'getRetrievalSelectionMetadata') && $step->getRetrievalSelectionMetadata() !== '') {
                    $lines[] = '';
                    $lines[] = 'Configured metadata schema:';
                    $lines[] = '```json';
                    $lines[] = $step->getRetrievalSelectionMetadata();
                    $lines[] = '```';
                }
            }

            if ($step instanceof FilterAwarePipelineStepConfigurationInterface && $step->getRetrievalFilter() !== null) {
                $lines[] = '';
                $lines[] = '#### Vector Filter';
                $lines[] = '';
                foreach ($step->getRetrievalFilter()->conditions as $condition) {
                    $value = is_array($condition->value) ? implode(', ', $condition->value) : (string)$condition->value;
                    $lines[] = '- `' . $condition->field . '` `' . $condition->operator->value . '` `' . $value . '`';
                }
            }
        }

        return trim(implode("\n", $lines)) . "\n";
    }

    /**
     * @param string $title
     * @param string $content
     * @return string
     */
    private function prompt(string $title, string $content): string
    {
        $content = trim($content);
        return $content === '' ? '##### ' . $title . "\n\n_(not configured)_" : '##### ' . $title . "\n\n```text\n" . $content . "\n```";
    }

    /**
     * @param bool $value
     * @return string
     */
    private function boolean(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }

    /**
     * @param string $value
     * @return string
     */
    private function value(string $value): string
    {
        return trim($value) === '' ? '(not configured)' : trim($value);
    }
}
