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

use Madj2k\AiCore\Assistant\Tool\DTO\ToolCall;

/**
 * Class AiResponse
 *
 * Contains a normalized AI chat response.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class AiResponse
{
    /**
     * Response content.
     *
     * @var string
     */
    protected string $content = '';


    /**
     * Raw provider response.
     *
     * @var array<string, mixed>
     */
    protected array $rawResponse = [];


    /**
     * Constructor.
     *
     * @param string $content Response content.
     * @param array<string, mixed> $rawResponse Raw provider response.
     */
    public function __construct(string $content = '', array $rawResponse = [])
    {
        $this->content = $content;
        $this->rawResponse = $rawResponse;
    }


    /**
     * Returns the response content.
     *
     * @return string Response content.
     */
    public function getContent(): string
    {
        return $this->content;
    }


    /**
     * Sets the response content.
     *
     * @param string $content Response content.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }


    /**
     * Returns the raw provider response.
     *
     * @return array<string, mixed> Raw provider response.
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }


    /**
     * Returns normalized tool calls from provider response data.
     *
     * Supports OpenAI-compatible tool calls and Gemini function calls.
     *
     * @return array<int, \Madj2k\AiCore\Assistant\Tool\DTO\ToolCall> Tool calls.
     */
    public function getToolCalls(): array
    {
        $calls = [];
        $raw = $this->rawResponse;

        foreach ((array)($raw['choices'][0]['message']['tool_calls'] ?? []) as $toolCall) {
            if (!is_array($toolCall) || !is_array($toolCall['function'] ?? null)) {
                continue;
            }

            $arguments = json_decode((string)($toolCall['function']['arguments'] ?? '{}'), true);
            $calls[] = new ToolCall(
                id: (string)($toolCall['id'] ?? uniqid('tool_', true)),
                name: (string)($toolCall['function']['name'] ?? ''),
                arguments: is_array($arguments) ? $arguments : [],
            );
        }

        foreach ((array)($raw['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (!is_array($part) || !is_array($part['functionCall'] ?? null)) {
                continue;
            }

            $functionCall = $part['functionCall'];
            $calls[] = new ToolCall(
                id: (string)($functionCall['id'] ?? uniqid('tool_', true)),
                name: (string)($functionCall['name'] ?? ''),
                arguments: is_array($functionCall['args'] ?? null) ? $functionCall['args'] : [],
            );
        }

        return array_values(array_filter($calls, static fn (ToolCall $call): bool => $call->name !== ''));
    }


    /**
     * Sets the raw provider response.
     *
     * @param array<string, mixed> $rawResponse Raw provider response.
     * @return void
     */
    public function setRawResponse(array $rawResponse): void
    {
        $this->rawResponse = $rawResponse;
    }


    /**
     * Returns token usage information from the raw response.
     *
     * @return array<string, mixed> Token usage information.
     */
    public function getUsage(): array
    {
        /** @var mixed $usage */
        $usage = $this->rawResponse['usage'] ?? [];

        return is_array($usage) ? $usage : [];
    }

    /**
     * Returns the number of prompt tokens.
     *
     * @return int Prompt tokens.
     */
    public function getPromptTokens(): int
    {
        return (int)($this->getUsage()['prompt_tokens'] ?? 0);
    }


    /**
     * Returns the number of completion tokens.
     *
     * @return int Completion tokens.
     */
    public function getCompletionTokens(): int
    {
        return (int)($this->getUsage()['completion_tokens'] ?? 0);
    }


    /**
     * Returns the total number of tokens.
     *
     * @return int Total tokens.
     */
    public function getTotalTokens(): int
    {
        return (int)($this->getUsage()['total_tokens'] ?? 0);
    }
}
