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

namespace Madj2k\AiCore\Assistant\Tool\Registry;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Exception\AssistantException;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolCall;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolDefinition;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolResult;
use Madj2k\AiCore\Assistant\Tool\Provider\ContextAwareToolProviderInterface;
use Madj2k\AiCore\Assistant\Tool\Provider\ToolProviderInterface;

/**
 * Class ToolRegistry
 *
 * Collects tool providers and resolves tools by name.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class ToolRegistry
{
    /** @var array<string, ToolProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<ToolProviderInterface> $providers Tool providers.
     */
    public function __construct(iterable $providers = [])
    {
        foreach ($providers as $provider) {
            if ($provider instanceof ToolProviderInterface) {
                $this->providers[$provider->getIdentifier()] = $provider;
            }
        }
    }

    /**
     * Returns all available tools.
     *
     * @return array<int, ToolDefinition> Tool definitions.
     */
    public function all(?Context $context = null, ?PipelineStepConfigurationInterface $step = null): array
    {
        $tools = [];
        foreach ($this->providers as $provider) {
            $providerTools = $context !== null && $provider instanceof ContextAwareToolProviderInterface
                ? $provider->getToolsForContext($context, $step)
                : $provider->getTools();
            foreach ($providerTools as $tool) {
                if (!$tool instanceof ToolDefinition || isset($tools[$tool->name])) {
                    continue;
                }
                $tools[$tool->name] = $tool;
            }
        }

        return array_values($tools);
    }

    /**
     * Executes a tool call through its registered provider.
     *
     * @param ToolCall $call Tool call.
     * @return ToolResult Tool result.
     * @throws AssistantException If no provider exposes the requested tool.
     */
    public function call(ToolCall $call, ?Context $context = null, ?PipelineStepConfigurationInterface $step = null): ToolResult
    {
        foreach ($this->providers as $provider) {
            $providerTools = $context !== null && $provider instanceof ContextAwareToolProviderInterface
                ? $provider->getToolsForContext($context, $step)
                : $provider->getTools();
            foreach ($providerTools as $tool) {
                if ($tool instanceof ToolDefinition && $tool->name === $call->name) {
                    return $context !== null && $provider instanceof ContextAwareToolProviderInterface
                        ? $provider->callForContext($context, $call, $step)
                        : $provider->call($call);
                }
            }
        }

        throw new AssistantException(sprintf('No tool provider exposes "%s".', $call->name), 1789002001);
    }
}
