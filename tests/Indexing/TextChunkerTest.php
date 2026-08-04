<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Indexing;

use Madj2k\AiCore\Indexing\TextChunker;
use PHPUnit\Framework\TestCase;

final class TextChunkerTest extends TestCase
{
    public function testChunksTextWithOverlap(): void
    {
        $chunker = new TextChunker(chunkSize: 5, chunkOverlap: 2);

        self::assertSame(['abcde', 'defgh', 'ghij', 'j'], $chunker->chunk('abcdefghij'));
    }

    public function testReturnsNoChunkForBlankInput(): void
    {
        self::assertSame([], (new TextChunker())->chunk(" \n "));
    }
}
