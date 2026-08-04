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

/**
 * Class IndexingResult
 *
 * Collects indexing statistics and details.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class IndexingResult
{
    /**
     * @var int
     */
    protected int $processed = 0;


    /**
     * @var int
     */
    protected int $indexed = 0;


    /**
     * @var int
     */
    protected int $skipped = 0;

    /**
     * @var int
     */
    protected int $failed = 0;


    /**
     * @var int
     */
    protected int $removed = 0;


    /**
     * @var int
     */
    protected int $chunksTotal = 0;




    /**
     * Next cursor.
     *
     * @var string
     */
    protected string $nextCursor = '';


    /**
     * Has more flag.
     *
     * @var bool
     */
    protected bool $hasMore = false;


    /**
     * @var array<string, mixed>
     */
    protected array $details = [];


    /**
     * @param int $amount
     * @return void
     */
    public function increaseProcessed(int $amount = 1): void
    {
        $this->processed += $amount;
    }


    /**
     * @param int $amount
     * @return void
     */
    public function increaseIndexed(int $amount = 1): void
    {
        $this->indexed += $amount;
    }


    /**
     * @param int $amount
     * @return void
     */
    public function increaseSkipped(int $amount = 1): void
    {
        $this->skipped += $amount;
    }


    /**
     * @param int $amount
     * @return void
     */
    public function increaseFailed(int $amount = 1): void
    {
        $this->failed += $amount;
    }


    /**
     * @param int $amount
     * @return void
     */
    public function increaseRemoved(int $amount = 1): void
    {
        $this->removed += $amount;
    }


    /**
     * @param int $amount
     * @return void
     */
    public function increaseChunksTotal(int $amount = 1): void
    {
        $this->chunksTotal += $amount;
    }


    /**
     * @return int
     */
    public function getProcessed(): int
    {
        return $this->processed;
    }


    /**
     * @return int
     */
    public function getIndexed(): int
    {
        return $this->indexed;
    }


    /**
     * @return int
     */
    public function getSkipped(): int
    {
        return $this->skipped;
    }


    public function getFailed(): int
    {
        return $this->failed;
    }


    /**
     * @return int
     */
    public function getRemoved(): int
    {
        return $this->removed;
    }


    /**
     * @return int
     */
    public function getChunksTotal(): int
    {
        return $this->chunksTotal;
    }



    /**
     * Returns the next cursor.
     *
     * @return string Next cursor.
     */
    public function getNextCursor(): string
    {
        return $this->nextCursor;
    }


    /**
     * Sets the next cursor.
     *
     * @param string $nextCursor Next cursor.
     * @return void
     */
    public function setNextCursor(string $nextCursor): void
    {
        $this->nextCursor = trim($nextCursor);
    }


    /**
     * Returns whether more items are available.
     *
     * @return bool Has more flag.
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }


    /**
     * Sets whether more items are available.
     *
     * @param bool $hasMore Has more flag.
     * @return void
     */
    public function setHasMore(bool $hasMore): void
    {
        $this->hasMore = $hasMore;
    }


    /**
     * Adds a detail.
     *
     * @param string $key Detail key.
     * @param mixed $value Detail value.
     * @return void
     */
    public function addDetail(string $key, mixed $value): void
    {
        $this->details[$key] = $value;
    }


    /**
     * Returns details.
     *
     * @return array<string, mixed> Details.
     */
    public function getDetails(): array
    {
        return $this->details;
    }


    /**
     * Merges another result into this result.
     *
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingResult $result Result.
     * @return void
     */
    public function merge(IndexingResult $result): void
    {
        $this->processed += $result->getProcessed();
        $this->indexed += $result->getIndexed();
        $this->skipped += $result->getSkipped();
        $this->failed += $result->getFailed();
        $this->removed += $result->getRemoved();
        $this->chunksTotal += $result->getChunksTotal();
        $this->nextCursor = $result->getNextCursor() !== '' ? $result->getNextCursor() : $this->nextCursor;
        $this->hasMore = $this->hasMore || $result->hasMore();
        $this->details = array_merge($this->details, $result->getDetails());
    }


    /**
     * Exports the result as array.
     *
     * @return array<string, mixed> Result data.
     */
    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'indexed' => $this->indexed,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'removed' => $this->removed,
            'chunks_total' => $this->chunksTotal,
            'next_cursor' => $this->nextCursor,
            'has_more' => $this->hasMore,
            'details' => $this->details,
        ];
    }
}
