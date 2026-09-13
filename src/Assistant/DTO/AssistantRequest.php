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

namespace Madj2k\AiCore\Assistant\DTO;

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AssistantRequest
 *
 * Immutable input for one assistant turn.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class AssistantRequest
{
    /**
     * Constructor.
     *
     * @param string $query User query.
     * @param int $startTimestamp Request start timestamp.
     * @param \Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface $assistantProfile Active assistant configuration.
     * @param string $chatIdentifier Conversation identifier.
     * @param \Psr\Http\Message\ServerRequestInterface|null $serverRequest Current server request.
     * @param \Madj2k\AiCore\Assistant\DTO\ChatOptions $chatOptions Structured chat options.
     * @param array<string,mixed> $runtimeSettings Runtime settings provided by the host application.
     */
    public function __construct(
        public string $query,
        public int $startTimestamp,
        public AssistantConfigurationInterface $assistantProfile,
        public string $chatIdentifier,
        public ?ServerRequestInterface $serverRequest,
        public ChatOptions $chatOptions,
        public array $runtimeSettings = [],
    ) {
    }
}
