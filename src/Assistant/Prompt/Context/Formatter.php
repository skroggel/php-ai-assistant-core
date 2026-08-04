<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt\Context;

use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;

/**
 * Class Formatter
 *
 * Formats prompt sections into the plain-text prompt context.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class Formatter
{
    /**
     * Formats prompt sections into a plain-text prompt context.
     *
     * @param array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> $sections Prompt sections.
     * @return string Formatted prompt context.
     */
    public function format(array $sections): string
    {
        $parts = [];

        foreach ($sections as $section) {
            if (!$section instanceof PromptSection || $section->isEmpty()) {
                continue;
            }

            $parts[] = $section->toText();
        }

        return implode("\n\n", $parts);
    }
}
