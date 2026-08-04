<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Indexing;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Adapter\JsonAdapter;
use Madj2k\AiCore\Indexing\Adapter\PlainAdapter;
use Madj2k\AiCore\Indexing\Resolver\AdapterResolver;
use PHPUnit\Framework\TestCase;

final class AdapterResolverTest extends TestCase
{
    public function testResolvesFirstSupportingAdapter(): void
    {
        $resolver = new AdapterResolver([new JsonAdapter(), new PlainAdapter()]);

        self::assertInstanceOf(JsonAdapter::class, $resolver->getForPath('/tmp/data.json'));
        self::assertInstanceOf(PlainAdapter::class, $resolver->getForPath('/tmp/readme.md'));
    }

    public function testReportsUnsupportedFiles(): void
    {
        $resolver = new AdapterResolver([new PlainAdapter()]);

        $this->expectException(IndexingException::class);
        $resolver->getForPath('/tmp/image.png');
    }
}
