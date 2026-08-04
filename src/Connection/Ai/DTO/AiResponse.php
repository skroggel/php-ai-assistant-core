<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai\DTO;

/**
 * Class AiResponse
 *
 * Contains a normalized AI chat response.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
