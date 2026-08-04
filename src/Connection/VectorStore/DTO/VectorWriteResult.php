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
 * Class VectorWriteResult
 *
 * Contains the result of a vector write operation.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class VectorWriteResult
{
    /**
     * Number of written vectors.
     *
     * @var int
     */
    protected int $written = 0;


    /**
     * Raw provider response.
     *
     * @var mixed
     */
    protected mixed $rawResponse = null;


    /**
     * Constructor.
     *
     * @param int $written Number of written vectors.
     * @param mixed $rawResponse Raw provider response.
     */
    public function __construct(int $written = 0, mixed $rawResponse = null)
    {
        $this->written = $written;
        $this->rawResponse = $rawResponse;
    }


    /**
     * Returns the number of written vectors.
     *
     * @return int Number of written vectors.
     */
    public function getWritten(): int
    {
        return $this->written;
    }


    /**
     * Sets the number of written vectors.
     *
     * @param int $written Number of written vectors.
     * @return void
     */
    public function setWritten(int $written): void
    {
        $this->written = $written;
    }


    /**
     * Returns the raw provider response.
     *
     * @return mixed Raw provider response.
     */
    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }


    /**
     * Sets the raw provider response.
     *
     * @param mixed $rawResponse Raw provider response.
     * @return void
     */
    public function setRawResponse(mixed $rawResponse): void
    {
        $this->rawResponse = $rawResponse;
    }
}
