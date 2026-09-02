<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Memory;

/**
 * Stores serializable assistant session data behind a named key.
 */
interface SessionStoreInterface
{
    /** Returns the value stored under the given key. */
    public function read(string $key): mixed;

    /** Replaces the value stored under the given key. */
    public function write(string $key, mixed $value): void;
}
