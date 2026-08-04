<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Assistant;

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;

/**
 * Class AssistantContextFactory
 *
 * Converts an assistant configuration into a runtime context object.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
            collection: trim($assistantProfile->getCollection()),
            identityPrompt: trim($assistantProfile->getIdentityPrompt()),
            behaviorRules: trim($assistantProfile->getBehaviorRules()),
            retrievalRules: trim($assistantProfile->getRetrievalRules()),
            outputRules: trim($assistantProfile->getOutputRules()),
        );
    }
}
