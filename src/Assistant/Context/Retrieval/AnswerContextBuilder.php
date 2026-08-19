<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Retrieval;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;

/**
 * Class AnswerContextBuilder
 *
 * Converts retrieved documents and their metadata into compact prompt context.
 *
 * @internal Prompt context implementations may change independently of the public builder contract.
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AnswerContextBuilder
{
    /**
     * Builds an answer context from retrieved documents.
     *
     * @param array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument> $documents Retrieved documents.
     * @param PipelineStepConfigurationInterface $step Step configuration.
     * @return string
     */
    public function build(array $documents, PipelineStepConfigurationInterface $step): string
    {
        return $this->buildWithLimits(
            $documents,
            $step->getMaxContextChunks(),
            $step->getMaxContextCharacters(),
            $step->getPromptMetadataFieldList(),
        );
    }


    /**
     * Builds one retrieval group using its individual prompt budget.
     *
     * @param RetrievalGroup $group Retrieval group.
     * @return string Prompt-ready retrieval context.
     */
    public function buildGroup(RetrievalGroup $group): string
    {
        return $this->buildWithLimits(
            $group->documents,
            $group->maxContextChunks,
            $group->maxContextCharacters,
            $group->promptMetadataFields,
        );
    }


    /**
     * Builds prompt context with explicit retrieval-specific limits.
     *
     * @param array<int, RetrievalDocument> $documents Retrieval documents.
     * @param int $maxContextChunks Maximum number of context chunks.
     * @param int $maxContextCharacters Maximum number of context characters.
     * @param array<int, string> $metadataFields Metadata fields included in the prompt.
     * @return string Prompt-ready retrieval context.
     */
    private function buildWithLimits(
        array $documents,
        int $maxContextChunks,
        int $maxContextCharacters,
        array $metadataFields,
    ): string {

        /** @var array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument> $limitedDocuments */
        $limitedDocuments = $maxContextChunks > 0
            ? array_slice($documents, 0, $maxContextChunks)
            : $documents;

        /** @var array<int,string> $chunks */
        $chunks = [];

        /** @var int $characters */
        $characters = 0;

        foreach ($limitedDocuments as $document) {
            $chunk = $this->formatDocument($document, $metadataFields);
            if ($chunk === '') {
                continue;
            }

            if ($maxContextCharacters > 0 && ($characters + strlen($chunk)) > $maxContextCharacters) {
                break;
            }

            $chunks[] = $chunk;
            $characters += strlen($chunk);
        }

        return implode("\n\n---\n\n", $chunks);
    }


    /**
     * Applies the configured character limit to an already optimized answer context.
     *
     * @param string $answerContext Answer context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step configuration.
     * @return string Limited answer context.
     */
    public function limit(string $answerContext, PipelineStepConfigurationInterface $step): string
    {
        $answerContext = trim($answerContext);
        if ($answerContext === '') {
            return '';
        }

        /** @var int $maxContextCharacters */
        $maxContextCharacters = $step->getMaxContextCharacters();

        if ($maxContextCharacters <= 0 || strlen($answerContext) <= $maxContextCharacters) {
            return $answerContext;
        }

        return rtrim(substr($answerContext, 0, $maxContextCharacters));
    }


    /**
     * Formats one retrieved document for a prompt.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\RetrievalDocument $document Retrieved document.
     * @param array<int,string> $metadataFields Metadata fields.
     * @return string
     */
    private function formatDocument(RetrievalDocument $document, array $metadataFields): string
    {
        if (trim($document->text) === '') {
            return '';
        }

        $lines = [];
        $metadataLines = $this->formatMetadataLines($document, $metadataFields);

        if ($metadataLines !== []) {
            $lines[] = 'source:';
            $lines = array_merge($lines, $metadataLines);
            $lines[] = '';
        }

        $lines[] = 'content:';
        $lines[] = trim($document->text);

        return implode("\n", $lines);
    }


    /**
     * Formats selected document metadata as source lines for the prompt.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\RetrievalDocument $document Retrieved document.
     * @param array<int,string> $metadataFields Metadata field names.
     * @return array<int,string> Formatted source metadata lines.
     */
    private function formatMetadataLines(RetrievalDocument $document, array $metadataFields): array
    {
        $lines = [];

        foreach ($metadataFields as $field) {
            $field = trim($field);
            if ($field === '') {
                continue;
            }

            $value = $this->normalizeMetadataValue($document->getMetadataValue($field));
            if ($value === '') {
                continue;
            }

            $lines[] = sprintf('  %s: %s', $field, $value);
        }

        return $lines;
    }


    /**
     * Normalizes a metadata value for compact prompt output.
     *
     * @param mixed $value Metadata value.
     * @return string Prompt-safe metadata value.
     */
    private function normalizeMetadataValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value) || is_object($value)) {
            $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return trim((string)($encodedValue ?: ''));
        }

        return trim((string)$value);
    }


}
