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

namespace Madj2k\AiCore\Indexing\Resolver;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Adapter\AdapterInterface;

/**
 * Class AdapterResolver
 *
 * Resolves tagged text content adapters.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class AdapterResolver
{
    /**
     * Adapters.
     *
     * @var iterable<\Madj2k\AiCore\Indexing\Adapter\AdapterInterface>
     */
    protected iterable $adapters;


    /**
     * Constructor. Set via Service.yaml with tags
     *
     * @param iterable<\Madj2k\AiCore\Indexing\Adapter\AdapterInterface> $adapters Adapters.
     */
    public function __construct(iterable $adapters)
    {
        $this->adapters = $adapters;
    }


    /**
     * Returns an adapter for a path.
     *
     * @param string $path File path.
     * @return \Madj2k\AiCore\Indexing\Adapter\AdapterInterface Adapter
     * @throws \Madj2k\AiCore\Exception\IndexingException
     */
    public function getForPath(string $path): AdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($path)) {
                return $adapter;
            }
        }

        throw new IndexingException(sprintf('No text content adapter registered for "%s".', $path), 1760001002);
    }


    /**
     * Returns all registered adapters.
     *
     * @return array<string, \Madj2k\AiCore\Indexing\Adapter\AdapterInterface> Adapters.
     */
    public function all(): array
    {
        return is_array($this->adapters)
            ? $this->adapters
            : iterator_to_array($this->adapters, false);
    }
}
