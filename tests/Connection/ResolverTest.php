<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Connection;

use Madj2k\AiCore\Connection\Ai\OpenAiConnector;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;
use PHPUnit\Framework\TestCase;

final class ResolverTest extends TestCase
{
    public function testResolvesConnectorByIdentifier(): void
    {
        $connector = new OpenAiConnector();
        $resolver = new AiConnectorResolver([$connector]);

        self::assertTrue($resolver->has('openai'));
        self::assertSame($connector, $resolver->get('openai'));
        self::assertSame(['openai' => $connector], $resolver->all());
    }

    public function testRejectsDuplicateIdentifiers(): void
    {
        $this->expectException(DuplicateConnectorIdentifierException::class);

        new AiConnectorResolver([new OpenAiConnector(), new OpenAiConnector()]);
    }

    public function testReportsUnknownIdentifiers(): void
    {
        $resolver = new AiConnectorResolver([]);

        $this->expectException(ConnectorNotFoundException::class);
        $resolver->get('missing');
    }
}
