<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;
use Madj2k\AiCore\Assistant\Prompt\Context\Formatter;
use Madj2k\AiCore\Assistant\Prompt\Context\Registry\ContextBuilderRegistry;

/**
 * Class PromptBuilder
 *
 * Builds compact prompts for LLM-based chat pipeline steps.
 *
 *
 * Workflow
 * -> PromptBuilder
 * -> ContextBuilderRegistry
 * -> suitable ContextBuilder
 * -> PromptSection[]
 * -> Formatter
 * -> final Prompt-Text
 * -> LLM
 *
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class PromptBuilder
{
    /**
     * constructor
     */
    public function __construct(
        private readonly ContextBuilderRegistry $contextBuilderRegistry,
        private readonly Formatter $contextFormatter
    ) {
    }


    /**
     * Builds chat messages for an LLM step.
     *
     * @param Context $context Current chat context.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @return array<int,array{role:string,content:string}>
     */
    public function buildMessages(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $systemPrompt = $this->buildSystemPrompt($context, $step);
        $historyPrompt = $this->buildHistoryPrompt($context, $step);
        $contextPrompt = $this->buildContextPrompt($context, $step);
        $userPrompt = trim($historyPrompt . "\n\n" . $contextPrompt);

        return [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => trim($userPrompt),
            ],
        ];
    }


    /**
     * Returns one section content from the centralized prompt context.
     *
     * @param Context $context Current chat context.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @param string $title Section title.
     * @return string Section content.
     */
    public function getContextSectionContent(
        Context $context,
        PipelineStepConfigurationInterface $step,
        string $title
    ): string {
        foreach ($this->contextBuilderRegistry->buildSections($context, $step) as $section) {
            if ($section->title === $title) {
                return trim($section->content);
            }
        }

        return '';
    }


    /**
     * Builds the system prompt from selected assistant and step prompt parts.
     *
     * @param Context $context Current chat context.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @return string
     */
    private function buildSystemPrompt(Context $context, PipelineStepConfigurationInterface $step): string
    {
        $assistant = $context->getAssistant();
        $parts = [];

        if ($step->getIncludeIdentityPrompt() && $assistant->getIdentityPrompt() !== '') {
            $parts[] = "[Assistant Identity]\n" . $assistant->getIdentityPrompt();
        }
        if ($step->getIncludeBehaviorRules() && $assistant->getBehaviorRules() !== '') {
            $parts[] = "[Assistant Behavior Rules]\n" . $assistant->getBehaviorRules();
        }
        if ($step->getIncludeRetrievalRules() && $assistant->getRetrievalRules() !== '') {
            $parts[] = "[Assistant Retrieval Rules]\n" . $assistant->getRetrievalRules();
        }
        if ($step->getIncludeOutputRules() && $assistant->getOutputRules() !== '') {
            $parts[] = "[Assistant Output Rules]\n" . $assistant->getOutputRules();
        }

        if ($step->getStepIdentity() !== '') {
            $parts[] = "[Step Identity]\n" . $step->getStepIdentity();
        }
        if ($step->getStepBehaviorRules() !== '') {
            $parts[] = "[Step Behavior Rules]\n" . $step->getStepBehaviorRules();
        }
        if ($step->getStepRetrievalRules() !== '') {
            $parts[] = "[Step Retrieval Rules]\n" . $step->getStepRetrievalRules();
        }
        if ($step->getStepOutputRules() !== '') {
            $parts[] = "[Step Output Rules]\n" . $step->getStepOutputRules();
        }

        $parts[] = 'If assistant rules and step rules conflict, assistant rules have priority.';

        return implode("\n\n", array_filter($parts));
    }


    /**
     * Builds the history prompt according to the configured history policy.
     *
     * @param Context $context Current chat context.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @return string
     */
    private function buildHistoryPrompt(Context $context, PipelineStepConfigurationInterface $step): string
    {
        if ($step->getHistoryMode() === HistoryMode::None) {
            return '';
        }

        $messages = $context->getHistory()->last($step->getHistoryLimit());

        if ($messages === []) {
            return '';
        }

        $lines = ['[Chat History]'];
        foreach ($messages as $message) {
            $lines[] = sprintf('%s: %s', $message['role'], $message['content']);
        }

        return implode("\n", $lines);
    }


    /**
     * Builds the step-specific context prompt from centralized prompt context sections.
     *
     * @param Context $context Current chat context.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @return string Prompt context.
     */
    private function buildContextPrompt(Context $context, PipelineStepConfigurationInterface $step): string
    {
        return $this->contextFormatter->format($this->contextBuilderRegistry->buildSections($context, $step));
    }
}
