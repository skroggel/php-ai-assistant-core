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

namespace Madj2k\AiCore\Assistant\Context\Request;

/**
 * Class History
 *
 * Provides controlled access to the already visible messages.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class History
{
    /**
     * @var array<int,array{role:string,content:string}>
     */
    private array $messages;


    /**
     * Constructor.
     *
     * @param array<int,array<string,string>> $messages Visible history messages.
     */
    public function __construct(array $messages)
    {
        /** @var array<int,array{role:string,content:string}> $normalizedMessages */
        $normalizedMessages = [];
        foreach ($messages as $message) {
            $role = trim((string)($message['role'] ?? ''));
            $content = trim((string)($message['content'] ?? ''));
            if ($role !== '' && $content !== '') {
                $normalizedMessages[] = [
                    'role' => $role,
                    'content' => $content,
                ];
            }
        }

        $this->messages = $normalizedMessages;
    }


    /**
     * Returns all visible history messages.
     *
     * @return array<int,array{role:string,content:string}>
     */
    public function all(): array
    {
        return $this->messages;
    }


    /**
     * Returns the last history messages.
     *
     * @param int $limit Maximum number of messages.
     * @return array<int,array{role:string,content:string}>
     */
    public function last(int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        return array_slice($this->messages, -$limit);
    }
}
