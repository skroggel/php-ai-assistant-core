<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

final readonly class AiConnectionConfiguration implements AiConnectionConfigurationInterface
{
    /** @param array<string, mixed> $additionalOptions */
    public function __construct(
        private string $apiKey,
        private string $baseUrl = 'https://api.openai.com/v1',
        private string $organization = '',
        private string $project = '',
        private string $defaultModel = 'gpt-4o-mini',
        private string $embeddingModel = 'text-embedding-3-small',
        private float $defaultTemperature = 0.2,
        private float $embeddingTemperature = 0.0,
        private array $additionalOptions = [],
        private string $connectorIdentifier = 'openai',
    ) {}

    public function getConnectorIdentifier(): string { return $this->connectorIdentifier; }
    public function getApiKey(): string { return $this->apiKey; }
    public function getBaseUrl(): string { return rtrim($this->baseUrl, '/'); }
    public function getOrganization(): string { return $this->organization; }
    public function getProject(): string { return $this->project; }
    public function getDefaultModel(): string { return $this->defaultModel; }
    public function getEmbeddingModel(): string { return $this->embeddingModel; }
    public function getDefaultTemperature(): float { return $this->defaultTemperature; }
    public function getEmbeddingTemperature(): float { return $this->embeddingTemperature; }
    public function getAdditionalOptionsArray(): array { return $this->additionalOptions; }
}
