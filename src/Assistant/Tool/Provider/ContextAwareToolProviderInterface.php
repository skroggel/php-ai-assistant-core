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

namespace Madj2k\AiCore\Assistant\Tool\Provider;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolCall;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolDefinition;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolResult;

/**
 * Interface ContextAwareToolProviderInterface
 *
 * Defines providers whose tools depend on the active assistant context.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface ContextAwareToolProviderInterface extends ToolProviderInterface
{
    /**
     * Returns tools enabled for the current context.
     *
     * @param Context $context Current assistant context.
     * @return array<int, ToolDefinition> Tool definitions.
     */
    public function getToolsForContext(Context $context, ?PipelineStepConfigurationInterface $step = null): array;

    /**
     * Executes a tool call in the current context.
     *
     * @param Context $context Current assistant context.
     * @param ToolCall $call Tool call.
     * @return ToolResult Tool result.
     */
    public function callForContext(Context $context, ToolCall $call, ?PipelineStepConfigurationInterface $step = null): ToolResult;
}
