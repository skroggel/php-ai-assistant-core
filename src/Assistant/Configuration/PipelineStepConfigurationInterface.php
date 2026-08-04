<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Configuration;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;

/**
 * Interface PipelineStepConfigurationInterface
 *
 * Defines processor selection, prompt composition and execution limits for one pipeline step.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface PipelineStepConfigurationInterface
{
    /** Returns the optional persistent identifier. */
    public function getUid(): ?int;

    /** Returns the step title. */
    public function getTitle(): string;

    /** Returns the semantic processor type. */
    public function getType(): AssistantPipelineProcessorType;

    /** Returns the registered processor identifier. */
    public function getProcessorIdentifier(): string;

    /** Returns the pipeline stage. */
    public function getStage(): AssistantPipelineStage;

    /** Determines whether the global identity prompt is included. */
    public function getIncludeIdentityPrompt(): bool;

    /** Determines whether the global behavior rules are included. */
    public function getIncludeBehaviorRules(): bool;

    /** Determines whether the global retrieval rules are included. */
    public function getIncludeRetrievalRules(): bool;

    /** Determines whether the global output rules are included. */
    public function getIncludeOutputRules(): bool;

    /** Returns the step-specific identity prompt. */
    public function getStepIdentity(): string;

    /** Returns the step-specific behavior rules. */
    public function getStepBehaviorRules(): string;

    /** Returns the step-specific retrieval rules. */
    public function getStepRetrievalRules(): string;

    /** Returns the step-specific output rules. */
    public function getStepOutputRules(): string;

    /** Returns the conversation history mode. */
    public function getHistoryMode(): HistoryMode;

    /** Returns the maximum number of history entries. */
    public function getHistoryLimit(): int;

    /** Returns the model override or an empty string for the connection default. */
    public function getModel(): string;

    /** Returns the sampling temperature. */
    public function getTemperature(): float;

    /** Returns the maximum number of generated tokens. */
    public function getMaxTokens(): int;

    /** Returns the maximum number of retrieval results. */
    public function getMaxRetrievalResults(): int;

    /** Returns the minimum retrieval score. */
    public function getScoreThreshold(): float;

    /** Returns the maximum number of context chunks. */
    public function getMaxContextChunks(): int;

    /** Returns the maximum context length in characters. */
    public function getMaxContextCharacters(): int;

    /**
     * Returns metadata fields exposed to prompt context builders.
     *
     * @return array<int, string>
     */
    public function getPromptMetadataFieldList(): array;

    /** Returns the strategy applied when the processor fails. */
    public function getFailureStrategy(): AssistantPipelineFailureStrategy;

    /** Determines whether the configured processor calls an LLM. */
    public function isLlmStep(): bool;
}
