<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

final readonly class VectorStoreConnectionConfiguration implements VectorStoreConnectionConfigurationInterface
{
    /** @param array<string, mixed> $additionalOptions */
    public function __construct(
        private string $endpoint,
        private string $apiKey = '',
        private array $additionalOptions = [],
        private string $connectorIdentifier = 'qdrant',
        private string $defaultCollection = '',
        private int $vectorSize = 1536,
        private string $distance = 'Cosine',
    ) {}

    public function getConnectorIdentifier(): string { return $this->connectorIdentifier; }
    public function getEndpoint(): string { return rtrim($this->endpoint, '/'); }
    public function getApiKey(): string { return $this->apiKey; }
    public function getDefaultCollection(): string { return $this->defaultCollection; }
    public function getVectorSize(): int { return $this->vectorSize; }
    public function getDistance(): string { return $this->distance; }
    public function getAdditionalOptionsArray(): array { return $this->additionalOptions; }
}
