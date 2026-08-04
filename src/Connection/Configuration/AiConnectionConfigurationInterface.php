<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

interface AiConnectionConfigurationInterface
{
    public function getConnectorIdentifier(): string;

    public function getApiKey(): string;

    public function getBaseUrl(): string;

    public function getOrganization(): string;

    public function getProject(): string;

    public function getDefaultModel(): string;

    public function getEmbeddingModel(): string;

    public function getDefaultTemperature(): float;

    public function getEmbeddingTemperature(): float;

    /** @return array<string, mixed> */
    public function getAdditionalOptionsArray(): array;
}
