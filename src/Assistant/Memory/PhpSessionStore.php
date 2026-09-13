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

/**
 * Persists assistant state in the native PHP session.
 *
 * Sessions opened by this store are closed immediately after each operation so
 * a long-running streamed response does not hold the session lock.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class PhpSessionStore implements SessionStoreInterface
{
    /**
     * Returns a value from the native PHP session.
     *
     * The session is opened for the duration of the read operation and closed
     * afterwards when it was not already active.
     *
     * @param string $key Session key.
     * @return mixed Stored value or null when the key does not exist.
     * @throws \RuntimeException When the native PHP session cannot be opened.
     */
    public function read(string $key): mixed
    {
        $startedHere = $this->openSession();

        try {
            return $_SESSION[$key] ?? null;
        } finally {
            $this->closeSession($startedHere);
        }
    }


    /**
     * Stores a value in the native PHP session.
     *
     * The session is opened for the duration of the write operation and closed
     * afterwards when it was not already active.
     *
     * @param string $key Session key.
     * @param mixed $value Value to store.
     * @return void
     * @throws \RuntimeException When the native PHP session cannot be opened.
     */
    public function write(string $key, mixed $value): void
    {
        $startedHere = $this->openSession();

        try {
            $_SESSION[$key] = $value;
        } finally {
            $this->closeSession($startedHere);
        }
    }


    /**
     * Opens the native PHP session when it is not active yet.
     *
     * A previously initialized session can be reopened while an SSE response
     * is being emitted, provided cookie handling and the cache limiter were
     * disabled before the response headers were sent.
     *
     * @return bool True when this method opened the session, otherwise false.
     * @throws \RuntimeException When sessions are disabled or the session
     *     cannot safely be opened in the current response phase.
     */
    private function openSession(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return false;
        }
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Native PHP sessions are disabled.', 1788342001);
        }
        if (headers_sent() && session_id() === '') {
            throw new \RuntimeException('A PHP session cannot be started after response headers were sent.', 1788342002);
        }

        if (headers_sent()) {
            if (filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOL)) {
                throw new \RuntimeException(
                    'The PHP session was not prepared before response streaming started.',
                    1788342004,
                );
            }
            if (ini_get('session.cache_limiter') !== '') {
                throw new \RuntimeException(
                    'The PHP session cache limiter was not disabled before response streaming started.',
                    1788342005,
                );
            }
        }

        $options = headers_sent() ? [] : ['cache_limiter' => ''];
        if (!session_start($options)) {
            throw new \RuntimeException('The native PHP session could not be started.', 1788342003);
        }

        return true;
    }


    /**
     * Closes a session opened by this store and prepares it for SSE streaming.
     *
     * The initial session start schedules the session cookie. Further cookie
     * handling is then disabled so the same session can be reopened after the
     * response headers have already been sent.
     *
     * @param bool $startedHere Whether this store opened the active session.
     * @return void
     * @throws \RuntimeException When cookie handling cannot be disabled.
     */
    private function closeSession(bool $startedHere): void
    {
        if ($startedHere && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();

            // The first access happens before the streaming response is sent and
            // has already scheduled the session cookie. Disable further cookie
            // handling so the same session can be reopened after SSE headers.
            if (!headers_sent() && ini_get('session.use_cookies') !== '0') {
                if (ini_set('session.use_cookies', '0') === false) {
                    throw new \RuntimeException(
                        'PHP session cookie handling could not be disabled for response streaming.',
                        1788342006,
                    );
                }
            }
        }
    }
}
