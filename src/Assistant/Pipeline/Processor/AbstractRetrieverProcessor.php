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

namespace Madj2k\AiCore\Assistant\Pipeline\Processor;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalGroup;
use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class AbstractRetrieverProcessor
 *
 * Shared helper logic for retriever processors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
abstract readonly class AbstractRetrieverProcessor implements ProcessorInterface
{
    /**
     * Stores a complete, named retrieval run using the common merge semantics.
     *
     * @param Context $context Current assistant context.
     * @param PipelineStepConfigurationInterface $step Current pipeline step.
     * @param string $processorIdentifier Retriever processor identifier.
     * @param array<int, RetrievalDocument> $documents Normalized retrieval documents.
     * @param array<int, mixed> $rawResults Source-specific raw results.
     * @param string $query Effective retrieval query.
     * @param string $collection Effective vector collection.
     * @return void
     */
    protected function storeRetrievalGroup(
        Context $context,
        PipelineStepConfigurationInterface $step,
        string $processorIdentifier,
        array $documents,
        array $rawResults,
        string $query = '',
        string $collection = '',
    ): void {
        $retrievalTitle = trim($step->getTitle());
        if ($retrievalTitle === '') {
            $retrievalTitle = $step->getUid() !== null && $step->getUid() > 0
                ? 'retrieval-' . $step->getUid()
                : $processorIdentifier;
        }

        $context->getRetrieval()->storeGroup(new RetrievalGroup(
            identifier: $retrievalTitle,
            processorIdentifier: $processorIdentifier,
            query: trim($query) !== '' ? trim($query) : $context->getCurrentQuery(),
            documents: $documents,
            rawResults: $rawResults,
            maxContextChunks: $step->getMaxContextChunks(),
            maxContextCharacters: $step->getMaxContextCharacters(),
            promptMetadataFields: $step->getPromptMetadataFieldList(),
            collection: trim($collection),
        ));
    }

    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::Retriever;
    }


    /**
     * Extracts the content text from a retriever payload.
     *
     * @param array<string, mixed> $payload Payload.
     * @return string Payload text.
     */
    protected function extractText(array $payload): string
    {
        foreach (['text', 'content', 'body', 'chunk'] as $field) {
            $value = trim((string)($payload[$field] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }


    /**
     * Extracts and normalizes source metadata from a retriever payload.
     *
     * The base handling is intentionally the same as in the default retriever:
     * root payload data, nested meta data and nested additional data are merged
     * before the DocumentMetadata object is created.
     *
     * Fields configured on the pipeline step are added to DocumentMetadata::additional.
     * For external retrievers, pass the original source row as $additionalSource.
     *
     * @param array<string, mixed> $payload Payload.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface|null $step Pipeline step.
     * @param array<string, mixed> $additionalSource Optional source data for configured metadata fields.
     * @return \Madj2k\AiCore\DTO\DocumentMetadata Document metadata.
     */
    protected function extractMetadata(
        array $payload,
        ?PipelineStepConfigurationInterface $step = null,
        array $additionalSource = []
    ): DocumentMetadata {
        $metadata = $this->flattenMetadataPayload($payload);

        $documentMetadata = new DocumentMetadata(
            sourceType: (string)($metadata['source_type'] ?? ''),
            sourceIdentifier: (string)($metadata['source_identifier'] ?? $metadata['source_id'] ?? ''),
            title: (string)($metadata['title'] ?? ''),
            url: (string)($metadata['url'] ?? ''),
            language: (string)($metadata['language'] ?? ''),
            languageId: (int)($metadata['language_id'] ?? -1),
            pageId: (int)($metadata['page_id'] ?? 0),
            path: (string)($metadata['path'] ?? ''),
            filename: (string)($metadata['filename'] ?? ''),
            changedAt: (int)($metadata['changed_at'] ?? 0),
            additional: $metadata
        );

        if ($step instanceof PipelineStepConfigurationInterface) {
            $this->addConfiguredMetadataFieldsToAdditional(
                $documentMetadata,
                $step,
                $additionalSource !== [] ? $additionalSource : $metadata,
                $metadata
            );
        }

        return $documentMetadata;
    }


    /**
     * Flattens root payload, meta and additional data.
     *
     * @param array<string, mixed> $payload Payload.
     * @return array<string, mixed> Flattened metadata.
     */
    protected function flattenMetadataPayload(array $payload): array
    {
        $metadata = $payload;

        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $metadata = array_merge($metadata, $payload['meta']);
        }

        /** @var mixed $additional */
        $additional = $metadata['additional'] ?? [];
        if (is_array($additional)) {
            $metadata = array_merge($metadata, $additional);
        }

        unset($metadata['meta'], $metadata['additional']);

        return $metadata;
    }


    /**
     * Adds the fields configured in the pipeline step to additional metadata.
     *
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Document metadata.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @param array<string, mixed> $sourceData Source data.
     * @param array<string, mixed> $fallbackData Fallback data.
     * @return void
     */
    protected function addConfiguredMetadataFieldsToAdditional(
        DocumentMetadata $metadata,
        PipelineStepConfigurationInterface $step,
        array $sourceData,
        array $fallbackData = []
    ): void {
        foreach ($this->getConfiguredMetadataFields($step) as $field) {
            if ($field === '') {
                continue;
            }

            if (array_key_exists($field, $sourceData)) {
                $metadata->addAdditional($field, $this->normalizeMetadataValue($sourceData[$field]));
                continue;
            }

            if (array_key_exists($field, $fallbackData)) {
                $metadata->addAdditional($field, $this->normalizeMetadataValue($fallbackData[$field]));
            }
        }
    }


    /**
     * Returns configured metadata fields for prompt context and source output.
     *
     * @param \\Madj2k\\AiCore\\Assistant\\Domain\\Model\\PipelineStepConfigurationInterface $step Pipeline step.
     * @return array<int, string> Field names.
     */
    protected function getConfiguredMetadataFields(PipelineStepConfigurationInterface $step): array
    {
        return array_values(array_unique(array_filter(
            $step->getPromptMetadataFieldList(),
            static fn ($field): bool => trim((string)$field) !== ''
        )));
    }


    /**
     * Normalizes metadata values before storing them as additional metadata.
     *
     * @param mixed $value Raw value.
     * @return mixed Normalized value.
     */
    protected function normalizeMetadataValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }
}
