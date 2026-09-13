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

namespace Madj2k\AiCore\Assistant\Memory;

use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\DTO\LastRetrievalResult;

/**
 * Interface MemoryInterface
 *
 * Defines conversation history and retrieval state storage for assistant sessions.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface MemoryInterface
{

    /**
     * Starts or resumes a chat session.
     *
     * @param string $chatIdentifier
     * @param int $chatStartTimestamp
     * @return void
     */
    public function start(string $chatIdentifier, int $chatStartTimestamp): void;


    /**
     * Returns the ordered conversation messages.
     *
     * @return array<int, array{role:string,content:string}>
     */
    public function getMessages(string $chatIdentifier): array;


    /**
     * Adds one conversation message.
     *
     * @param string $chatIdentifier
     * @param string $role
     * @param string $content
     * @return void
     */
    public function addMessage(string $chatIdentifier, string $role, string $content): void;


    /**
     * Stores a retrieval result and returns its serializable representation.
     *
     * @param string $chatIdentifier
     * @param \Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult $retrievalResult
     * @return \Madj2k\AiCore\Assistant\DTO\LastRetrievalResult|null
     */
    public function setLastRetrievalResult(string $chatIdentifier, RetrievalResult $retrievalResult): ?LastRetrievalResult;


    /**
     * Returns the most recently stored retrieval result.
     *
     * @param string $chatIdentifier
     * @return \Madj2k\AiCore\Assistant\DTO\LastRetrievalResult|null
     */
    public function getLastRetrievalResult(string $chatIdentifier): ?LastRetrievalResult;
}
