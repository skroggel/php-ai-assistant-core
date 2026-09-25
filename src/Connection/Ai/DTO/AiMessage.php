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

namespace Madj2k\AiCore\Connection\Ai\DTO;

/**
 * Class AiMessage
 *
 * Contains one message for an AI chat request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class AiMessage
{
    /**
     * Message role.
     *
     * @var string
     */
    protected string $role = '';


    /**
     * Message content.
     *
     * @var string
     */
    protected string $content = '';


    /**
     * Message source for tracing and logging.
     *
     * @var string
     */
    protected string $source = '';


    /**
     * Additional message metadata.
     *
     * @var array<string, mixed>
     */
    protected array $metadata = [];


    /**
     * Constructor.
     *
     * @param string $role Message role.
     * @param string $content Message content.
     * @param string $source Message source for tracing and logging.
     * @param array<string, mixed> $metadata Additional message metadata.
     */
    public function __construct(string $role = '', string $content = '', string $source = '', array $metadata = [])
    {
        $this->role = $role;
        $this->content = $content;
        $this->source = $source;
        $this->metadata = $metadata;
    }


    /**
     * Returns the message role.
     *
     * @return string Message role.
     */
    public function getRole(): string
    {
        return $this->role;
    }


    /**
     * Sets the message role.
     *
     * @param string $role Message role.
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = trim($role);
    }


    /**
     * Returns the message content.
     *
     * @return string Message content.
     */
    public function getContent(): string
    {
        return $this->content;
    }


    /**
     * Sets the message content.
     *
     * @param string $content Message content.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }


    /**
     * Returns the message source.
     *
     * @return string Message source.
     */
    public function getSource(): string
    {
        return $this->source;
    }


    /**
     * Sets the message source.
     *
     * @param string $source Message source.
     * @return void
     */
    public function setSource(string $source): void
    {
        $this->source = trim($source);
    }


    /**
     * Returns additional message metadata.
     *
     * @return array<string, mixed> Additional message metadata.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }


    /**
     * Sets additional message metadata.
     *
     * @param array<string, mixed> $metadata Additional message metadata.
     * @return void
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }


    /**
     * Converts the message into an API-compatible array.
     *
     * @return array<string, mixed> API-compatible message.
     */
    public function toApiArray(): array
    {
        $message = [
            'role' => $this->role,
            'content' => $this->content,
        ];

        if (isset($this->metadata['tool_calls']) && is_array($this->metadata['tool_calls'])) {
            $message['tool_calls'] = array_map(
                static fn (array $toolCall): array => [
                    'id' => (string)($toolCall['id'] ?? ''),
                    'type' => 'function',
                    'function' => [
                        'name' => (string)($toolCall['name'] ?? ''),
                        'arguments' => json_encode($toolCall['arguments'] ?? [], JSON_THROW_ON_ERROR),
                    ],
                ],
                array_values(array_filter($this->metadata['tool_calls'], 'is_array')),
            );
        }

        if (isset($this->metadata['tool_call_id'])) {
            $message['tool_call_id'] = (string)$this->metadata['tool_call_id'];
        }

        return $message;
    }


    /**
     * Converts the message into a trace-compatible array.
     *
     * @return array<string, mixed> Trace-compatible message.
     */
    public function toTraceArray(): array
    {
        return [
            'role' => $this->role,
            'source' => $this->source,
            'content' => $this->content,
            'metadata' => $this->metadata,
        ];
    }
}
