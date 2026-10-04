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
namespace Madj2k\AiCore\Indexing;

/**
 * Class TextChunker
 *
 * Splits text into chunks before it is passed to indexing storage.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class TextChunker
{
    /**
     * Default chunk size.
     *
     * @var int
     */
    protected int $chunkSize;


    /**
     * Default chunk overlap.
     *
     * @var int
     */
    protected int $chunkOverlap;


    /**
     * Default minimum chunk length.
     *
     * @var int
     */
    protected int $minChunkChars;


    /**
     * Constructor.
     *
     * @param int $chunkSize Default chunk size.
     * @param int $chunkOverlap Default chunk overlap.
     * @param int $minChunkChars Default minimum chunk length.
     */
    public function __construct(int $chunkSize = 1200, int $chunkOverlap = 150, int $minChunkChars = 1)
    {
        $this->chunkSize = max(1, $chunkSize);
        $this->chunkOverlap = max(0, min($chunkOverlap, $this->chunkSize - 1));
        $this->minChunkChars = max(1, $minChunkChars);
    }


    /**
     * Splits text into chunks.
     *
     * Passing null for chunk settings means that service defaults are used. This is important because
     * persisted values of 0 in the indexer configuration mean "use defaults".
     *
     * @param string $text Source text to normalize and split.
     * @param int|null $chunkSize Maximum number of characters per chunk, or null for the configured default.
     * @param int|null $chunkOverlap Number of characters repeated between adjacent chunks, or null for the configured default.
     * @param int|null $maxChunks Maximum number of returned chunks, or null for no explicit limit.
     * @param int|null $minChunkChars Minimum number of characters required for a chunk, or null for the configured default.
     * @return array<int, string> Chunk texts in source order.
     */
    public function chunk(
        string $text,
        ?int $chunkSize = null,
        ?int $chunkOverlap = null,
        ?int $maxChunks = null,
        ?int $minChunkChars = null
    ): array {
        return array_column($this->chunkWithOffsets(
            $text,
            $chunkSize,
            $chunkOverlap,
            $maxChunks,
            $minChunkChars,
        ), 'text');
    }


    /**
     * Splits text into chunks and exposes their character ranges in the
     * whitespace-normalized source text. Keeping this calculation here makes
     * diagnostic previews use precisely the same boundaries as indexing.
     *
     * @param string $text Source text to normalize and split.
     * @param int|null $chunkSize Maximum number of characters per chunk, or null for the configured default.
     * @param int|null $chunkOverlap Number of characters repeated between adjacent chunks, or null for the configured default.
     * @param int|null $maxChunks Maximum number of returned chunks, or null for no explicit limit.
     * @param int|null $minChunkChars Minimum number of characters required for a chunk, or null for the configured default.
     * @return array<int, array{text: string, start: int, end: int}> Chunk texts with zero-based inclusive start and exclusive end offsets.
     */
    public function chunkWithOffsets(
        string $text,
        ?int $chunkSize = null,
        ?int $chunkOverlap = null,
        ?int $maxChunks = null,
        ?int $minChunkChars = null
    ): array {
        // Offsets must reference the exact normalized payload written to the
        // vector store, so normalization happens before both slicing and counting.
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        if ($text === '') {
            return [];
        }

        /** @var int $size */
        $size = max(1, $chunkSize ?? $this->chunkSize);

        /** @var int $overlap */
        $overlap = max(0, min($chunkOverlap ?? $this->chunkOverlap, $size - 1));

        /** @var int $minimum */
        $minimum = max(1, $minChunkChars ?? $this->minChunkChars);

        /** @var int|null $limit */
        $limit = ($maxChunks ?? 0) > 0
            ? (int)$maxChunks
            : null;

        /** @var array<int, array{text: string, start: int, end: int}> $chunks */
        $chunks = [];

        /** @var int $offset */
        $offset = 0;

        /** @var int $length */
        $length = mb_strlen($text);

        while ($offset < $length) {
            /** @var string $rawChunk */
            $rawChunk = mb_substr($text, $offset, $size);

            /** @var string $chunk */
            $chunk = trim($rawChunk);

            if (mb_strlen($chunk) >= $minimum) {
                $leadingWhitespace = mb_strlen($rawChunk) - mb_strlen(ltrim($rawChunk));
                $start = $offset + $leadingWhitespace;
                $chunks[] = [
                    'text' => $chunk,
                    'start' => $start,
                    'end' => $start + mb_strlen($chunk),
                ];
            }

            if ($limit !== null && count($chunks) >= $limit) {
                break;
            }

            /** @var int $nextOffset */
            $nextOffset = $offset + $size - $overlap;

            if ($nextOffset <= $offset) {
                break;
            }

            $offset = $nextOffset;
        }

        return $chunks;
    }
}
