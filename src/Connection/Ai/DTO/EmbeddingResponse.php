<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai\DTO;

/**
 * Class EmbeddingResponse
 *
 * Contains a normalized embedding response.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
