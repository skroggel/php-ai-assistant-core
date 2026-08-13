<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\DTO;

/**
 * Class DirectInteraction
 *
 * Immutable configuration for one explicit assistant request that bypasses
 * the configured processing pipeline.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class DirectInteraction
{
    /**
     * Constructor.
     *
     * @param string $instruction Trusted system instruction for the direct AI request.
     * @param int $maxTokens Maximum number of response tokens.
     * @param bool $remember Whether the request and response should be stored in conversation memory.
     */
    public function __construct(
        public string $instruction,
        public int $maxTokens = 80,
        public bool $remember = false,
    ) {
    }
}
