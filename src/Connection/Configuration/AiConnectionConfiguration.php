<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

/**
 * Class AiConnectionConfiguration
 *
 * Immutable framework-independent AI connection configuration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class AiConnectionConfiguration implements AiConnectionConfigurationInterface
{
    /**
     * @param string $apiKey Provider API key.
     * @param string $baseUrl Provider API base URL.
     * @param string $organization Optional provider organization identifier.
     * @param string $project Optional provider project identifier.
     * @param string $defaultModel Default chat model.
     * @param string $embeddingModel Default embedding model.
     * @param float $defaultTemperature Default chat sampling temperature.
     * @param float $embeddingTemperature Default embedding sampling temperature.
     * @param array<string, mixed> $additionalOptions Provider-specific options.
     * @param string $connectorIdentifier Registered connector identifier.
     */
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

    /** @inheritDoc */
    public function getConnectorIdentifier(): string { return $this->connectorIdentifier; }

    /** @inheritDoc */
    public function getApiKey(): string { return $this->apiKey; }

    /** @inheritDoc */
    public function getBaseUrl(): string { return rtrim($this->baseUrl, '/'); }

    /** @inheritDoc */
    public function getOrganization(): string { return $this->organization; }

    /** @inheritDoc */
    public function getProject(): string { return $this->project; }

    /** @inheritDoc */
    public function getDefaultModel(): string { return $this->defaultModel; }

    /** @inheritDoc */
    public function getEmbeddingModel(): string { return $this->embeddingModel; }

    /** @inheritDoc */
    public function getDefaultTemperature(): float { return $this->defaultTemperature; }

    /** @inheritDoc */
    public function getEmbeddingTemperature(): float { return $this->embeddingTemperature; }

    /** @inheritDoc */
    public function getAdditionalOptionsArray(): array { return $this->additionalOptions; }
}
