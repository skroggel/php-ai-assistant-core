<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Interface AiConnectionConfigurationInterface
 *
 * Provides provider credentials and model defaults to AI connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface AiConnectionConfigurationInterface
{
    /** Returns the connector identifier resolved by the application. */
    public function getConnectorIdentifier(): string;

    /** Returns the provider API key. */
    public function getApiKey(): string;

    /** Returns the provider API base URL. */
    public function getBaseUrl(): string;

    /** Returns the optional provider organization identifier. */
    public function getOrganization(): string;

    /** Returns the optional provider project identifier. */
    public function getProject(): string;

    /** Returns the default chat model. */
    public function getDefaultModel(): string;

    /** Returns the default embedding model. */
    public function getEmbeddingModel(): string;

    /** Returns the default chat sampling temperature. */
    public function getDefaultTemperature(): float;

    /** Returns the default embedding sampling temperature. */
    public function getEmbeddingTemperature(): float;

    /**
     * Returns provider-specific connection options.
     *
     * @return array<string, mixed>
     */
    public function getAdditionalOptionsArray(): array;
}
