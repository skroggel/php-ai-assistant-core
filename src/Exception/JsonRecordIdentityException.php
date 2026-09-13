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

namespace Madj2k\AiCore\Exception;

/**
 * Class JsonRecordIdentityException
 *
 * Exception for JSON records that cannot be mapped to unique source identities.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class JsonRecordIdentityException extends IndexingException
{
    /**
     * @param string $message Exception message.
     * @param array<string, mixed> $details Structured diagnostic details.
     * @param int $code Exception code.
     */
    public function __construct(
        string $message,
        private readonly array $details = [],
        int $code = 1782295401
    ) {
        parent::__construct($message, $code);
    }


    /**
     * Returns structured diagnostic details.
     *
     * @return array<string, mixed> Details.
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
