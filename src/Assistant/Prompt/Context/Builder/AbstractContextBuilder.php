<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt\Context\Builder;

use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;

/**
 * Class AbstractContextBuilder
 *
 * Provides shared helpers for prompt context builders.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractContextBuilder implements ContextBuilderInterface
{
    /**
     * Creates a prompt section or returns null for empty section data.
     *
     * @param string $title Section title.
     * @param string $content Section content.
     * @param int $priority Sorting priority.
     * @return \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection|null Prompt section.
     */
    protected function section(string $title, string $content, int $priority): ?PromptSection
    {
        $section = new PromptSection($title, $content, $priority);

        return $section->isEmpty() ? null : $section;
    }

    /**
     * Removes empty section placeholders.
     *
     * @param array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection|null> $sections Prompt sections.
     * @return array<int, \Madj2k\AiCore\Assistant\Prompt\Context\PromptSection> Filtered prompt sections.
     */
    protected function filterSections(array $sections): array
    {
        return array_values(array_filter(
            $sections,
            static fn (?PromptSection $section): bool => $section instanceof PromptSection && !$section->isEmpty()
        ));
    }
}
