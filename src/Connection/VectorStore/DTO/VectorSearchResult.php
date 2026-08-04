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
 * Class VectorSearchResult
 *
 * Contains one vector store search result.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class VectorSearchResult
{
    /**
     * Result identifier.
     *
     * @var string
     */
    protected string $id = '';


    /**
     * Result score.
     *
     * @var float
     */
    protected float $score = 0.0;


    /**
     * Payload.
     *
     * @var array<string, mixed>
     */
    protected array $payload = [];


    /**
     * Constructor.
     *
     * @param string $id Result identifier.
     * @param float $score Result score.
     * @param array<string, mixed> $payload Payload.
     */
    public function __construct(string $id = '', float $score = 0.0, array $payload = [])
    {
        $this->id = $id;
        $this->score = $score;
        $this->payload = $payload;
    }


    /**
     * Returns the result identifier.
     *
     * @return string Result identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Sets the result identifier.
     *
     * @param string $id Result identifier.
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = trim($id);
    }


    /**
     * Returns the result score.
     *
     * @return float Result score.
     */
    public function getScore(): float
    {
        return $this->score;
    }


    /**
     * Sets the result score.
     *
     * @param float $score Result score.
     * @return void
     */
    public function setScore(float $score): void
    {
        $this->score = $score;
    }


    /**
     * Returns the payload.
     *
     * @return array<string, mixed> Payload.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }


    /**
     * Sets the payload.
     *
     * @param array<string, mixed> $payload Payload.
     * @return void
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }


    /**
     * Returns the result as array.
     *
     * @return array{
     *     id: string,
     *     score: float,
     *     payload: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'score' => $this->getScore(),
            'payload' => $this->getPayload(),
        ];
    }
}
