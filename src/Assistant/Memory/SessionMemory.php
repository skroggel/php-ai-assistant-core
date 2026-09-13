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
 * Stores conversation history and retrieval state in a pluggable session store.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
class SessionMemory implements MemoryInterface
{

    /**
     * sessionKey
     */
    public const string SESSION_KEY = 'aiassistant_frontend_conversations';


    /**
     * Constructor.
     *
     * @param SessionStoreInterface $sessionStore Store used to persist conversation data.
     * @param int $maxMessages Maximum number of messages retained per conversation.
     */
    public function __construct(
        private readonly SessionStoreInterface $sessionStore,
        private readonly int $maxMessages = 20,
    ) {
    }


    /**
     * Starts or resumes a conversation.
     *
     * Existing conversation data is retained when the stored start timestamp
     * matches the supplied timestamp. A different timestamp starts a new
     * conversation and clears its messages and last retrieval result.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param int $chatStartTimestamp Frontend timestamp identifying the chat instance.
     * @return void
     */
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


    /**
     * Returns the ordered, valid messages of a conversation.
     *
     * Invalid entries and messages with empty content are omitted.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return array<int,array{role:string,content:string}> Conversation messages.
     */
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


    /**
     * Adds a message to a conversation.
     *
     * Empty content is ignored. The assistant role is preserved explicitly;
     * every other role is normalized to the user role. Older messages are
     * removed when the configured history limit is exceeded.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param string $role Message role.
     * @param string $content Message content.
     * @return void
     */
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


    /**
     * Stores the most recent retrieval result for a conversation.
     *
     * An empty retrieval result clears the previously stored result.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param RetrievalResult $retrievalResult Retrieval result to store.
     * @return LastRetrievalResult|null Serializable result or null for an empty result.
     */
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


    /**
     * Returns the most recently stored retrieval result of a conversation.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return LastRetrievalResult|null Stored result or null when none is available.
     */
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


    /**
     * Loads all conversations and initializes the requested conversation.
     *
     * Invalid message and retrieval collections are normalized to empty arrays.
     *
     * @param string $chatIdentifier Normalized conversation identifier.
     * @return array<string,mixed> Stored conversations.
     */
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


    /**
     * Persists all conversations in the configured session store.
     *
     * @param array<string,mixed> $conversations Conversations to store.
     * @return void
     */
    private function saveConversations(array $conversations): void
    {
        $this->sessionStore->write(self::SESSION_KEY, $conversations);
    }


    /**
     * Creates an empty conversation state.
     *
     * @param int $startTimestamp Frontend timestamp identifying the chat instance.
     * @return array{start_timestamp:int,messages:array<int,mixed>,lastRetrievalResult:array<mixed>} Conversation state.
     */
    private function createConversation(int $startTimestamp = 0): array
    {
        return [
            'start_timestamp' => $startTimestamp,
            'messages' => [],
            'lastRetrievalResult' => [],
        ];
    }


    /**
     * Normalizes a conversation identifier.
     *
     * @param string $chatIdentifier Raw conversation identifier.
     * @return string Trimmed identifier or the default identifier when empty.
     */
    private function normalizeIdentifier(string $chatIdentifier): string
    {
        $chatIdentifier = trim($chatIdentifier);
        return $chatIdentifier !== '' ? $chatIdentifier : 'default';
    }
}
