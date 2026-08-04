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
 * Class IndexingRequest
 *
 * Contains command options used to execute an indexer.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class IndexingRequest
{
    /**
     * Indexer identifier.
     *
     * @var string
     */
    protected string $indexerIdentifier = '';


    /**
     * Indexer uid.
     *
     * @var int|null
     */
    protected ?int $indexerUid = null;


    /**
     * Source type.
     *
     * @var string
     */
    protected string $sourceType = '';


    /**
     * Collection override.
     *
     * @var string
     */
    protected string $collection = '';


    /**
     * Dry-run flag.
     *
     * @var bool
     */
    protected bool $dryRun = false;


    /**
     * Only changed flag.
     *
     * @var bool
     */
    protected bool $onlyChanged = true;


    /**
     * Limit.
     *
     * @var int|null
     */
    protected ?int $limit = null;


    /**
     * Since date.
     *
     * @var \DateTimeImmutable|null
     */
    protected ?\DateTimeImmutable $since = null;


    /**
     * Cursor.
     *
     * @var string
     */
    protected string $cursor = '';


    /**
     * Reset cursor flag.
     *
     * @var bool
     */
    protected bool $resetCursor = false;


    /**
     * Additional options.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];


    /**
     * Returns the indexer identifier.
     *
     * @return string Indexer identifier.
     */
    public function getIndexerIdentifier(): string
    {
        return $this->indexerIdentifier;
    }


    /**
     * Sets the indexer identifier.
     *
     * @param string $indexerIdentifier Indexer identifier.
     * @return void
     */
    public function setIndexerIdentifier(string $indexerIdentifier): void
    {
        $this->indexerIdentifier = trim($indexerIdentifier);
    }


    /**
     * Returns the indexer uid.
     *
     * @return int|null Indexer uid.
     */
    public function getIndexerUid(): ?int
    {
        return $this->indexerUid;
    }


    /**
     * Sets the indexer uid.
     *
     * @param int|null $indexerUid Indexer uid.
     * @return void
     */
    public function setIndexerUid(?int $indexerUid): void
    {
        $this->indexerUid = $indexerUid;
    }


    /**
     * Returns the source type.
     *
     * @return string Source type.
     */
    public function getSourceType(): string
    {
        return $this->sourceType;
    }


    /**
     * Sets the source type.
     *
     * @param string $sourceType Source type.
     * @return void
     */
    public function setSourceType(string $sourceType): void
    {
        $this->sourceType = trim($sourceType);
    }


    /**
     * Returns the collection override.
     *
     * @return string Collection override.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }


    /**
     * Sets the collection override.
     *
     * @param string $collection Collection override.
     * @return void
     */
    public function setCollection(string $collection): void
    {
        $this->collection = trim($collection);
    }


    /**
     * Returns whether this is a dry run.
     *
     * @return bool Dry-run flag.
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }


    /**
     * Sets the dry-run flag.
     *
     * @param bool $dryRun Dry-run flag.
     * @return void
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }


    /**
     * Returns whether only changed sources should be indexed.
     *
     * @return bool Only changed flag.
     */
    public function isOnlyChanged(): bool
    {
        return $this->onlyChanged;
    }


    /**
     * Sets whether only changed sources should be indexed.
     *
     * @param bool $onlyChanged Only changed flag.
     * @return void
     */
    public function setOnlyChanged(bool $onlyChanged): void
    {
        $this->onlyChanged = $onlyChanged;
    }


    /**
     * Returns the limit.
     *
     * @return int|null Limit.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }


    /**
     * Sets the limit.
     *
     * @param int|null $limit Limit.
     * @return void
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit !== null ? max(1, $limit) : null;
    }


    /**
     * Returns the since date.
     *
     * @return \DateTimeImmutable|null Since date.
     */
    public function getSince(): ?\DateTimeImmutable
    {
        return $this->since;
    }


    /**
     * Sets the since date.
     *
     * @param \DateTimeImmutable|null $since Since date.
     * @return void
     */
    public function setSince(?\DateTimeImmutable $since): void
    {
        $this->since = $since;
    }



    /**
     * Returns the cursor.
     *
     * @return string Cursor.
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }


    /**
     * Sets the cursor.
     *
     * @param string $cursor Cursor.
     * @return void
     */
    public function setCursor(string $cursor): void
    {
        $this->cursor = trim($cursor);
    }


    /**
     * Returns whether the stored cursor should be ignored.
     *
     * @return bool Reset cursor flag.
     */
    public function isResetCursor(): bool
    {
        return $this->resetCursor;
    }


    /**
     * Sets whether the stored cursor should be ignored.
     *
     * @param bool $resetCursor Reset cursor flag.
     * @return void
     */
    public function setResetCursor(bool $resetCursor): void
    {
        $this->resetCursor = $resetCursor;
    }


    /**
     * Returns additional options.
     *
     * @return array<string, mixed> Additional options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * Sets additional options.
     *
     * @param array<string, mixed> $options Additional options.
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }


    /**
     * Returns one option value.
     *
     * @param string $key Option key.
     * @param mixed $default Default value.
     * @return mixed Option value.
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}
