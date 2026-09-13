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
 * Interface VectorStoreConnectionConfigurationInterface
 *
 * Provides endpoint and collection defaults to vector store connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface VectorStoreConnectionConfigurationInterface
{
    /**
     * Returns the connector identifier resolved by the application.
     *
     * @return string
     */
    public function getConnectorIdentifier(): string;


    /**
     * Returns the vector store endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string;


    /**
     * Returns the optional vector store API key.
     *
     * @return string
     */
    public function getApiKey(): string;


    /**
     * Returns the default collection name.
     *
     * @return string
     */
    public function getDefaultCollection(): string;


    /**
     * Returns all configured collections including the default collection.
     *
     * @return array<int, string> Collection names.
     */
    public function getCollectionList(): array;


    /**
     * Returns the default vector distance metric.
     * @return string
     */
    public function getDistance(): string;


    /**
     * Returns provider-specific connection options.
     *
     * @return array<string, mixed>
     */
    public function getAdditionalOptionsArray(): array;
}
