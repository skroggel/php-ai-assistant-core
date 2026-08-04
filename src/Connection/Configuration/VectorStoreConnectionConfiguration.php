<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Class VectorStoreConnectionConfiguration
 *
 * Immutable framework-independent vector store connection configuration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class VectorStoreConnectionConfiguration implements VectorStoreConnectionConfigurationInterface
{
    /**
     * @param string $endpoint Vector store endpoint URL.
     * @param string $apiKey Optional vector store API key.
     * @param array<string, mixed> $additionalOptions Provider-specific options.
     * @param string $connectorIdentifier Registered connector identifier.
     * @param string $defaultCollection Default collection name.
     * @param int $vectorSize Default vector dimensions.
     * @param string $distance Default vector distance metric.
     */
    public function __construct(
        private string $endpoint,
        private string $apiKey = '',
        private array $additionalOptions = [],
        private string $connectorIdentifier = 'qdrant',
        private string $defaultCollection = '',
        private int $vectorSize = 1536,
        private string $distance = 'Cosine',
    ) {}

    /** @inheritDoc */
    public function getConnectorIdentifier(): string { return $this->connectorIdentifier; }

    /** @inheritDoc */
    public function getEndpoint(): string { return rtrim($this->endpoint, '/'); }

    /** @inheritDoc */
    public function getApiKey(): string { return $this->apiKey; }

    /** @inheritDoc */
    public function getDefaultCollection(): string { return $this->defaultCollection; }

    /** @inheritDoc */
    public function getVectorSize(): int { return $this->vectorSize; }

    /** @inheritDoc */
    public function getDistance(): string { return $this->distance; }

    /** @inheritDoc */
    public function getAdditionalOptionsArray(): array { return $this->additionalOptions; }
}
