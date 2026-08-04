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
 * Class AiMessage
 *
 * Contains one message for an AI chat request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
     * @return array{role: string, content: string} API-compatible message.
     */
    public function toApiArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
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
