<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Configuration;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

/**
 * Interface AssistantConfigurationInterface
 *
 * Defines the framework-independent configuration required for one assistant profile.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface AssistantConfigurationInterface
{
    /** Returns the optional persistent identifier. */
    public function getUid(): ?int;

    /** Returns the internal profile title. */
    public function getTitle(): string;

    /** Returns the assistant label shown to users. */
    public function getAssistantLabel(): string;

    /** Returns the vector collection name. */
    public function getCollection(): string;

    /** Returns the assistant identity prompt. */
    public function getIdentityPrompt(): string;

    /** Returns the global behavior rules. */
    public function getBehaviorRules(): string;

    /** Returns the global retrieval rules. */
    public function getRetrievalRules(): string;

    /** Returns the global output rules. */
    public function getOutputRules(): string;

    /** Returns the configured AI connection. */
    public function getAiConnection(): ?AiConnectionConfigurationInterface;

    /** Returns the configured vector store connection. */
    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface;

    /**
     * Returns the ordered chat pipeline steps.
     *
     * @return iterable<PipelineStepConfigurationInterface>
     */
    public function getChatPipelineSteps(): iterable;
}
