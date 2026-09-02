<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Memory;

/**
 * Persists assistant state in the native PHP session.
 *
 * Sessions opened by this store are closed immediately after each operation so
 * a long-running streamed response does not hold the session lock.
 */
final class PhpSessionStore implements SessionStoreInterface
{
    public function read(string $key): mixed
    {
        $startedHere = $this->openSession();

        try {
            return $_SESSION[$key] ?? null;
        } finally {
            $this->closeSession($startedHere);
        }
    }

    public function write(string $key, mixed $value): void
    {
        $startedHere = $this->openSession();

        try {
            $_SESSION[$key] = $value;
        } finally {
            $this->closeSession($startedHere);
        }
    }

    /** Returns whether this store opened the session. */
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
            if ((bool)filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOL)) {
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
