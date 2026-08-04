<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Request;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Request
 *
 * Contains the original frontend request data for a conversational turn.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class Request
{
    /**
     * Current user query.
     *
     * @var string
     */
    protected string $query = '';


    /**
     * Stable key for the conversation.
     *
     * @var string
     */
    protected string $chatIdentifier = '';


    /**
     * Current server request.
     *
     * @var \Psr\Http\Message\ServerRequestInterface|null
     */
    protected ?ServerRequestInterface $serverRequest = null;


    /**
     * Runtime settings provided by the frontend plugin.
     *
     * @var array<string,mixed>
     */
    protected array $runtimeSettings = [];


    /**
     * Constructor.
     *
     * @param string $query Current user query.
     * @param string $chatIdentifier Stable key for the conversation.
     * @param \Psr\Http\Message\ServerRequestInterface|null $serverRequest Current server request.
     * @param array<string,mixed> $runtimeSettings Runtime settings provided by the frontend plugin.
     */
    public function __construct(
        string $query = '',
        string $chatIdentifier = '',
        ?ServerRequestInterface $serverRequest = null,
        array $runtimeSettings = []
    ) {
        $this->query = $query;
        $this->chatIdentifier = $chatIdentifier;
        $this->serverRequest = $serverRequest;
        $this->runtimeSettings = $runtimeSettings;
    }


    /**
     * Returns the current user query.
     *
     * @return string Current user query.
     */
    public function getQuery(): string
    {
        return $this->query;
    }


    /**
     * Sets the current user query.
     *
     * @param string $query Current user query.
     * @return void
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }


    /**
     * Returns the stable key for the conversation.
     *
     * @return string Stable key for the conversation.
     */
    public function getChatIdentifier(): string
    {
        return $this->chatIdentifier;
    }


    /**
     * Sets the stable key for the conversation.
     *
     * @param string $chatIdentifier Stable key for the conversation.
     * @return void
     */
    public function setChatIdentifier(string $chatIdentifier): void
    {
        $this->chatIdentifier = $chatIdentifier;
    }




    /**
     * Returns the current server request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null
     */
    public function getServerRequest(): ?ServerRequestInterface
    {
        return $this->serverRequest;
    }


    /**
     * Sets the current server request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface|null $serverRequest Current server request.
     * @return void
     */
    public function setServerRequest(?ServerRequestInterface $serverRequest): void
    {
        $this->serverRequest = $serverRequest;
    }


    /**
     * Returns all runtime settings.
     *
     * @return array<string,mixed>
     */
    public function getRuntimeSettings(): array
    {
        return $this->runtimeSettings;
    }


    /**
     * Returns one runtime setting by dot-separated path.
     *
     * @param string $path Dot-separated runtime setting path.
     * @param mixed $default Default value.
     * @return mixed Runtime setting value.
     */
    public function getRuntimeSetting(string $path, mixed $default = null): mixed
    {
        /**
         * @var mixed $value
         */
        $value = $this->runtimeSettings;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }


    /**
     * Sets all runtime settings.
     *
     * @param array<string,mixed> $runtimeSettings Runtime settings.
     * @return void
     */
    public function setRuntimeSettings(array $runtimeSettings): void
    {
        $this->runtimeSettings = $runtimeSettings;
    }
}
