<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Identity;

use Madj2k\AiCore\Indexing\DTO\IndexableDocument;

/**
 * Class SourceIdentityGenerator
 *
 * Creates deterministic source hashes and vector point identifiers for indexed documents.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
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
