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
 * @author Steffen Kroggel <developer@steffenkroggel.de>
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
        /** @var int $maxContextChunks */
        $maxContextChunks = $step->getMaxContextChunks();

        /** @var array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument> $limitedDocuments */
        $limitedDocuments = $maxContextChunks > 0
            ? array_slice($documents, 0, $maxContextChunks)
            : $documents;

        /** @var array<int,string> $chunks */
        $chunks = [];

        /** @var int $characters */
        $characters = 0;

        foreach ($limitedDocuments as $document) {
            $chunk = $this->formatDocument($document, $step->getPromptMetadataFieldList());
            if ($chunk === '') {
                continue;
            }

            if ($this->exceedsMaximumContextCharacters($characters, $chunk, $step)) {
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


    /**
     * Checks whether adding the next chunk would exceed the configured context character limit.
     *
     * @param int $currentCharacters Current context characters.
     * @param string $chunk Candidate context chunk.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step configuration.
     * @return bool Whether the maximum would be exceeded.
     */
    private function exceedsMaximumContextCharacters(
        int $currentCharacters,
        string $chunk,
        PipelineStepConfigurationInterface $step
    ): bool {
        /** @var int $maxContextCharacters */
        $maxContextCharacters = $step->getMaxContextCharacters();

        return $maxContextCharacters > 0
            && ($currentCharacters + strlen($chunk)) > $maxContextCharacters;
    }
}
