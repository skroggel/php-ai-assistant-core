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

namespace Madj2k\AiCore\Assistant\Context\Assistant;

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;

/**
 * Class AssistantContextFactory
 *
 * Converts an assistant configuration into a runtime context object.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class AssistantContextFactory
{
    /**
     * Creates the assistant context from a configuration object.
     *
     * @param \Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface $assistantProfile Assistant profile.
     * @return \Madj2k\AiCore\Assistant\Context\Assistant\AssistantContext
     */
    public function create(AssistantConfigurationInterface $assistantProfile): AssistantContext
    {
        return new AssistantContext(
            uid: (int)$assistantProfile->getUid(),
            title: trim($assistantProfile->getTitle()),
            assistantLabel: trim($assistantProfile->getAssistantLabel()),
            aiConnection: $assistantProfile->getAiConnection(),
            vectorStoreConnection: $assistantProfile->getVectorStoreConnection(),
            identityPrompt: trim($assistantProfile->getIdentityPrompt()),
            behaviorRules: trim($assistantProfile->getBehaviorRules()),
            retrievalRules: trim($assistantProfile->getRetrievalRules()),
            outputRules: trim($assistantProfile->getOutputRules()),
        );
    }
}
