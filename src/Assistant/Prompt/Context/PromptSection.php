<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Prompt\Context;

/**
 * Class PromptSection
 *
 * Represents one structured section of a prompt context before final formatting.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class PromptSection
{
    /**
     * Constructor.
     *
     * @param string $title Section title.
     * @param string $content Section content.
     * @param int $priority Sorting priority.
     */
    public function __construct(
        public string $title,
        public string $content,
        public int $priority = 100
    ) {
    }


    /**
     * Returns whether the section has no usable title or content.
     *
     * @return bool Empty section flag.
     */
    public function isEmpty(): bool
    {
        return trim($this->title) === '' || trim($this->content) === '';
    }


    /**
     * Converts the section to the prompt text format.
     *
     * @return string Formatted section.
     */
    public function toText(): string
    {
        return '[' . trim($this->title) . ']' . "\n" . trim($this->content);
    }
}
