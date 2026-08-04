<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\Enum;

/**
 * Enum AssistantPipelineStage
 *
 * Describes the semantic position of a step in the assistant pipeline.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
enum AssistantPipelineStage: string
{
    case PreRetrieval = 'pre_retrieval';
    case Retrieval = 'retrieval';
    case PostRetrieval = 'post_retrieval';
    case PreAnswer = 'pre_answer';
    case PostAnswer = 'post_answer';
}
