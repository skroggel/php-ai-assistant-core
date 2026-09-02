<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Memory;

use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\DTO\LastRetrievalResult;

/**
 * Stores conversation history and retrieval state in a pluggable session store.
 */
class SessionMemory implements MemoryInterface
{
    public const SESSION_KEY = 'aiassistant_frontend_conversations';

    public function __construct(
        private readonly SessionStoreInterface $sessionStore,
        private readonly int $maxMessages = 20,
    ) {
    }

    public function start(string $chatIdentifier, int $chatStartTimestamp): void
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $storedStartTimestamp = (int)($conversations[$chatIdentifier]['start_timestamp'] ?? 0);

        if ($storedStartTimestamp !== $chatStartTimestamp) {
            $conversations[$chatIdentifier] = $this->createConversation($chatStartTimestamp);
            $this->saveConversations($conversations);
        }
    }

    public function getMessages(string $chatIdentifier): array
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $messages = $this->loadConversations($chatIdentifier)[$chatIdentifier]['messages'] ?? [];
        if (!is_array($messages)) {
            return [];
        }

        return array_values(array_filter(
            $messages,
            static fn (mixed $message): bool => is_array($message)
                && in_array((string)($message['role'] ?? ''), ['user', 'assistant'], true)
                && trim((string)($message['content'] ?? '')) !== '',
        ));
    }

    public function addMessage(string $chatIdentifier, string $role, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            return;
        }

        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $conversations[$chatIdentifier]['messages'][] = [
            'role' => $role === 'assistant' ? 'assistant' : 'user',
            'content' => $content,
        ];

        $messages = $conversations[$chatIdentifier]['messages'];
        $limit = max(2, $this->maxMessages);
        if (count($messages) > $limit) {
            $conversations[$chatIdentifier]['messages'] = array_slice($messages, -$limit);
        }

        $this->saveConversations($conversations);
    }

    public function setLastRetrievalResult(string $chatIdentifier, RetrievalResult $retrievalResult): ?LastRetrievalResult
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $groups = $retrievalResult->getGroups();

        if ($groups === []) {
            $conversations[$chatIdentifier]['lastRetrievalResult'] = [];
            $this->saveConversations($conversations);
            return null;
        }

        $lastRetrievalResult = new LastRetrievalResult(
            chatIdentifier: $chatIdentifier,
            groups: $groups,
            createdAt: time(),
        );
        $conversations[$chatIdentifier]['lastRetrievalResult'] = $lastRetrievalResult->toArray();
        $this->saveConversations($conversations);

        return $lastRetrievalResult;
    }

    public function getLastRetrievalResult(string $chatIdentifier): ?LastRetrievalResult
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $result = $this->loadConversations($chatIdentifier)[$chatIdentifier]['lastRetrievalResult'] ?? [];
        if (!is_array($result) || !is_array($result['groups'] ?? null) || $result['groups'] === []) {
            return null;
        }

        return new LastRetrievalResult(
            chatIdentifier: (string)($result['chatIdentifier'] ?? ''),
            groups: $result['groups'],
            createdAt: (int)($result['createdAt'] ?? 0),
        );
    }

    /** @return array<string,mixed> */
    private function loadConversations(string $chatIdentifier): array
    {
        $storedData = $this->sessionStore->read(self::SESSION_KEY);
        $conversations = is_array($storedData) ? $storedData : [];

        if (!isset($conversations[$chatIdentifier]) || !is_array($conversations[$chatIdentifier])) {
            $conversations[$chatIdentifier] = $this->createConversation();
        }
        if (!is_array($conversations[$chatIdentifier]['messages'] ?? null)) {
            $conversations[$chatIdentifier]['messages'] = [];
        }
        if (!is_array($conversations[$chatIdentifier]['lastRetrievalResult'] ?? null)) {
            $conversations[$chatIdentifier]['lastRetrievalResult'] = [];
        }

        return $conversations;
    }

    /** @param array<string,mixed> $conversations */
    private function saveConversations(array $conversations): void
    {
        $this->sessionStore->write(self::SESSION_KEY, $conversations);
    }

    /** @return array{start_timestamp:int,messages:array<int,mixed>,lastRetrievalResult:array<mixed>} */
    private function createConversation(int $startTimestamp = 0): array
    {
        return [
            'start_timestamp' => $startTimestamp,
            'messages' => [],
            'lastRetrievalResult' => [],
        ];
    }

    private function normalizeIdentifier(string $chatIdentifier): string
    {
        $chatIdentifier = trim($chatIdentifier);
        return $chatIdentifier !== '' ? $chatIdentifier : 'default';
    }
}
