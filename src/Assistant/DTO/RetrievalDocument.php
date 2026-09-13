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

namespace Madj2k\AiCore\Assistant\DTO;

use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class RetrievalDocument
 *
 * Represents one normalized document chunk returned by a retrieval connector.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class RetrievalDocument
{
    /**
     * Result identifier.
     *
     * @var string
     */
    public string $id;


    /**
     * Retrieval score.
     *
     * @var float
     */
    public float $score;


    /**
     * Main text sent to the answer context.
     *
     * @var string
     */
    public string $text;


    /**
     * Structured document metadata.
     *
     * @var \Madj2k\AiCore\DTO\DocumentMetadata
     */
    public DocumentMetadata $documentMetadata;


    /**
     * Constructor.
     *
     * @param string $id Result identifier.
     * @param float $score Retrieval score.
     * @param string $text Main text sent to the answer context.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata|array $documentMetadata
     */
    public function __construct(
        string $id,
        float $score,
        string $text,
        DocumentMetadata|array $documentMetadata,
    ) {
        $this->id = $id;
        $this->score = $score;
        $this->text = $text;
        $this->documentMetadata = is_array($documentMetadata)
            ? DocumentMetadata::fromArray($documentMetadata)
            : $documentMetadata;
    }


    /**
     * Returns the structured document metadata.
     *
     * @return \Madj2k\AiCore\DTO\DocumentMetadata Metadata.
     */
    public function getDocumentMetadata(): DocumentMetadata
    {
        return $this->documentMetadata;
    }


    /**
     * Returns a serializable representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'text' => $this->text,
            'metadata' => $this->getDocumentMetadata()->toArray(),
        ];
    }



    /**
     * Returns a compact representation
     *
     * @return array<string,mixed>
     */
    public function toTraceArray(): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'text_excerpt' => mb_substr($this->text, 0, 300, 'UTF-8'),
            'metadata' => $this->getDocumentMetadata()->toArray(),
        ];
    }


    /**
     * Returns one normalized metadata value.
     *
     * @param string $field Metadata field name.
     * @return mixed Metadata value.
     */
    public function getMetadataValue(string $field): mixed
    {
        return match ($field) {
            'id' => $this->id,
            'score' => $this->score,
            'text' => $this->text,
            default => $this->documentMetadata->getValue($field) ?? null,
        };
    }


    /**
     * Returns selected metadata fields for presentation-layer source output.
     *
     * @param array<int,string> $fields Source field names.
     * @return array<string,mixed> Source data.
     */
    public function toSourceArray(array $fields): array
    {
        $source = [];

        foreach ($fields as $field) {
            $value = $this->getMetadataValue($field);
            if ($value === null || $value === '') {
                continue;
            }

            $source[$field] = $value;
        }

        return $source;
    }
}
