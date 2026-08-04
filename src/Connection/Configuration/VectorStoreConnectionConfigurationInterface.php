<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Interface VectorStoreConnectionConfigurationInterface
 *
 * Provides endpoint and collection defaults to vector store connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface VectorStoreConnectionConfigurationInterface
{
    /** Returns the connector identifier resolved by the application. */
    public function getConnectorIdentifier(): string;

    /** Returns the vector store endpoint URL. */
    public function getEndpoint(): string;

    /** Returns the optional vector store API key. */
    public function getApiKey(): string;

    /** Returns the default collection name. */
    public function getDefaultCollection(): string;

    /** Returns the default vector dimensions. */
    public function getVectorSize(): int;

    /** Returns the default vector distance metric. */
    public function getDistance(): string;

    /**
     * Returns provider-specific connection options.
     *
     * @return array<string, mixed>
     */
    public function getAdditionalOptionsArray(): array;
}
