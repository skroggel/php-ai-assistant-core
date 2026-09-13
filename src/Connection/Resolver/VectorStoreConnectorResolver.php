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

namespace Madj2k\AiCore\Connection\Resolver;

use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

/**
 * Class VectorStoreConnectorResolver
 *
 * Resolves registered vector store connectors by their unique identifiers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class VectorStoreConnectorResolver
{
    /** @var array<string, VectorStoreConnectorInterface> */
    private array $connectors = [];

    /**
     * @param iterable<VectorStoreConnectorInterface> $connectors Registered vector store connectors.
     * @throws \Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException
     */
    public function __construct(iterable $connectors)
    {
        foreach ($connectors as $connector) {
            $identifier = $connector->getIdentifier();
            if (isset($this->connectors[$identifier])) {
                throw new DuplicateConnectorIdentifierException('vector store', $identifier);
            }
            $this->connectors[$identifier] = $connector;
        }
    }


    /**
     * Returns the connector registered for an identifier.
     *
     * @param string $identifier
     * @return \Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface
     * @throws \Madj2k\AiCore\Exception\ConnectorNotFoundException
     */
    public function get(string $identifier): VectorStoreConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('vector store', $identifier, 1780002001);
    }


    /** Determines whether an identifier is registered.
     *
     * @param string $identifier
     * @return bool
     */
    public function has(string $identifier): bool
    {
        return isset($this->connectors[$identifier]);
    }


    /**
     * Returns all connectors keyed by identifier.
     *
     * @return array<string, VectorStoreConnectorInterface>
     */
    public function all(): array { return $this->connectors; }
}
