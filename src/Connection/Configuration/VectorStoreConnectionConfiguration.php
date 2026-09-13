<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Class VectorStoreConnectionConfiguration
 *
 * Immutable framework-independent vector store connection configuration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
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
    public function getConnectorIdentifier(): string { return $this->connectorIdentifier; }

    /** @inheritDoc */
    public function getEndpoint(): string { return rtrim($this->endpoint, '/'); }

    /** @inheritDoc */
    public function getApiKey(): string { return $this->apiKey; }

    /** @inheritDoc */
    public function getDefaultCollection(): string { return $this->defaultCollection; }

    /** @inheritDoc */
    public function getCollectionList(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $collection): string => trim((string)$collection),
            array_merge([$this->defaultCollection], $this->collections),
        ))));
    }

    /** @inheritDoc */
    public function getDistance(): string { return $this->distance; }

    /** @inheritDoc */
    public function getAdditionalOptionsArray(): array { return $this->additionalOptions; }
}
