<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Memory;

use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\DTO\LastRetrievalResult;

/**
 * Interface MemoryInterface
 *
 * Defines conversation history and retrieval state storage for assistant sessions.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface MemoryInterface
{
    /** Starts or resumes a chat session. */
    public function start(string $chatIdentifier, int $chatStartTimestamp): void;

    /**
     * Returns the ordered conversation messages.
     *
     * @return array<int, array{role:string,content:string}>
     */
    public function getMessages(string $chatIdentifier): array;

    /** Adds one conversation message. */
    public function addMessage(string $chatIdentifier, string $role, string $content): void;

    /** Stores a retrieval result and returns its serializable representation. */
    public function setLastRetrievalResult(string $chatIdentifier, RetrievalResult $retrievalResult): ?LastRetrievalResult;

    /** Returns the most recently stored retrieval result. */
    public function getLastRetrievalResult(string $chatIdentifier): ?LastRetrievalResult;

    /**
     * Returns the documents from the most recently stored retrieval result.
     *
     * @return array<int, \Madj2k\AiCore\Assistant\DTO\RetrievalDocument>
     */
    public function getLastRetrievalDocuments(string $chatIdentifier): array;
}
