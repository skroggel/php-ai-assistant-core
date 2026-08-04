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
 * Class VectorDeleteResult
 *
 * Contains the result of a vector delete operation.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class VectorDeleteResult
{
    /**
     * Number of deleted vectors if known.
     *
     * @var int
     */
    protected int $deleted = 0;


    /**
     * Raw provider response.
     *
     * @var mixed
     */
    protected mixed $rawResponse = null;


    /**
     * Constructor.
     *
     * @param int $deleted Number of deleted vectors if known.
     * @param mixed $rawResponse Raw provider response.
     */
    public function __construct(int $deleted = 0, mixed $rawResponse = null)
    {
        $this->deleted = $deleted;
        $this->rawResponse = $rawResponse;
    }


    /**
     * Returns the number of deleted vectors if known.
     *
     * @return int Number of deleted vectors if known.
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }


    /**
     * Sets the number of deleted vectors.
     *
     * @param int $deleted Number of deleted vectors if known.
     * @return void
     */
    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
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
