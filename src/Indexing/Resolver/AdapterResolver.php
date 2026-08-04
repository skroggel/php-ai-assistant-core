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

namespace Madj2k\AiCore\Indexing\Resolver;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Adapter\AdapterInterface;

/**
 * Class AdapterResolver
 *
 * Resolves tagged text content adapters.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
