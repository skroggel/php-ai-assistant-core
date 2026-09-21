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

namespace Madj2k\AiCore\Indexing\Adapter;

use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;

/**
 * Interface AdapterInterface
 *
 * Converts raw content files into normalized indexable text and metadata.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
interface AdapterInterface
{
    /**
     * Returns the adapter identifier.
     *
     * @return string Adapter identifier.
     */
    public function getIdentifier(): string;


    /**
     * Returns supported file extensions without dot.
     *
     * @return array<int, string> Supported extensions.
     */
    public function getSupportedExtensions(): array;


    /**
     * Checks if this adapter supports the given path.
     *
     * @param string $path File path.
     * @return bool
     */
    public function supports(string $path): bool;


    /**
     * Extracts one indexable document from a source file.
     *
     * @param string $path File path.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Metadata to enrich.
     * @return \Madj2k\AiCore\Indexing\DTO\IndexableDocument|null Extracted document or null when unreadable.
     */
    public function extract(string $path, DocumentMetadata $metadata): ?IndexableDocument;
}
