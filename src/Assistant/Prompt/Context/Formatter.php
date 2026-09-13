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

namespace Madj2k\AiCore\Assistant\Prompt\Context;

use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;

/**
 * Class Formatter
 *
 * Formats prompt sections into the plain-text prompt context.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
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
