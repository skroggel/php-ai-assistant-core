<?php

declare(strict_types=1);

namespace Madj2k\AiCore\Tests\DTO;

use Madj2k\AiCore\DTO\DocumentMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Tests language metadata semantics.
 */
final class DocumentMetadataTest extends TestCase
{
    /**
     * Verifies the unset language defaults.
     *
     * @return void
     */
    public function testLanguageDefaultsToUnsetValues(): void
    {
        $metadata = new DocumentMetadata();

        self::assertSame('', $metadata->getLanguage());
        self::assertSame(-1, $metadata->getLanguageId());
    }

    /**
     * Verifies ISO language and TYPO3 language ID are stored independently.
     *
     * @return void
     */
    public function testLanguageAndLanguageIdAreStoredSeparately(): void
    {
        $metadata = new DocumentMetadata(language: 'de', languageId: 1);

        self::assertSame('de', $metadata->getLanguage());
        self::assertSame(1, $metadata->getLanguageId());
        self::assertSame('de', $metadata->getValue('language'));
        self::assertSame(1, $metadata->getValue('language_id'));

        $serialized = $metadata->toArray();
        self::assertSame('de', $serialized['language']);
        self::assertSame(1, $serialized['language_id']);

        $restored = DocumentMetadata::fromArray($serialized);
        self::assertSame('de', $restored->getLanguage());
        self::assertSame(1, $restored->getLanguageId());
    }
}
