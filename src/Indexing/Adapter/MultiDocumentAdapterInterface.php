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

use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\DTO\DocumentMetadata;

/**
 * Interface MultiDocumentAdapterInterface
 *
 * Allows adapters to split one source file into multiple indexable documents.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface MultiDocumentAdapterInterface
{
    /**
     * Extracts one or more documents from a source file.
     *
     * @param string $path File path.
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Base metadata to copy and enrich.
     * @return array<int, \Madj2k\AiCore\Indexing\DTO\IndexableDocument> Extracted documents.
     */
    public function extractDocuments(string $path, DocumentMetadata $metadata): array;
}
