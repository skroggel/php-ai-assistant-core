<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class AbstractRetrieverProcessor
 *
 * Shared helper logic for retriever processors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
abstract readonly class AbstractRetrieverProcessor implements ProcessorInterface
{

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
            language: (int)($metadata['language'] ?? 0),
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
