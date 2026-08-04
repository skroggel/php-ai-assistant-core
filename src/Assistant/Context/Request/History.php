<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Request;

/**
 * Class History
 *
 * Provides controlled access to the already visible messages.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
