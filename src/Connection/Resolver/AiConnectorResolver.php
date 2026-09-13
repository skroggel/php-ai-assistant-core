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

use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

/**
 * Class AiConnectorResolver
 *
 * Resolves registered AI connectors by their unique identifiers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class AiConnectorResolver
{
    /** @var array<string, AiConnectorInterface> */
    private array $connectors = [];

    /**
     * @param iterable<AiConnectorInterface> $connectors Registered AI connectors.
     * @throws \Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException
     */
    public function __construct(iterable $connectors)
    {
        foreach ($connectors as $connector) {
            $identifier = $connector->getIdentifier();
            if (isset($this->connectors[$identifier])) {
                throw new DuplicateConnectorIdentifierException('AI', $identifier);
            }
            $this->connectors[$identifier] = $connector;
        }
    }


    /**
     * Returns the connector registered for an identifier.
     *
     * @param string $identifier
     * @return \Madj2k\AiCore\Connection\Ai\AiConnectorInterface
     * @throws \Madj2k\AiCore\Exception\ConnectorNotFoundException
     */
    public function get(string $identifier): AiConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('AI', $identifier, 1780001001);
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
     * @return array<string, AiConnectorInterface>
     */
    public function all(): array
    {
        return $this->connectors;
    }
}
