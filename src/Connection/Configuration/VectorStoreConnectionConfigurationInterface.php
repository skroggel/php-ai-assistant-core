<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Configuration;

interface VectorStoreConnectionConfigurationInterface
{
    public function getConnectorIdentifier(): string;

    public function getEndpoint(): string;

    public function getApiKey(): string;

    public function getDefaultCollection(): string;

    public function getVectorSize(): int;

    public function getDistance(): string;

    /** @return array<string, mixed> */
    public function getAdditionalOptionsArray(): array;
}
