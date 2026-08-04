<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\DTO;

/**
 * Class ChatTurnResponse
 *
 * Result returned to the frontend for one chat turn.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class AssistantResponse
{
    /**
     * Constructor.
     *
     * @param string $answer Final answer text.
     * @param array<string,mixed> $context Exported runtime context.
     * @param array<string,mixed> $debug Debug payload.
     */
    public function __construct(
        public string $answer,
        public array $context = [],
        public array $debug = [],
    ) {
    }
}
