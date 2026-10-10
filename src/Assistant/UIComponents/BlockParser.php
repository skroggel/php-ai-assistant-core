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

namespace Madj2k\AiCore\Assistant\UIComponents;

/**
 * Class BlockParser
 *
 * Parses the framework-independent UI block syntax embedded in an answer.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class BlockParser
{
    /**
     * Parses complete UI blocks and keeps all other content as Markdown.
     *
     * The parser deliberately ignores malformed JSON blocks. They remain
     * Markdown text and can therefore not create a frontend component.
     *
     * @param string $content Assistant response content.
     * @return array<int,Block> Parsed blocks.
     */
    public function parse(string $content): array
    {
        $pattern = '/^:::ui[ \t]+([A-Za-z0-9._-]+)[ \t]*\R(.*?)^:::[ \t]*(?:\R|$)/ms';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        $blocks = [];
        $usedIds = [];
        $offset = 0;
        foreach ($matches[0] ?? [] as $index => $match) {
            $start = (int)$match[1];
            $raw = (string)$match[0];
            if ($start > $offset) {
                $blocks[] = new Block('markdown', substr($content, $offset, $start - $offset));
            }

            $identifier = (string)($matches[1][$index][0] ?? '');
            $payload = trim((string)($matches[2][$index][0] ?? ''));
            $data = json_decode($payload, true);
            if (!is_array($data)) {
                $blocks[] = new Block('markdown', $raw);
                $offset = $start + strlen($raw);
                continue;
            }

            $id = trim((string)($data['id'] ?? ''));
            if ($id === '') {
                $id = substr(hash('sha256', $identifier . ':' . $start), 0, 16);
            }
            $baseId = $id;
            $suffix = 2;
            while (isset($usedIds[$id])) {
                $id = $baseId . '-' . $suffix++;
            }
            $usedIds[$id] = true;
            unset($data['id']);

            $blocks[] = new Block('component', '', $identifier, $id, $data);
            $offset = $start + strlen($raw);
        }

        if ($offset < strlen($content)) {
            $blocks[] = new Block('markdown', substr($content, $offset));
        }

        return $blocks === [] ? [new Block('markdown', $content)] : $blocks;
    }
}
