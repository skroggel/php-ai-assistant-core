<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\DTO;

use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalGroup;

/**
 * Class LastRetrievalResult
 *
 * Represents named retrieval groups stored as one conversation snapshot.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class LastRetrievalResult
{
    /**
     * Chat identifier.
     *
     * @var string
     */
    public string $chatIdentifier;


    /**
     * Named retrieval groups.
     *
     * @var array<int, RetrievalGroup>
     */
    public array $groups;


    /**
     * Creation timestamp.
     *
     * @var int
     */
    public int $createdAt;


    /**
     * Constructor.
     *
     * @param string $chatIdentifier Chat identifier.
     * @param array<int, RetrievalGroup|array<string, mixed>> $groups Named retrieval groups.
     * @param int $createdAt Creation timestamp.
     */
    public function __construct(
        string $chatIdentifier = '',
        array $groups = [],
        int $createdAt = 0,
    ) {
        $this->chatIdentifier = trim($chatIdentifier);
        $this->groups = $this->normalizeGroups($groups);
        $this->createdAt = $createdAt;
    }


    /**
     * Returns all normalized documents in retrieval order.
     *
     * @return array<int, RetrievalDocument> Retrieval documents.
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
     * Returns the total number of retrieved documents.
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
     * Returns a serializable representation.
     *
     * @return array{chatIdentifier:string,createdAt:int,groups:array<int,array<string,mixed>>} Serialized retrieval snapshot.
     */
    public function toArray(): array
    {
        return [
            'chatIdentifier' => $this->chatIdentifier,
            'createdAt' => $this->createdAt,
            'groups' => array_map(
                static fn (RetrievalGroup $group): array => $group->toArray(),
                $this->groups,
            ),
        ];
    }


    /**
     * Normalizes serialized and instantiated retrieval groups.
     *
     * @param array<int, RetrievalGroup|array<string, mixed>> $groups Retrieval groups.
     * @return array<int, RetrievalGroup> Normalized retrieval groups.
     */
    private function normalizeGroups(array $groups): array
    {
        $normalized = [];
        foreach ($groups as $group) {
            if ($group instanceof RetrievalGroup) {
                $normalized[] = $group;
            } elseif (is_array($group)) {
                $normalized[] = RetrievalGroup::fromArray($group);
            }
        }

        return $normalized;
    }
}
