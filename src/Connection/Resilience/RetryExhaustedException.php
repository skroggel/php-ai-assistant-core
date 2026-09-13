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

namespace Madj2k\AiCore\Connection\Resilience;

/**
 * Class RetryExhaustedException
 *
 * Wraps the final provider failure together with retry diagnostics.
 *
 * @internal Provider connectors convert this exception into public provider exceptions.
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class RetryExhaustedException extends \RuntimeException
{
    /**
     * @param string $provider Provider identifier.
     * @param string $operation Operation identifier.
     * @param int $attempts Number of executed attempts.
     * @param bool $retryable Whether the final failure was classified as retryable.
     * @param int|null $statusCode Detected HTTP status code.
     * @param \Throwable $previous Original provider exception.
     */
    public function __construct(
        private readonly string $provider,
        private readonly string $operation,
        private readonly int $attempts,
        private readonly bool $retryable,
        private readonly ?int $statusCode,
        \Throwable $previous,
    ) {
        parent::__construct($previous->getMessage(), (int)$previous->getCode(), $previous);
    }


    /** Returns the provider identifier.
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }


    /**
     * Returns the operation identifier.
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }


    /**
     * Returns the number of executed attempts.
     *
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }


    /**
     * Determines whether the final failure was classified as retryable.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return $this->retryable;
    }


    /**
     * Returns the detected HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
