<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Answer;

/**
 * Class AnswerState
 *
 * Holds the answer candidate and the final answer.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AnswerState
{
    /**
     * Answer candidate.
     *
     * @var string
     */
    protected string $candidate = '';


    /**
     * Final answer.
     *
     * @var string
     */
    protected string $final = '';


    /**
     * Returns the answer candidate.
     *
     * @return string Answer candidate.
     */
    public function getCandidate(): string
    {
        return $this->candidate;
    }


    /**
     * Sets the answer candidate.
     *
     * @param string $candidate Answer candidate.
     * @return void
     */
    public function setCandidate(string $candidate): void
    {
        $this->candidate = trim($candidate);
    }


    /**
     * Returns the final answer.
     *
     * @return string Final answer.
     */
    public function getFinal(): string
    {
        return $this->final;
    }


    /**
     * Sets the final answer.
     *
     * @param string $final Final answer.
     * @return void
     */
    public function setFinal(string $final): void
    {
        $this->final = trim($final);
    }
}
