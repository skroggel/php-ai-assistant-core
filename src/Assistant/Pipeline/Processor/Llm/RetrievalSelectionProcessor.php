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
namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Llm;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalPlan;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\AbstractLlmProcessor;
use Madj2k\AiCore\Assistant\Prompt\PromptBuilder;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
/**
 * Class RetrievalSelectionProcessor
 *
 * Uses a fixed JSON contract to select retrieval targets and stores the result
 * as a RetrievalPlan in the shared assistant context.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class RetrievalSelectionProcessor extends AbstractLlmProcessor
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Connection\Resolver\AiConnectorResolver $aiConnectorResolver AI connector resolver.
     * @param \Madj2k\AiCore\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface $pipelineLogger Pipeline logger.
     */
    public function __construct(AiConnectorResolver $aiConnectorResolver, PromptBuilder $promptBuilder, PipelineLoggerInterface $pipelineLogger)
    { parent::__construct($aiConnectorResolver, $promptBuilder, $pipelineLogger); }

    /** @return string Processor identifier. */
    public function getIdentifier(): string { return 'aiassistant.retrieval_selector.default'; }

    /** @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Step type. */
    public function supports(AssistantPipelineProcessorType $type): bool { return $type === AssistantPipelineProcessorType::RetrievalSelector; }

    /** @param \Madj2k\AiCore\Assistant\Context\Context $context Assistant context. @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step. */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool { return trim($context->getCurrentQuery()) !== ''; }

    /**
     * Parses the fixed selector contract and stores it in the context.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Assistant context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional pipeline log context.
     * @return void
     */
    public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
    {
        $raw = $this->callAi($context, $this->promptBuilder->buildMessages($context, $step), $step, $logContext);
        $json = json_decode(trim($raw), true);
        if (!is_array($json)) { return; }
        $mode = in_array($json['mode'] ?? '', ['all', 'selected', 'all_except', 'none'], true) ? $json['mode'] : 'all';
        $targets = is_array($json['targets'] ?? null) ? array_values(array_filter(array_map('strval', $json['targets']))) : [];
        $metadata = is_array($json['metadata'] ?? null) ? $json['metadata'] : [];
        $context->setRetrievalPlan(new RetrievalPlan($mode, $targets, $metadata));
    }
}
