<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Configuration;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

interface AssistantConfigurationInterface
{
    public function getUid(): ?int;
    public function getTitle(): string;
    public function getAssistantLabel(): string;
    public function getCollection(): string;
    public function getIdentityPrompt(): string;
    public function getBehaviorRules(): string;
    public function getRetrievalRules(): string;
    public function getOutputRules(): string;
    public function getAiConnection(): ?AiConnectionConfigurationInterface;
    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface;

    /** @return iterable<PipelineStepConfigurationInterface> */
    public function getChatPipelineSteps(): iterable;
}
