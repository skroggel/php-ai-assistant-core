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

namespace Madj2k\AiCore\Indexing\Registry;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Indexer\IndexerInterface;

/**
 * Class IndexerRegistry
 *
 * Registers indexers and resolves them by identifier or source type.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class IndexerRegistry
{
    /** @var array<string, IndexerInterface> */
    private array $indexers = [];

    /**
     * @param iterable<IndexerInterface> $indexers Registered indexers.
     * @throws \Madj2k\AiCore\Exception\IndexingException For empty or duplicate identifiers.
     */
    public function __construct(iterable $indexers)
    {
        foreach ($indexers as $indexer) {
            $identifier = trim($indexer->getIdentifier());
            if ($identifier === '') {
                throw new IndexingException('Indexer identifiers must not be empty.', 1781001001);
            }
            if (isset($this->indexers[$identifier])) {
                throw new IndexingException(sprintf('Duplicate indexer identifier "%s".', $identifier), 1781001002);
            }
            $this->indexers[$identifier] = $indexer;
        }
    }

    /**
     * Returns the indexer registered for an identifier.
     *
     * @throws \Madj2k\AiCore\Exception\IndexingException
     */
    public function get(string $identifier): IndexerInterface
    {
        return $this->indexers[$identifier]
            ?? throw new IndexingException(sprintf('No indexer registered for identifier "%s".', $identifier), 1760001001);
    }

    /**
     * Returns indexers supporting a source type.
     *
     * @return array<int, IndexerInterface>
     */
    public function findBySourceType(string $sourceType): array
    {
        return array_values(array_filter(
            $this->indexers,
            static fn (IndexerInterface $indexer): bool => $indexer->getSourceType() === $sourceType,
        ));
    }

    /**
     * Returns all registered indexers.
     *
     * @return array<int, IndexerInterface>
     */
    public function all(): array
    {
        return array_values($this->indexers);
    }

    /**
     * Returns all registered identifiers.
     *
     * @return array<int, string>
     */
    public function getIdentifiers(): array
    {
        return array_keys($this->indexers);
    }
}
