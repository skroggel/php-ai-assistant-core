<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Retrieval;

use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;

/**
 * Class RetrievalGroup
 *
 * Represents one named retrieval run with its source, query and prompt budget.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class RetrievalGroup
{
    /**
     * Normalized retrieval documents.
     *
     * @var array<int, RetrievalDocument>
     */
    public array $documents;


    /**
     * Source-specific raw results.
     *
     * @var array<int, mixed>
     */
    public array $rawResults;


    /**
     * Metadata fields included in the prompt context.
     *
     * @var array<int, string>
     */
    public array $promptMetadataFields;


    /**
     * Constructor.
     *
     * @param string $identifier Prompt-visible retrieval name derived from the retriever-step title.
     * @param string $processorIdentifier Identifier of the producing processor.
     * @param string $query Query used for this retrieval.
     * @param array<int, RetrievalDocument> $documents Normalized retrieval documents.
     * @param array<int, mixed> $rawResults Source-specific raw results.
     * @param int $maxContextChunks Maximum prompt chunks for this retrieval.
     * @param int $maxContextCharacters Maximum prompt characters for this retrieval.
     * @param array<int, string> $promptMetadataFields Metadata fields included in the prompt context.
     * @param string $collection Vector collection used for this retrieval.
     */
    public function __construct(
        public string $identifier,
        public string $processorIdentifier,
        public string $query,
        array $documents = [],
        array $rawResults = [],
        public int $maxContextChunks = 0,
        public int $maxContextCharacters = 0,
        array $promptMetadataFields = [],
        public string $collection = '',
    ) {
        $this->documents = array_values(array_filter(
            $documents,
            static fn (mixed $document): bool => $document instanceof RetrievalDocument,
        ));
        $this->rawResults = self::normalizeRawResults($rawResults);
        $this->promptMetadataFields = array_values(array_unique(array_filter(array_map(
            static fn (mixed $field): string => trim((string)$field),
            $promptMetadataFields,
        ))));
    }

    /**
     * Returns a serializable representation.
     *
     * @return array<string, mixed> Serialized retrieval group.
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'processorIdentifier' => $this->processorIdentifier,
            'query' => $this->query,
            'documents' => array_map(
                static fn (RetrievalDocument $document): array => $document->toArray(),
                $this->documents,
            ),
            'rawResults' => $this->rawResults,
            'maxContextChunks' => $this->maxContextChunks,
            'maxContextCharacters' => $this->maxContextCharacters,
            'promptMetadataFields' => $this->promptMetadataFields,
            'collection' => $this->collection,
        ];
    }

    /**
     * Creates a retrieval group from serialized data.
     *
     * @param array<string, mixed> $data Serialized retrieval group.
     * @return self Retrieval group.
     */
    public static function fromArray(array $data): self
    {
        $documents = [];
        foreach (is_array($data['documents'] ?? null) ? $data['documents'] : [] as $document) {
            if ($document instanceof RetrievalDocument) {
                $documents[] = $document;
            } elseif (is_array($document)) {
                $documents[] = new RetrievalDocument(
                    id: (string)($document['id'] ?? ''),
                    score: (float)($document['score'] ?? 0.0),
                    text: (string)($document['text'] ?? ''),
                    documentMetadata: is_array($document['metadata'] ?? null)
                        ? $document['metadata']
                        : (is_array($document['documentMetadata'] ?? null) ? $document['documentMetadata'] : []),
                );
            }
        }

        return new self(
            identifier: trim((string)($data['identifier'] ?? '')),
            processorIdentifier: trim((string)($data['processorIdentifier'] ?? '')),
            query: trim((string)($data['query'] ?? '')),
            documents: $documents,
            rawResults: is_array($data['rawResults'] ?? null) ? $data['rawResults'] : [],
            maxContextChunks: (int)($data['maxContextChunks'] ?? 0),
            maxContextCharacters: (int)($data['maxContextCharacters'] ?? 0),
            promptMetadataFields: is_array($data['promptMetadataFields'] ?? null) ? $data['promptMetadataFields'] : [],
            collection: trim((string)($data['collection'] ?? '')),
        );
    }

    /**
     * Normalizes raw results for serialization.
     *
     * @param array<int, mixed> $rawResults Raw results.
     * @return array<int, mixed> Normalized raw results.
     */
    private static function normalizeRawResults(array $rawResults): array
    {
        $normalized = [];
        foreach ($rawResults as $rawResult) {
            if (is_object($rawResult) && method_exists($rawResult, 'toArray')) {
                $normalized[] = $rawResult->toArray();
            } elseif (!is_object($rawResult)) {
                $normalized[] = $rawResult;
            }
        }

        return $normalized;
    }
}
