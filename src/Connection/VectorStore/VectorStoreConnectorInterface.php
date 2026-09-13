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

namespace Madj2k\AiCore\Connection\VectorStore;

use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorWriteResult;
use Madj2k\AiCore\Exception\VectorDatabaseException;

/**
 * Interface VectorStoreConnectorInterface
 *
 * Defines the contract for shared vector store connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface VectorStoreConnectorInterface
{
    /**
     * Returns the connector identifier.
     *
     * @return string Connector identifier.
     */
    public function getIdentifier(): string;


    /**
     * Ensures that the collection exists.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @return bool True if collection exists.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function ensureCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): bool;


    /**
     * Writes vector documents.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @param array<int, \Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument> $documents Vector documents.
     * @return \Madj2k\AiCore\Connection\VectorStore\DTO\VectorWriteResult Write result.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function upsert(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, array $documents): VectorWriteResult;


    /**
     * Searches vector documents.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest $request Search request.
     * @return array<int, \Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchResult> Search results.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function search(VectorStoreConnectionConfigurationInterface $connection, VectorSearchRequest $request): array;


    /**
     * Lists known collection names in the vector store.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @return array<int, string> Collection names.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function listCollections(VectorStoreConnectionConfigurationInterface $connection): array;


    /**
     * Deletes vector documents by source hash.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @param string $sourceHash Source hash.
     * @return \Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult Delete result.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function deleteBySourceHash(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, string $sourceHash): VectorDeleteResult;


    /**
     * Deletes obsolete vector documents for a source while preserving the current index generation.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @param string $sourceHash Source hash.
     * @param string $indexGeneration Index generation to preserve.
     * @return \Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult Delete result.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function deleteObsoleteSourceGenerations(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
        string $sourceHash,
        string $indexGeneration,
    ): VectorDeleteResult;


    /**
     * Deletes a collection.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @param \Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @return \Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult Delete result.
     * @throws \Madj2k\AiCore\Exception\VectorDatabaseException
     */
    public function deleteCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): VectorDeleteResult;
}
