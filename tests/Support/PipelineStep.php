<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Support;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;

final readonly class PipelineStep implements PipelineStepConfigurationInterface
{
    /**
     * @param array<int, string> $metadataFields
     */
    public function __construct(
        private AssistantPipelineProcessorType $type = AssistantPipelineProcessorType::AnswerGenerator,
        private string $identifier = 'test.processor',
        private AssistantPipelineFailureStrategy $failureStrategy = AssistantPipelineFailureStrategy::Stop,
        private int $maxContextChunks = 6,
        private int $maxContextCharacters = 9000,
        private array $metadataFields = ['title', 'url'],
        private HistoryMode $historyMode = HistoryMode::None,
        private int $historyLimit = 0,
        private bool $includeIdentity = false,
        private bool $includeBehavior = false,
        private bool $includeRetrieval = false,
        private bool $includeOutput = false,
        private string $stepIdentity = '',
        private string $stepBehavior = '',
        private string $stepRetrieval = '',
        private string $stepOutput = '',
    ) {
    }

    public function getUid(): ?int { return 1; }
    public function getTitle(): string { return 'Test step'; }
    public function getType(): AssistantPipelineProcessorType { return $this->type; }
    public function getProcessorIdentifier(): string { return $this->identifier; }
    public function getStage(): AssistantPipelineStage { return AssistantPipelineStage::PreAnswer; }
    public function getIncludeIdentityPrompt(): bool { return $this->includeIdentity; }
    public function getIncludeBehaviorRules(): bool { return $this->includeBehavior; }
    public function getIncludeRetrievalRules(): bool { return $this->includeRetrieval; }
    public function getIncludeOutputRules(): bool { return $this->includeOutput; }
    public function getStepIdentity(): string { return $this->stepIdentity; }
    public function getStepBehaviorRules(): string { return $this->stepBehavior; }
    public function getStepRetrievalRules(): string { return $this->stepRetrieval; }
    public function getStepOutputRules(): string { return $this->stepOutput; }
    public function getHistoryMode(): HistoryMode { return $this->historyMode; }
    public function getHistoryLimit(): int { return $this->historyLimit; }
    public function getModel(): string { return ''; }
    public function getTemperature(): float { return 0.0; }
    public function getMaxTokens(): int { return 0; }
    public function getMaxRetrievalResults(): int { return 0; }
    public function getScoreThreshold(): float { return 0.0; }
    public function getMaxContextChunks(): int { return $this->maxContextChunks; }
    public function getMaxContextCharacters(): int { return $this->maxContextCharacters; }
    public function getPromptMetadataFieldList(): array { return $this->metadataFields; }
    public function getFailureStrategy(): AssistantPipelineFailureStrategy { return $this->failureStrategy; }
    public function isLlmStep(): bool
    {
        return !in_array($this->type, [AssistantPipelineProcessorType::Retriever, AssistantPipelineProcessorType::Memory], true);
    }
}
