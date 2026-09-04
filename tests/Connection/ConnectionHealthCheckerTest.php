<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Tests\Connection;

use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfiguration;
use Madj2k\AiCore\Connection\Health\ConnectionHealthChecker;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class ConnectionHealthCheckerTest
 *
 * Verifies diagnostic probes for AI provider connections.
 *
 * @author Maximilian Fäßlär <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class ConnectionHealthCheckerTest extends TestCase
{
    public function testEmbeddingProbeReturnsCompleteResponse(): void
    {
        $connector = $this->createStub(AiConnectorInterface::class);
        $connector->method('getIdentifier')->willReturn('test-ai');
        $connector->method('embed')->willReturn(
            new EmbeddingResponse([0.1, 0.2, 0.3], 'test-embedding'),
        );

        $response = $this->createChecker($connector)->probeAiEmbedding(
            new AiConnectionConfiguration(apiKey: '', connectorIdentifier: 'test-ai'),
        );

        self::assertCount(3, $response->getEmbedding());
        self::assertSame('test-embedding', $response->getModel());
    }


    public function testChatProbeReturnsCompleteResponse(): void
    {
        $receivedModel = '';
        $connector = $this->createStub(AiConnectorInterface::class);
        $connector->method('getIdentifier')->willReturn('test-ai');
        $connector->method('chat')->willReturnCallback(
            static function (mixed $connection, AiRequest $request) use (&$receivedModel): AiResponse {
                $receivedModel = $request->getModel();
                return new AiResponse('OK');
            },
        );

        $response = $this->createChecker($connector)->probeAiChat(
            new AiConnectionConfiguration(apiKey: '', connectorIdentifier: 'test-ai'),
            model: 'provider-model',
        );

        self::assertSame('OK', $response->getContent());
        self::assertSame('provider-model', $receivedModel);
    }


    /**
     * Creates a health checker for one AI connector.
     *
     * @param \Madj2k\AiCore\Connection\Ai\AiConnectorInterface $connector AI connector.
     * @return \Madj2k\AiCore\Connection\Health\ConnectionHealthChecker Health checker.
     */
    private function createChecker(AiConnectorInterface $connector): ConnectionHealthChecker
    {
        return new ConnectionHealthChecker(
            new AiConnectorResolver([$connector]),
            new VectorStoreConnectorResolver([]),
        );
    }
}
