<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\DTO;

use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class LastRetrievalResult
 *
 * Represents one normalized retrieval snapshot that can be stored in the chat session.
 * It contains both raw source data for integrations and normalized retrieval documents for summaries.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class LastRetrievalResult
{

    /**
     * Chat identifier.
     *
     * @var string
     */
    public string $chatIdentifier;


    /**
     * Retrieval identifier.
     *
     * @var string
     */
    public string $retrievalIdentifier;


    /**
     * Original query.
     *
     * @var string
     */
    public string $query;


    /**
     * Optimized query.
     *
     * @var string
     */
    public string $optimizedQuery;


    /**
     * Normalized retrieval documents.
     *
     * @var array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument>
     */
    public array $documents;


    /**
     * Source-specific raw data.
     *
     * @var array<string,mixed>
     */
    public array $rawData;


    /**
     * Creation timestamp.
     *
     * @var int
     */
    public int $createdAt;


    /**
     * Constructor.
     *
     * @param string $chatIdentifier Chat identifier.
     * @param string $retrievalIdentifier Retrieval identifier.
     * @param string $query Original query.
     * @param string $optimizedQuery Optimized query.
     * @param array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument|array<string,mixed>|object|mixed> $documents Normalized retrieval documents or serialized document arrays.
     * @param array<string,mixed> $rawData Source-specific raw data.
     * @param int $createdAt Creation timestamp.
     */
    public function __construct(
        string $chatIdentifier = '',
        string $retrievalIdentifier = '',
        string $query = '',
        string $optimizedQuery = '',
        array $documents = [],
        array $rawData = [],
        int $createdAt = 0,
    ) {
        $this->chatIdentifier = $chatIdentifier;
        $this->retrievalIdentifier = $retrievalIdentifier;
        $this->query = $query;
        $this->optimizedQuery = $optimizedQuery;
        $this->documents = $this->normalizeDocuments($documents);
        $this->rawData = $rawData;
        $this->createdAt = $createdAt;
    }



    /**
     * Returns the number of retrieved documents.
     *
     * @return int Document count.
     */
    public function getDocumentCount(): int
    {
        return count($this->documents);
    }


    /**
     * Returns a serializable representation.
     *
     * @return array{identifier:string,chatIdentifier:string,retrievalIdentifier:string,source:string,processorIdentifier:string,query:string,optimizedQuery:string,createdAt:int,documents:array<int,array<string,mixed>>,rawData:array<string,mixed>}
     */
    public function toArray(): array
    {
        return [
            'chatIdentifier' => $this->chatIdentifier,
            'retrievalIdentifier' => $this->retrievalIdentifier,
            'query' => $this->query,
            'optimizedQuery' => $this->optimizedQuery,
            'createdAt' => $this->createdAt,
            'documents' => $this->documentsToArray($this->documents),
            'rawData' => $this->rawData,
        ];
    }


    /**
     * Normalizes retrieval documents.
     *
     * @param array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument|array<string,mixed>> $documents Retrieval documents.
     * @return array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument> Normalized documents.
     */
    private function normalizeDocuments(array $documents): array
    {
        $normalizedDocuments = [];

        foreach ($documents as $document) {
            if ($document instanceof RetrievalDocument) {
                $normalizedDocuments[] = $document;
                continue;
            }

            if (is_array($document)) {
                $normalizedDocuments[] = new RetrievalDocument(
                    (string)($document['id'] ?? ''),
                    (float)($document['score'] ?? 0.0),
                    (string)($document['text'] ?? ''),
                   is_array($document['documentMetadata'] ?? null) ? $document['documentMetadata'] : [],
                );
            }
        }

        return $normalizedDocuments;
    }


    /**
     * Converts retrieval documents to arrays.
     *
     * @param array<int,\Madj2k\AiCore\Assistant\DTO\RetrievalDocument|array<string,mixed>|object|mixed> $documents Retrieval documents.
     * @return array<int,array<string,mixed>> Serialized documents.
     */
    private function documentsToArray(array $documents): array
    {
        $serializedDocuments = [];

        foreach ($documents as $document) {
            if (is_array($document)) {
                $serializedDocuments[] = $document;
                continue;
            }

            if (is_object($document) && method_exists($document, 'toArray')) {
                /** @var mixed $documentData */
                $documentData = $document->toArray();
                if (is_array($documentData)) {
                    $serializedDocuments[] = $documentData;
                }
            }
        }

        return $serializedDocuments;
    }
}
