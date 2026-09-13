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

namespace Madj2k\AiCore\Connection\VectorStore\DTO;

/**
 * Class VectorWriteResult
 *
 * Contains the result of a vector write operation.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
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
