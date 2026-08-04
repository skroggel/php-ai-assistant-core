<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Configuration;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;

interface PipelineStepConfigurationInterface
{
    public function getUid(): ?int;
    public function getTitle(): string;
    public function getType(): AssistantPipelineProcessorType;
    public function getProcessorIdentifier(): string;
    public function getStage(): AssistantPipelineStage;
    public function getIncludeIdentityPrompt(): bool;
    public function getIncludeBehaviorRules(): bool;
    public function getIncludeRetrievalRules(): bool;
    public function getIncludeOutputRules(): bool;
    public function getStepIdentity(): string;
    public function getStepBehaviorRules(): string;
    public function getStepRetrievalRules(): string;
    public function getStepOutputRules(): string;
    public function getHistoryMode(): HistoryMode;
    public function getHistoryLimit(): int;
    public function getModel(): string;
    public function getTemperature(): float;
    public function getMaxTokens(): int;
    public function getMaxRetrievalResults(): int;
    public function getScoreThreshold(): float;
    public function getMaxContextChunks(): int;
    public function getMaxContextCharacters(): int;

    /** @return array<int, string> */
    public function getPromptMetadataFieldList(): array;

    public function getFailureStrategy(): AssistantPipelineFailureStrategy;
    public function isLlmStep(): bool;
}
