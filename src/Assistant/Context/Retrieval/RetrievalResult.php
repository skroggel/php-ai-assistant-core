<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */
namespace Madj2k\AiCore\Assistant\Context\Retrieval;

/**
 * Class RetrievalResult
 *
 * Holds named retrieval groups and the answer context derived from them.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class RetrievalResult
{
    /**
     * Named retrieval groups collected by the pipeline.
     *
     * @var array<int, RetrievalGroup>
     */
    protected array $groups = [];


    /**
     * Answer context derived from raw retrieval results.
     *
     * @var string
     */
    protected string $answerContext = '';


    /**
     * Returns the named retrieval groups.
     *
     * @return array<int, RetrievalGroup> Retrieval groups.
     */
    public function getGroups(): array
    {
        return $this->groups;
    }


    /**
     * Replaces all retrieval groups.
     *
     * @param array<int, RetrievalGroup> $groups Retrieval groups.
     * @return void
     */
    public function replaceGroups(array $groups): void
    {
        $this->groups = array_values(array_filter(
            $groups,
            static fn (mixed $group): bool => $group instanceof RetrievalGroup,
        ));
        $this->answerContext = '';
    }


    /**
     * Appends one named retrieval group.
     *
     * @param RetrievalGroup $group Retrieval group.
     * @return void
     */
    public function storeGroup(RetrievalGroup $group): void
    {
        foreach ($this->groups as $existingGroup) {
            if ($existingGroup->identifier === $group->identifier) {
                throw new \LogicException(sprintf(
                    'Retrieval name "%s" is already present in the current pipeline context.',
                    $group->identifier,
                ), 1786047701);
            }
        }

        $this->groups[] = $group;
        $this->answerContext = '';
    }

    /**
     * Returns all normalized documents in retrieval order.
     *
     * @return array<int, \Madj2k\AiCore\Assistant\DTO\RetrievalDocument> Retrieval documents.
     */
    public function getDocuments(): array
    {
        $documents = [];
        foreach ($this->groups as $group) {
            array_push($documents, ...$group->documents);
        }

        return $documents;
    }


    /**
     * Returns the total number of normalized documents.
     *
     * @return int Document count.
     */
    public function getDocumentCount(): int
    {
        return count($this->getDocuments());
    }


    /**
     * Returns the total number of source-specific raw results.
     *
     * @return int Raw result count.
     */
    public function getRawResultCount(): int
    {
        return array_sum(array_map(
            static fn (RetrievalGroup $group): int => count($group->rawResults),
            $this->groups,
        ));
    }


    /**
     * Returns the answer context derived from raw retrieval results.
     *
     * @return string Answer context derived from raw retrieval results.
     */
    public function getAnswerContext(): string
    {
        return $this->answerContext;
    }


    /**
     * Sets the answer context derived from raw retrieval results.
     *
     * @param string $answerContext Answer context derived from raw retrieval results.
     * @return void
     */
    public function setAnswerContext(string $answerContext): void
    {
        $this->answerContext = trim($answerContext);
    }
}
