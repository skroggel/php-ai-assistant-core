<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\Enum;

/**
 * Enum AssistantPipelineFailureStrategy
 *
 * Defines how the pipeline behaves when a step fails.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
enum AssistantPipelineFailureStrategy: string
{
    case Continue = 'continue';
    case Stop = 'stop';
    case Fallback = 'fallback';
}
