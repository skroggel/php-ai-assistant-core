<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai;

use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;

/**
 * Interface AiConnectorInterface
 *
 * Defines the contract for shared AI provider connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface AiConnectorInterface
{
    /**
     * Returns the connector identifier.
     *
     * @return string Connector identifier.
     */
    public function getIdentifier(): string;


    /**
     * Performs a synchronous chat request.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @return \Madj2k\AiCore\Connection\Ai\DTO\AiResponse AI response.
     */
    public function chat(AiConnectionConfigurationInterface $connection, AiRequest $request): AiResponse;


    /**
     * Streams a chat response and forwards chunks to the callback.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiRequest $request AI request.
     * @param callable $onData Callback for streamed chunks.
     * @return void
     */
    public function streamChat(AiConnectionConfigurationInterface $connection, AiRequest $request, callable $onData): void;


    /**
     * Generates an embedding for one text.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest $request Embedding request.
     * @return \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse Embedding response.
     */
    public function embed(AiConnectionConfigurationInterface $connection, EmbeddingRequest $request): EmbeddingResponse;


    /**
     * Generates embeddings for multiple texts.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest> $requests Embedding requests.
     * @return array<int, \Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse> Embedding responses.
     */
    public function embedBatch(AiConnectionConfigurationInterface $connection, array $requests): array;
}
