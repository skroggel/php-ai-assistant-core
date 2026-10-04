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

namespace Madj2k\AiCore\Indexing\State;

use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;

/**
 * Class SourceStateInterface
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface SourceStateInterface
{
    /**
     * Determines whether the document can be skipped because its content hash
     * matches the already persisted state for the configured collection.
     *
     * @param \Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface $configuration
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document
     * @param string $collection
     * @param bool $onlyChanged
     * @return bool
     */
    public function shouldSkip(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        bool $onlyChanged,
    ): bool;


    /**
     * Persists the document as successfully indexed, including its content and
     * storage hashes and the current source-group membership.
     *
     * @param \Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface $configuration
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document
     * @param string $collection
     * @return void
     */
    public function markIndexed(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
    ): void;


    /**
     * Persists the document as failed and records the exception for the next
     * indexing run or diagnostics.
     *
     * @param \Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface $configuration
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document
     * @param string $collection
     * @param \Throwable $exception
     * @return void
     */
    public function markFailed(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        \Throwable $exception,
    ): void;


    /**
     * Returns all storage hashes that may need to be removed before replacing
     * this document or reconciling its source group.
     *
     * @param \Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface $configuration
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document
     * @param string $collection
     * @return array<int, string>
     */
    public function getStorageSourceHashesForDeletion(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
    ): array;


    /**
     * Removes persisted states and returns the number of source-group members
     * that are no longer present in the successfully processed document group.
     *
     * @param \Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface $configuration
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document
     * @param string $collection
     * @param array $currentHashes
     * @return int
     */
    public function removeStaleGroupMembers(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        array $currentHashes,
    ): int;
}
