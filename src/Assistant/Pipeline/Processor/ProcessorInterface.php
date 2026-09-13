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

namespace Madj2k\AiCore\Assistant\Pipeline\Processor;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;

/**
 * Interface ProcessorInterface
 *
 * Contract for one typed chat pipeline processor.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface ProcessorInterface
{

    /**
     * Tells which processor is to load
     * @return string
     */
    public function getIdentifier(): string;


    /**
     * Tells whether this processor supports a step type.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Step type.
     * @return bool
     */
    public function supports(AssistantPipelineProcessorType $type): bool;


    /**
     * Tells whether the current context contains required input slots.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step.
     * @return bool
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool;


    /**
     * Processes the step and writes its result to the context.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return void
     * @throws \Madj2k\AiCore\Exception\ApiException
     */
    public function process(
        Context                  $context,
        PipelineStepConfigurationInterface    $step,
        ?PipelineLogMetaData $logContext = null
    ): void;
}
