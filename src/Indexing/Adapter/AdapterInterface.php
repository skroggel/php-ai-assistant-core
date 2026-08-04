<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Indexing\Adapter;

use Madj2k\AiCore\Indexing\DTO\IndexableMetadata;

/**
 * Interface TextContentAdapterInterface
 *
 * Converts raw content files into normalized indexable text and metadata.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
     * Extracts normalized text from a source file.
     *
     * @param string $path File path.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableMetadata $metadata Metadata to enrich.
     * @return string Extracted text.
     */
    public function extract(string $path, IndexableMetadata $metadata): string;
}
