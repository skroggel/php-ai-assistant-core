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

namespace Madj2k\AiCore\Connection\Ai\DTO;

/**
 * Class EmbeddingResponse
 *
 * Contains a normalized embedding response.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class EmbeddingResponse
{
    /**
     * Embedding vector.
     *
     * @var array<int, float>
     */
    protected array $embedding = [];


    /**
     * Model identifier.
     *
     * @var string
     */
    protected string $model = '';


    /**
     * Raw provider response.
     *
     * @var array<string, mixed>
     */
    protected array $rawResponse = [];


    /**
     * Constructor.
     *
     * @param array<int, float> $embedding Embedding vector.
     * @param string $model Model identifier.
     * @param array<string, mixed> $rawResponse Raw provider response.
     */
    public function __construct(array $embedding = [], string $model = '', array $rawResponse = [])
    {
        $this->embedding = $embedding;
        $this->model = $model;
        $this->rawResponse = $rawResponse;
    }


    /**
     * Returns the embedding vector.
     *
     * @return array<int, float> Embedding vector.
     */
    public function getEmbedding(): array
    {
        return $this->embedding;
    }


    /**
     * Sets the embedding vector.
     *
     * @param array<int, float> $embedding Embedding vector.
     * @return void
     */
    public function setEmbedding(array $embedding): void
    {
        $this->embedding = $embedding;
    }


    /**
     * Returns the model identifier.
     *
     * @return string Model identifier.
     */
    public function getModel(): string
    {
        return $this->model;
    }


    /**
     * Sets the model identifier.
     *
     * @param string $model Model identifier.
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = trim($model);
    }


    /**
     * Returns the raw provider response.
     *
     * @return array<string, mixed> Raw provider response.
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }


    /**
     * Sets the raw provider response.
     *
     * @param array<string, mixed> $rawResponse Raw provider response.
     * @return void
     */
    public function setRawResponse(array $rawResponse): void
    {
        $this->rawResponse = $rawResponse;
    }
}
