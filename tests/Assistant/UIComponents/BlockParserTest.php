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

namespace Madj2k\AiCore\Tests\Assistant\UIComponents;

use Madj2k\AiCore\Assistant\UIComponents\BlockParser;
use PHPUnit\Framework\TestCase;

/**
 * Class BlockParserTest
 *
 * Tests the framework-independent UI response block syntax.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore\Tests
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class BlockParserTest extends TestCase
{
    /**
     * Tests Markdown and a JSON UI block are kept as separate blocks.
     *
     * @return void
     */
    public function testParsesMarkdownAndUiBlock(): void
    {
        $blocks = (new BlockParser())->parse("Intro\n\n:::ui select-list\n{\"id\":\"choices\",\"options\":[{\"value\":\"a\"}]}\n:::\n\nOutro");

        self::assertCount(3, $blocks);
        self::assertSame('markdown', $blocks[0]->type);
        self::assertSame('component', $blocks[1]->type);
        self::assertSame('select-list', $blocks[1]->identifier);
        self::assertSame('choices', $blocks[1]->id);
        self::assertSame('a', $blocks[1]->data['options'][0]['value']);
        self::assertSame('markdown', $blocks[2]->type);
    }

    /**
     * Tests malformed component JSON cannot create a component block.
     *
     * @return void
     */
    public function testKeepsMalformedBlockAsMarkdown(): void
    {
        $blocks = (new BlockParser())->parse(":::ui button\nnot-json\n:::\n");

        self::assertCount(1, $blocks);
        self::assertSame('markdown', $blocks[0]->type);
    }
}
