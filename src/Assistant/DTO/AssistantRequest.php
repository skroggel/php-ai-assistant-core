<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
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
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
     * @param array<string,mixed> $runtimeSettings Runtime settings provided by the host application.
     */
    public function __construct(
        public string $query,
        public int $startTimestamp,
        public AssistantConfigurationInterface $assistantProfile,
        public string $chatIdentifier,
        public ?ServerRequestInterface $serverRequest,
        public array $runtimeSettings = [],
    ) {
    }
}
