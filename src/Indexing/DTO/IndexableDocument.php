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

namespace Madj2k\AiCore\Indexing\DTO;

use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Class IndexableDocument
 *
 * Standardized content object passed from indexers to indexing storage connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
     * @return array<string, mixed> Payload.
     */
    public function createPayload(int $chunkIndex, string $chunkText, string $sourceHash): array
    {
        return [
            'text' => $chunkText,
            'meta' => array_merge($this->metadata->toArray(), [
                'source_hash' => trim($sourceHash),
                'content_hash' => $this->contentHash,
                'chunk_index' => $chunkIndex,
            ]),
        ];
    }
}
