<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\VectorStore\DTO;

/**
 * Class VectorSearchRequest
 *
 * Contains a normalized vector store search request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class VectorSearchRequest
{
    /**
     * Collection name.
     *
     * @var string
     */
    protected string $collection = '';


    /**
     * Query vector.
     *
     * @var array<int, float>
     */
    protected array $vector = [];


    /**
     * Result limit.
     *
     * @var int
     */
    protected int $limit = 10;


    /**
     * Search parameters.
     *
     * @var array<string, mixed>
     */
    protected array $params = [
        'hnsw_ef' => 128,
        'exact' => false,
    ];


    /**
     * Include payload.
     *
     * @var bool
     */
    protected bool $withPayload = true;


    /**
     * Include vector.
     *
     * @var bool
     */
    protected bool $withVector = false;


    /**
     * Vector name.
     *
     * @var string
     */
    protected string $vectorName = '';


    /**
     * Constructor.
     *
     * @param string $collection Collection name.
     * @param array<int, float> $vector Query vector.
     * @param int $limit Result limit.
     * @param array<string, mixed> $params Search parameters.
     * @param bool $withPayload Include payload.
     * @param bool $withVector Include vector.
     * @param string $vectorName Vector name.
     */
    public function __construct(
        string $collection = '',
        array $vector = [],
        int $limit = 10,
        array $params = ['hnsw_ef' => 128, 'exact' => false],
        bool $withPayload = true,
        bool $withVector = false,
        string $vectorName = ''
    ) {
        $this->collection = $collection;
        $this->vector = $vector;
        $this->limit = $limit;
        $this->params = $params;
        $this->withPayload = $withPayload;
        $this->withVector = $withVector;
        $this->vectorName = $vectorName;
    }


    /**
     * Returns the collection name.
     *
     * @return string Collection name.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }


    /**
     * Sets the collection name.
     *
     * @param string $collection Collection name.
     * @return void
     */
    public function setCollection(string $collection): void
    {
        $this->collection = trim($collection);
    }


    /**
     * Returns the query vector.
     *
     * @return array<int, float> Query vector.
     */
    public function getVector(): array
    {
        return $this->vector;
    }


    /**
     * Sets the query vector.
     *
     * @param array<int, float> $vector Query vector.
     * @return void
     */
    public function setVector(array $vector): void
    {
        $this->vector = $vector;
    }


    /**
     * Returns the result limit.
     *
     * @return int Result limit.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }


    /**
     * Sets the result limit.
     *
     * @param int $limit Result limit.
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }


    /**
     * Returns the search parameters.
     *
     * @return array<string, mixed> Search parameters.
     */
    public function getParams(): array
    {
        return $this->params;
    }


    /**
     * Sets the search parameters.
     *
     * @param array<string, mixed> $params Search parameters.
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }


    /**
     * Returns whether payload should be included.
     *
     * @return bool Include payload.
     */
    public function getWithPayload(): bool
    {
        return $this->withPayload;
    }


    /**
     * Sets whether payload should be included.
     *
     * @param bool $withPayload Include payload.
     * @return void
     */
    public function setWithPayload(bool $withPayload): void
    {
        $this->withPayload = $withPayload;
    }


    /**
     * Returns whether vector should be included.
     *
     * @return bool Include vector.
     */
    public function getWithVector(): bool
    {
        return $this->withVector;
    }


    /**
     * Sets whether vector should be included.
     *
     * @param bool $withVector Include vector.
     * @return void
     */
    public function setWithVector(bool $withVector): void
    {
        $this->withVector = $withVector;
    }


    /**
     * Returns the vector name.
     *
     * @return string Vector name.
     */
    public function getVectorName(): string
    {
        return $this->vectorName;
    }


    /**
     * Sets the vector name.
     *
     * @param string $vectorName Vector name.
     * @return void
     */
    public function setVectorName(string $vectorName): void
    {
        $this->vectorName = trim($vectorName);
    }
}
