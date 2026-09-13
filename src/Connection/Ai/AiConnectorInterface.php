<?php
declare(strict_types=1);

/*
 * This file is part of madj2k\ai-core
 *
 * Copyright (C) 2026 Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
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
