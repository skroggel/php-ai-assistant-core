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

namespace Madj2k\AiCore\Indexing\Identity;

use Madj2k\AiCore\Indexing\DTO\IndexableDocument;

/**
 * Class SourceIdentityGenerator
 *
 * Creates deterministic source hashes and vector point identifiers for indexed documents.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class SourceIdentityGenerator
{
    /**
     * Creates a stable hash from source type, source identifier and language.
     */
    public function createSourceHash(IndexableDocument $document): string
    {
        $metadata = $document->getMetadata();

        return sha1(implode('|', [
            $metadata->getSourceType(),
            $metadata->getSourceIdentifier(),
            (string)$metadata->getLanguage(),
        ]));
    }

    /**
     * Creates a deterministic UUID-shaped identifier for one source chunk.
     */
    public function createVectorDocumentId(string $sourceHash, int $chunkIndex): string
    {
        $hash = md5(trim($sourceHash) . ':' . $chunkIndex);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12),
        );
    }
}
