<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\Enum;

/**
 * Enum HistoryMode
 *
 * Defines how much chat history a pipeline step receives.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
enum HistoryMode: string
{
    case None = 'none';
    case LastN = 'last_n';
}
