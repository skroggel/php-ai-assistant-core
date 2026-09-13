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

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Class VectorStoreConnectionConfiguration
 *
 * Immutable framework-independent vector store connection configuration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class VectorStoreConnectionConfiguration implements VectorStoreConnectionConfigurationInterface
{
    /**
     * @param string $endpoint Vector store endpoint URL.
     * @param string $apiKey Optional vector store API key.
     * @param array<string, mixed> $additionalOptions Provider-specific options.
     * @param string $connectorIdentifier Registered connector identifier.
     * @param string $defaultCollection Default collection name.
     * @param string $distance Default vector distance metric.
     * @param array<int,string> $collections Collections allowed for retrieval overrides.
     */
    public function __construct(
        private string $endpoint,
        private string $apiKey = '',
        private array $additionalOptions = [],
        private string $connectorIdentifier = 'qdrant',
        private string $defaultCollection = '',
        private string $distance = 'Cosine',
        private array $collections = [],
    ) {}

    /** @inheritDoc */
    public function getConnectorIdentifier(): string
    {
        return $this->connectorIdentifier;
    }

    /** @inheritDoc */
    public function getEndpoint(): string
    {
        return rtrim($this->endpoint, '/');
    }

    /** @inheritDoc */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /** @inheritDoc */
    public function getDefaultCollection(): string
    {
        return $this->defaultCollection;
    }

    /** @inheritDoc */
    public function getCollectionList(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $collection): string => trim((string)$collection),
            array_merge([$this->defaultCollection], $this->collections),
        ))));
    }

    /** @inheritDoc */
    public function getDistance(): string
    {
        return $this->distance;
    }

    /** @inheritDoc */
    public function getAdditionalOptionsArray(): array
    {
        return $this->additionalOptions;
    }
}
