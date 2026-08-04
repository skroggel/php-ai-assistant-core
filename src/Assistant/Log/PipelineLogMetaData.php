<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Log;

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;

/**
 * Class PipelineLogMetaData
 *
 * Carries the trace identity for one assistant request. The context is intentionally
 * small so it can be passed through application services without coupling them to a
 * specific pipeline implementation.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
class PipelineLogMetaData
{
    /**
     * Unique trace identifier shared by all log events of one chat request.
     *
     * @var string
     */
    protected string $traceId = '';


    /**
     * Original user query.
     *
     * @var string
     */
    protected string $query = '';


    /**
     * Logical conversation identifier, for example an assistant-profile scope.
     *
     * @var string
     */
    protected string $chatIdentifier = '';


    /**
     * assistant profile.
     *
     * @var \Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface|null
     */
    protected ?AssistantConfigurationInterface $assistantProfile = null;


    /**
     * Current route or pipeline mode.
     *
     * @var string
     */
    protected string $route = '';


    /**
     * Microtime at which the trace was created.
     *
     * @var float
     */
    protected float $startedAt = 0.0;


    /**
     * @param string $traceId Trace identifier.
     * @param string $query Original user query.
     * @param string $chatIdentifier Conversation identifier.
     * @param AssistantConfigurationInterface $assistantProfile
     * @param string $route Route name.
     */
    public function __construct(
        string $traceId,
        string $query,
        string $chatIdentifier,
        AssistantConfigurationInterface $assistantProfile,
        string $route = '',
    ) {
        $this->traceId = $traceId;
        $this->query = $query;
        $this->chatIdentifier = $chatIdentifier;
        $this->assistantProfile = $assistantProfile;
        $this->route = $route;
        $this->startedAt = microtime(true);
    }


    /**
     * @return string Trace identifier.
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }


    /**
     * @return string Original user query.
     */
    public function getQuery(): string
    {
        return $this->query;
    }


    /**
     * @return string Conversation identifier.
     */
    public function getChatIdentifier(): string
    {
        return $this->chatIdentifier;
    }


    /**
     * Updates the conversation identifier.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return void
     */
    public function setChatIdentifier(string $chatIdentifier): void
    {
        $this->chatIdentifier = trim($chatIdentifier);
    }


    /**
     * @return \Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface
     */
    public function getAssistantProfile(): AssistantConfigurationInterface
    {
        return $this->assistantProfile;
    }


    /**
     * Updates the resolved assistant profile data.
     *
     * @param \Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface $assistantProfile
     * @return void
     */
    public function setAssistantProfile(AssistantConfigurationInterface $assistantProfile): void
    {
        $this->assistantProfile = $assistantProfile;
    }


    /**
     * @return string Route name.
     */
    public function getRoute(): string
    {
        return $this->route;
    }


    /**
     * Updates the current route.
     *
     * @param string $route Route name.
     * @return void
     */
    public function setRoute(string $route): void
    {
        $this->route = trim($route);
    }


    /**
     * @return float Start time as microtime.
     */
    public function getStartedAt(): float
    {
        return $this->startedAt;
    }


    /**
     * Converts the context into payload data for trace events.
     *
     * @return array<string,mixed>
     */
    public function toPayload(): array
    {
        return [
            'trace_id' => $this->traceId,
            'query' => $this->query,
            'conversation_identifier' => $this->chatIdentifier,
            'assistant_profile_uid' => $this->assistantProfile->getUid(),
            'route' => $this->route,
        ];
    }
}
