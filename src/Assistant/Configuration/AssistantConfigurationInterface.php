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

namespace Madj2k\AiCore\Assistant\Configuration;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

/**
 * Interface AssistantConfigurationInterface
 *
 * Defines the framework-independent configuration required for one assistant profile.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface AssistantConfigurationInterface
{
    /**
     * Returns the optional persistent identifier
     *
     * @return int|null
     */
    public function getUid(): ?int;


    /**
     * Returns the internal profile title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Returns the assistant label shown to users.
     *
     * @return string
     */
    public function getAssistantLabel(): string;


    /**
     * Returns the assistant identity prompt.
     *
     * @return string
     */
    public function getIdentityPrompt(): string;


    /**
     * Returns the global behavior rules.
     *
     * @return string
     */
    public function getBehaviorRules(): string;

    /**
     * Returns the global retrieval rules.
     *
     * @return string
     */
    public function getRetrievalRules(): string;


    /**
     * Returns the global output rules.
     *
     * @return string
     */
    public function getOutputRules(): string;


    /**
     * Returns the configured AI connection
     *
     * @return \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface|null
     */
    public function getAiConnection(): ?AiConnectionConfigurationInterface;


    /**
     * Returns the configured vector store connection.
     *
     * @return \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface|null
     */
    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface;


    /**
     * Returns the ordered chat pipeline steps.
     *
     * @return iterable<\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface>
     */
    public function getChatPipelineSteps(): iterable;
}
