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
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalTargetProviderInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;
/**
 * Class RetrievalSelectionContextBuilder
 *
 * Adds available retrieval targets and selector instructions to the LLM context.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class RetrievalSelectionContextBuilder implements ContextBuilderInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalTargetProviderInterface $targetProvider Retrieval target provider.
     */
    public function __construct(private RetrievalTargetProviderInterface $targetProvider) {}

    /** @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Step type. */
    public function supports(AssistantPipelineProcessorType $type): bool { return $type === AssistantPipelineProcessorType::RetrievalSelector; }

    /**
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Assistant context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int,\Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    public function build(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $lines = ['Select retrieval targets for the current query.', 'Return JSON with mode (all, selected, all_except or none), targets and optional metadata.'];
        foreach ($this->targetProvider->getTargets($context, $step) as $target) {
            $lines[] = sprintf('- %s: %s', $target->identifier, $target->description !== '' ? $target->description : $target->title);
        }
        if (method_exists($step, 'getRetrievalSelectionInstructions') && $step->getRetrievalSelectionInstructions() !== '') {
            $lines[] = '';
            $lines[] = 'Additional selection rules:';
            $lines[] = $step->getRetrievalSelectionInstructions();
        }
        if (method_exists($step, 'getRetrievalSelectionMetadata') && $step->getRetrievalSelectionMetadata() !== '') {
            $lines[] = '';
            $lines[] = 'Return metadata according to this schema:';
            $lines[] = $step->getRetrievalSelectionMetadata();
        }
        return [new PromptSection('Available Retrieval Targets', implode("\n", $lines), 70)];
    }
}
