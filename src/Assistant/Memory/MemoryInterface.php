<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Memory;

use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\DTO\LastRetrievalResult;

interface MemoryInterface
{
    public function start(string $chatIdentifier, int $chatStartTimestamp): void;

    /** @return array<int, array{role:string,content:string}> */
    public function getMessages(string $chatIdentifier): array;

    public function addMessage(string $chatIdentifier, string $role, string $content): void;
    public function setLastRetrievalResult(string $chatIdentifier, RetrievalResult $retrievalResult): ?LastRetrievalResult;
    public function getLastRetrievalResult(string $chatIdentifier): ?LastRetrievalResult;

    /** @return array<int, \Madj2k\AiCore\Assistant\DTO\RetrievalDocument> */
    public function getLastRetrievalDocuments(string $chatIdentifier): array;
}
