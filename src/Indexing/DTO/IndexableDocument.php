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

namespace Madj2k\AiCore\Indexing\DTO;

use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class IndexableDocument
 *
 * Standardized content object passed from indexers to indexing storage connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class IndexableDocument
{

    /**
     * Raw text content.
     *
     * @var string
     */
    protected string $content = '';


    /**
     * Content hash.
     *
     * @var string
     */
    protected string $contentHash = '';


    /**
     * Metadata.
     *
     * @var \Madj2k\AiCore\DTO\DocumentMetadata
     */
    protected DocumentMetadata $metadata;


    /**
     * Constructor.
     *
     * @param string $content Raw text content.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata|null $metadata Metadata.
     */
    public function __construct(string $content = '', ?DocumentMetadata $metadata = null)
    {
        $this->content = trim($content);
        $this->metadata = $metadata ?? new DocumentMetadata();
        $this->contentHash = sha1($this->content);
    }


    /**
     * Returns the raw text content.
     *
     * @return string Raw text content.
     */
    public function getContent(): string
    {
        return $this->content;
    }


    /**
     * Sets the raw text content.
     *
     * @param string $content Raw text content.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = trim($content);
        $this->contentHash = sha1($this->content);
    }


    /**
     * Returns the content hash.
     *
     * @return string Content hash.
     */
    public function getContentHash(): string
    {
        return $this->contentHash;
    }


    /**
     * Sets the content hash.
     *
     * @param string $contentHash Content hash.
     * @return void
     */
    public function setContentHash(string $contentHash): void
    {
        $this->contentHash = trim($contentHash);
    }


    /**
     * Returns the metadata.
     *
     * @return \Madj2k\AiCore\DTO\DocumentMetadata Metadata.
     */
    public function getMetadata(): DocumentMetadata
    {
        return $this->metadata;
    }


    /**
     * Sets the metadata.
     *
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Metadata.
     * @return void
     */
    public function setMetadata(DocumentMetadata $metadata): void
    {
        $this->metadata = $metadata;
    }


    /**
     * Creates a vector payload for one chunk.
     *
     * @param int $chunkIndex Chunk index.
     * @param string $chunkText Chunk text.
     * @param string $sourceHash Stable source hash.
     * @param string $indexGeneration Index generation used for failure-safe replacement.
     * @return array<string, mixed> Payload.
     */
    public function createPayload(
        int $chunkIndex,
        string $chunkText,
        string $sourceHash,
        string $indexGeneration = '',
    ): array
    {
        return [
            'text' => $chunkText,
            'meta' => array_merge($this->metadata->toArray(), [
                'source_hash' => trim($sourceHash),
                'content_hash' => $this->contentHash,
                'index_generation' => trim($indexGeneration),
                'chunk_index' => $chunkIndex,
            ]),
        ];
    }
}
