<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Connection;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfiguration;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfiguration;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testAiConfigurationIsNormalizedAndIndependent(): void
    {
        $configuration = new AiConnectionConfiguration(
            apiKey: 'secret',
            baseUrl: 'https://example.test/v1/',
            additionalOptions: ['seed' => 42],
        );

        self::assertSame('secret', $configuration->getApiKey());
        self::assertSame('openai', $configuration->getConnectorIdentifier());
        self::assertSame('https://example.test/v1', $configuration->getBaseUrl());
        self::assertSame(['seed' => 42], $configuration->getAdditionalOptionsArray());
    }

    public function testVectorStoreConfigurationIsNormalized(): void
    {
        $configuration = new VectorStoreConnectionConfiguration('https://qdrant.test/');

        self::assertSame('https://qdrant.test', $configuration->getEndpoint());
        self::assertSame('qdrant', $configuration->getConnectorIdentifier());
        self::assertSame('', $configuration->getApiKey());
        self::assertSame('', $configuration->getDefaultCollection());
    }
}
