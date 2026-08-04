<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Indexing\Adapter;

use Madj2k\AiCore\Exception\JsonRecordIdentityException;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class JsonAdapter
 *
 * Extracts normalized text from JSON and JSONL files.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class JsonAdapter implements AdapterInterface, MultiDocumentAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.text.json';
    }


    /**
     * @inheritDoc
     */
    public function getSupportedExtensions(): array
    {
        return ['json', 'jsonl'];
    }


    /**
     * @inheritDoc
     */
    public function supports(string $path): bool
    {
        return in_array(strtolower((string)pathinfo($path, PATHINFO_EXTENSION)), $this->getSupportedExtensions(), true);
    }


    /**
     * @inheritDoc
     */
    public function extract(string $path, DocumentMetadata $metadata): string
    {
        /** @var string $raw */
        $raw = (string)file_get_contents($path);
        $extension = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
        $decoded = $this->decodeByExtension($raw, $extension);

        if ($decoded === null) {
            return trim($raw);
        }

        if (!is_array($decoded)) {
            return trim((string)$decoded);
        }

        /** @var array<string, mixed> $configuration */
        $configuration = $metadata->getAdditional();
        $jsonConfiguration = $this->extractJsonConfiguration($configuration);
        $textFields = $jsonConfiguration['textFields'];
        $metadataFields = $jsonConfiguration['metadataFields'];
        $metadata->setAdditional($configuration);

        if ($metadataFields !== []) {
            $this->addConfiguredMetadata($decoded, $metadataFields, $metadata);
        }

        if ($textFields !== []) {
            return trim($this->extractConfiguredText($decoded, $textFields));
        }

        $metadata->addAdditional('json', $decoded);
        return trim($this->flatten($decoded));
    }


    /**
     * @inheritDoc
     */
    public function extractDocuments(string $path, DocumentMetadata $metadata): array
    {
        /** @var string $raw */
        $raw = (string)file_get_contents($path);
        $extension = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
        $decoded = $this->decodeByExtension($raw, $extension);

        if ($decoded === null || !is_array($decoded) || !$this->containsMultipleDocuments($decoded)) {
            return [
                new IndexableDocument($this->extract($path, $metadata), $metadata),
            ];
        }

        /** @var array<int, array<int|string, mixed>> $records */
        $records = [];
        foreach (array_values($decoded) as $record) {
            if (!is_array($record)) {
                $record = ['value' => $record];
            }

            $records[] = (array)$record;
        }

        $recordIdentities = $this->buildRecordIdentities($records, $metadata);

        /** @var array<int, \Madj2k\AiCore\Indexing\DTO\IndexableDocument> $documents */
        $documents = [];
        foreach ($records as $index => $record) {
            /** @var \Madj2k\AiCore\DTO\DocumentMetadata $recordMetadata */
            $recordMetadata = clone $metadata;
            $content = $this->extractRecord($record, $recordMetadata, $index, $recordIdentities[$index]);
            $documents[] = new IndexableDocument($content, $recordMetadata);
        }

        return $documents;
    }


    /**
     * Returns whether decoded JSON should be treated as multiple documents.
     *
     * @param array<int|string, mixed> $decoded Decoded JSON.
     * @return bool True when the root value is a list.
     */
    private function containsMultipleDocuments(array $decoded): bool
    {
        return $decoded !== [] && array_is_list($decoded);
    }


    /**
     * Ensures configured JSON source IDs are unique within one multi-document file.
     *
     * @param array<int, mixed> $records Decoded JSON records.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Base metadata.
     * @return void
     * @throws \Madj2k\AiCore\Exception\JsonRecordIdentityException
     */
    private function assertUniqueConfiguredRecordIds(array $records, DocumentMetadata $metadata): void
    {
        $sourceIdField = trim((string)($metadata->getAdditional()['source_id_field'] ?? ''));
        if ($sourceIdField === '') {
            return;
        }

        /** @var array<string, array<int, int>> $recordNumbersById */
        $recordNumbersById = [];

        foreach ($records as $index => $record) {
            if (!is_array($record)) {
                $record = ['value' => $record];
            }

            $recordId = $this->resolveScalarPath((array)$record, $sourceIdField);
            if ($recordId === '') {
                continue;
            }

            $recordNumbersById[$recordId][] = $index + 1;
        }

        /** @var array<int, array{id:string,records:array<int, int>}> $duplicates */
        $duplicates = [];
        foreach ($recordNumbersById as $recordId => $recordNumbers) {
            if (count($recordNumbers) <= 1) {
                continue;
            }

            $duplicates[] = [
                'id' => $recordId,
                'records' => $recordNumbers,
            ];
        }

        if ($duplicates === []) {
            return;
        }

        throw new JsonRecordIdentityException(
            'Duplicate JSON record identifiers found for configured source_id_field.',
            [
                'type' => 'json_duplicate_record_ids',
                'source_id_field' => $sourceIdField,
                'duplicates' => $duplicates,
            ]
        );
    }


    /**
     * Extracts one JSON record into text and metadata.
     *
     * @param array<int|string, mixed> $record Decoded JSON record.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Metadata to enrich.
     * @param int $index Zero-based record index.
     * @param array{recordId:string,rawRecordId:string,warning:string} $recordIdentity Prepared record identity.
     * @return string Extracted text.
     */
    private function extractRecord(
        array $record,
        DocumentMetadata $metadata,
        int $index,
        array $recordIdentity
    ): string
    {
        /** @var array<string, mixed> $configuration */
        $configuration = $metadata->getAdditional();
        $jsonConfiguration = $this->extractJsonConfiguration($configuration);
        $textFields = $jsonConfiguration['textFields'];
        $metadataFields = $jsonConfiguration['metadataFields'];
        $metadata->setAdditional($configuration);
        $this->applyRecordIdentity($metadata, $index, $recordIdentity);

        if ($metadataFields !== []) {
            $this->addConfiguredMetadata($record, $metadataFields, $metadata);
        }

        if ($textFields !== []) {
            return trim($this->extractConfiguredText($record, $textFields));
        }

        $metadata->addAdditional('json', $record);
        return trim($this->flatten($record));
    }


    /**
     * Builds collision-safe record identities for one decoded JSON/JSONL file.
     *
     * @param array<int, array<int|string, mixed>> $records Decoded records.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Base metadata.
     * @return array<int, array{recordId:string,rawRecordId:string,warning:string}> Record identities.
     */
    private function buildRecordIdentities(array $records, DocumentMetadata $metadata): array
    {
        $sourceIdField = trim((string)($metadata->getAdditional()['source_id_field'] ?? ''));

        /** @var array<int, string> $rawRecordIds */
        $rawRecordIds = [];
        foreach ($records as $index => $record) {
            $rawRecordIds[$index] = $sourceIdField !== ''
                ? $this->resolveScalarPath($record, $sourceIdField)
                : '';
        }

        /** @var array<string, int> $counts */
        $counts = [];
        foreach ($rawRecordIds as $rawRecordId) {
            if ($rawRecordId === '') {
                continue;
            }

            $counts[$rawRecordId] = ($counts[$rawRecordId] ?? 0) + 1;
        }

        /** @var array<string, int> $occurrences */
        $occurrences = [];

        /** @var array<int, array{recordId:string,rawRecordId:string,warning:string}> $identities */
        $identities = [];
        foreach ($rawRecordIds as $index => $rawRecordId) {
            $recordNumber = $index + 1;
            $warning = '';

            if ($rawRecordId === '') {
                $recordId = 'record-' . $recordNumber;
                if ($sourceIdField !== '') {
                    $warning = 'missing_source_id_field';
                }
            } elseif (($counts[$rawRecordId] ?? 0) > 1) {
                $occurrences[$rawRecordId] = ($occurrences[$rawRecordId] ?? 0) + 1;
                $recordId = $rawRecordId . '@' . $occurrences[$rawRecordId];
                $warning = 'duplicate_source_id_field';
            } else {
                $recordId = $rawRecordId;
            }

            $identities[$index] = [
                'recordId' => $recordId,
                'rawRecordId' => $rawRecordId,
                'warning' => $warning,
            ];
        }

        return $identities;
    }


    /**
     * Adds record-specific source metadata.
     *
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Metadata to enrich.
     * @param int $index Zero-based record index.
     * @param array{recordId:string,rawRecordId:string,warning:string} $recordIdentity Prepared record identity.
     * @return void
     */
    private function applyRecordIdentity(DocumentMetadata $metadata, int $index, array $recordIdentity): void
    {
        $recordId = $recordIdentity['recordId'];

        $metadata->setSourceIdentifier($metadata->getSourceIdentifier() . '#' . $recordId);
        $metadata->addAdditional('json_record_index', $index);
        $metadata->addAdditional('json_record_id', $recordId);

        if ($recordIdentity['rawRecordId'] !== '') {
            $metadata->addAdditional('json_record_raw_id', $recordIdentity['rawRecordId']);
        }

        if ($recordIdentity['warning'] !== '') {
            $metadata->addAdditional('json_record_identity_warning', $recordIdentity['warning']);
        }
    }


    /**
     * Decodes a JSON document.
     *
     * @param string $raw Raw JSON.
     * @return mixed Decoded JSON value or null on parse error.
     */
    private function decodeJson(string $raw): mixed
    {
        /** @var mixed $decoded */
        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }


    /**
     * Decodes JSON by extension and accepts JSONL content as fallback.
     *
     * @param string $raw Raw JSON or JSONL.
     * @param string $extension File extension.
     * @return mixed Decoded JSON value or null on parse error.
     */
    private function decodeByExtension(string $raw, string $extension): mixed
    {
        if ($extension === 'jsonl') {
            return $this->decodeJsonLines($raw);
        }

        $decoded = $this->decodeJson($raw);
        if ($decoded !== null) {
            return $decoded;
        }

        return $this->decodeJsonLines($raw);
    }


    /**
     * Decodes newline-delimited JSON.
     *
     * @param string $raw Raw JSONL.
     * @return array<int, mixed>|null Decoded JSONL records or null on parse error.
     */
    private function decodeJsonLines(string $raw): ?array
    {
        /** @var array<int, mixed> $records */
        $records = [];

        foreach (preg_split('/\R/u', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            /** @var mixed $decoded */
            $decoded = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            $records[] = $decoded;
        }

        return $records;
    }


    /**
     * Extracts configured text fields from decoded JSON.
     *
     * @param array<int|string, mixed> $decoded Decoded JSON.
     * @param array<int, string> $fieldPaths Dot-notation field paths.
     * @return string Extracted text.
     */
    private function extractConfiguredText(array $decoded, array $fieldPaths): string
    {
        /** @var array<int, string> $parts */
        $parts = [];

        foreach ($fieldPaths as $fieldPath) {
            foreach ($this->resolvePath($decoded, $fieldPath) as $value) {
                $text = $this->flattenValue($value);
                if ($text !== '') {
                    $parts[] = $text;
                }
            }
        }

        return implode("\n", $parts);
    }


    /**
     * Adds configured JSON fields to metadata.
     *
     * @param array<int|string, mixed> $decoded Decoded JSON.
     * @param array<string, string> $fieldMap Metadata key to dot-notation field path map.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Metadata to enrich.
     * @return void
     */
    private function addConfiguredMetadata(array $decoded, array $fieldMap, DocumentMetadata $metadata): void
    {
        foreach ($fieldMap as $metadataKey => $fieldPath) {
            $values = $this->resolvePath($decoded, $fieldPath);
            if ($values === []) {
                continue;
            }

            $metadata->addAdditional(
                $metadataKey,
                count($values) === 1 ? $values[0] : array_values($values)
            );
        }
    }


    /**
     * Extracts JSON adapter configuration from additional metadata.
     *
     * @param array<string, mixed> $configuration Additional metadata configuration.
     * @return array{textFields: array<int, string>, metadataFields: array<string, string>}
     */
    private function extractJsonConfiguration(array &$configuration): array
    {
        $textFields = $this->normalizeFieldList($configuration['json_text_fields'] ?? []);
        $metadataFields = $this->normalizeMetadataFieldMap($configuration['json_metadata_fields'] ?? []);
        unset($configuration['json_text_fields'], $configuration['json_metadata_fields']);

        foreach ($configuration as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (str_starts_with($key, 'json_text_fields.')) {
                $fieldPath = trim(substr($key, strlen('json_text_fields.')));
                if ($fieldPath !== '') {
                    $textFields[] = $fieldPath;
                }

                unset($configuration[$key]);
                continue;
            }

            if (str_starts_with($key, 'json_metadata_fields.')) {
                $fieldPath = trim(substr($key, strlen('json_metadata_fields.')));
                $metadataKey = is_string($value) ? trim($value) : '';
                if ($fieldPath !== '') {
                    $metadataFields[$metadataKey !== '' ? $metadataKey : $this->buildMetadataKey($fieldPath)] = $fieldPath;
                }

                unset($configuration[$key]);
            }
        }

        return [
            'textFields' => array_values(array_unique($textFields)),
            'metadataFields' => $metadataFields,
        ];
    }


    /**
     * Normalizes a comma-separated string or array to a field path list.
     *
     * @param mixed $value Raw field configuration.
     * @return array<int, string> Field path list.
     */
    private function normalizeFieldList(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/u', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        /** @var array<int, string> $fields */
        $fields = [];
        foreach ($value as $field) {
            $field = trim((string)$field);
            if ($field !== '') {
                $fields[] = $field;
            }
        }

        return array_values(array_unique($fields));
    }


    /**
     * Normalizes metadata field configuration.
     *
     * @param mixed $value Raw metadata field configuration.
     * @return array<string, string> Metadata key to dot-notation field path map.
     */
    private function normalizeMetadataFieldMap(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/u', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        /** @var array<string, string> $fieldMap */
        $fieldMap = [];
        foreach ($value as $key => $fieldPath) {
            $fieldPath = trim((string)$fieldPath);
            if ($fieldPath === '') {
                continue;
            }

            $metadataKey = is_string($key) ? trim($key) : $this->buildMetadataKey($fieldPath);
            if ($metadataKey !== '') {
                $fieldMap[$metadataKey] = $fieldPath;
            }
        }

        return $fieldMap;
    }


    /**
     * Builds a metadata key from a dot-notation path.
     *
     * @param string $fieldPath Dot-notation field path.
     * @return string Metadata key.
     */
    private function buildMetadataKey(string $fieldPath): string
    {
        return trim((string)preg_replace('/[^a-zA-Z0-9_]+/', '_', $fieldPath), '_');
    }


    /**
     * Resolves a dot-notation path from decoded JSON.
     *
     * Supports numeric array indices and "*" as wildcard segment.
     *
     * @param mixed $value JSON value.
     * @param string $fieldPath Dot-notation field path.
     * @return array<int, mixed> Resolved values.
     */
    private function resolvePath(mixed $value, string $fieldPath): array
    {
        $segments = array_values(array_filter(
            array_map('trim', explode('.', $fieldPath)),
            static fn (string $segment): bool => $segment !== ''
        ));

        return $this->resolvePathSegments($value, $segments);
    }


    /**
     * Resolves the first scalar value for a dot-notation path.
     *
     * @param array<int|string, mixed> $value JSON value.
     * @param string $fieldPath Dot-notation field path.
     * @return string Scalar value or empty string.
     */
    private function resolveScalarPath(array $value, string $fieldPath): string
    {
        foreach ($this->resolvePath($value, $fieldPath) as $resolvedValue) {
            if (is_scalar($resolvedValue)) {
                return trim((string)$resolvedValue);
            }
        }

        return '';
    }


    /**
     * Resolves path segments recursively.
     *
     * @param mixed $value JSON value.
     * @param array<int, string> $segments Remaining path segments.
     * @return array<int, mixed> Resolved values.
     */
    private function resolvePathSegments(mixed $value, array $segments): array
    {
        if ($segments === []) {
            return [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $segment = array_shift($segments);
        if ($segment === '*') {
            /** @var array<int, mixed> $values */
            $values = [];
            foreach ($value as $item) {
                $values = array_merge($values, $this->resolvePathSegments($item, $segments));
            }

            return $values;
        }

        $key = ctype_digit($segment) ? (int)$segment : $segment;
        if (!array_key_exists($key, $value)) {
            if (array_is_list($value)) {
                /** @var array<int, mixed> $values */
                $values = [];
                foreach ($value as $item) {
                    $values = array_merge(
                        $values,
                        $this->resolvePathSegments($item, array_merge([$segment], $segments))
                    );
                }

                return $values;
            }

            return [];
        }

        return $this->resolvePathSegments($value[$key], $segments);
    }


    /**
     * Flattens JSON values into searchable text.
     *
     * @param mixed $value JSON value.
     * @return string Text.
     */
    private function flatten(mixed $value): string
    {
        if (is_scalar($value)) {
            return (string)$value;
        }

        if (!is_array($value)) {
            return '';
        }

        /** @var array<int, string> $parts */
        $parts = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $parts[] = $key;
            }

            $parts[] = $this->flatten($item);
        }

        return trim(implode("\n", array_filter($parts)));
    }


    /**
     * Flattens one configured JSON value without adding object keys.
     *
     * @param mixed $value JSON value.
     * @return string Text.
     */
    private function flattenValue(mixed $value): string
    {
        if (is_scalar($value)) {
            return trim((string)$value);
        }

        if (!is_array($value)) {
            return '';
        }

        /** @var array<int, string> $parts */
        $parts = [];
        foreach ($value as $item) {
            $text = $this->flattenValue($item);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode("\n", $parts));
    }
}
