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

namespace Madj2k\AiCore\Assistant\Tool\Execution;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Connection\Ai\DTO\AiMessage;
use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Exception\AssistantException;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolCall;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolDefinition;
use Madj2k\AiCore\Assistant\Tool\Registry\ToolRegistry;

/**
 * Class ToolCallingService
 *
 * Executes the provider-neutral model/tool interaction loop.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class ToolCallingService
{
    /**
     * @param AiConnectorResolver $aiConnectorResolver AI connector resolver.
     * @param ToolRegistry $toolRegistry Tool registry.
     * @param int $maxRounds Maximum model/tool rounds.
     */
    public function __construct(
        private AiConnectorResolver $aiConnectorResolver,
        private ToolRegistry $toolRegistry,
        private int $maxRounds = 5,
    ) {
    }

    /**
     * Runs the tool loop until the model returns normal content.
     *
     * @param Context|null $context Current assistant context.
     * @param array<int, AiMessage> $messages Initial messages.
     * @param PipelineStepConfigurationInterface $step Pipeline step.
     * @param object $connection AI connection configuration.
     * @return string Final model answer.
     * @throws AssistantException If the loop cannot produce an answer.
     */
    public function run(?Context $context, array $messages, PipelineStepConfigurationInterface $step, object $connection): string
    {
        $tools = $this->toolRegistry->all($context, $step);
        if ($tools === []) {
            throw new AssistantException('Tool calling requires at least one registered tool.', 1789002002);
        }

        $connector = $this->aiConnectorResolver->get($connection->getConnectorIdentifier());
        $options = ['tools' => array_map(static fn (ToolDefinition $tool): array => $tool->toArray(), $tools)];

        for ($round = 0; $round < max(1, $this->maxRounds); $round++) {
            $response = $connector->chat($connection, new AiRequest(
                messages: $messages,
                model: $step->getModel() ?? $connection->getDefaultModel(),
                temperature: $step->getTemperature(),
                maxTokens: $step->getMaxTokens(),
                options: $options,
            ));

            $toolCalls = $response->getToolCalls();
            if ($toolCalls === []) {
                return trim($response->getContent());
            }

            $messages[] = new AiMessage(
                role: 'assistant',
                content: $response->getContent(),
                source: 'tool_call',
                metadata: ['tool_calls' => array_map(static fn (ToolCall $call): array => [
                    'id' => $call->id,
                    'name' => $call->name,
                    'arguments' => $call->arguments,
                ], $toolCalls)],
            );

            foreach ($toolCalls as $call) {
                $result = $this->toolRegistry->call($call, $context, $step);
                $messages[] = new AiMessage(
                    role: 'tool',
                    content: $result->content,
                    source: 'tool_result',
                    metadata: [
                        'tool_call_id' => $call->id,
                        'tool_name' => $call->name,
                        'is_error' => $result->isError,
                    ],
                );
            }
        }

        throw new AssistantException('The tool-calling loop exceeded its maximum round count.', 1789002003);
    }
}
