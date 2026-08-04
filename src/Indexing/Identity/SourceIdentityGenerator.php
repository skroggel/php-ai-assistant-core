<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Identity;

use Madj2k\AiCore\Indexing\DTO\IndexableDocument;

final class SourceIdentityGenerator
{
    public function createSourceHash(IndexableDocument $document): string
    {
        $metadata = $document->getMetadata();

        return sha1(implode('|', [
            $metadata->getSourceType(),
            $metadata->getSourceIdentifier(),
            (string)$metadata->getLanguage(),
        ]));
    }

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
