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

namespace Madj2k\AiCore\Assistant\DTO;

/**
 * Class ChatOptions
 *
 * Immutable and normalized user-facing options for one assistant turn.
 * The DTO deliberately stays flat because it transports runtime values rather
 * than mirroring the configuration structure of a host application.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class ChatOptions
{
    /**
     * Language name or BCP 47 tag requested for assistant responses.
     *
     * @var string
     */
    public string $responseLanguage;


    /**
     * Normalized BCP 47 language tag when one can be determined safely.
     *
     * @var string
     */
    public string $languageCode;


    /**
     * Whether answer-producing steps should use plain language.
     *
     * @var bool
     */
    public bool $plainLanguage;


    /**
     * Constructor.
     *
     * @param string $responseLanguage Language name or BCP 47 tag requested for responses.
     * @param string $languageCode BCP 47 language tag when available.
     * @param bool $plainLanguage Whether plain language is requested.
     */
    public function __construct(
        string $responseLanguage = '',
        string $languageCode = '',
        bool $plainLanguage = false,
    ) {
        $this->responseLanguage = self::normalizeLanguage($responseLanguage);
        $this->languageCode = self::normalizeLanguageCode($languageCode);
        $this->plainLanguage = $plainLanguage;
    }


    /**
     * Normalizes and validates a human-readable response language.
     *
     * @param string $language Language name or language tag.
     * @return string Normalized language or an empty string for invalid input.
     */
    private static function normalizeLanguage(string $language): string
    {
        $language = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $language));
        $language = (string)preg_replace('/\s+/u', ' ', $language);

        if (
            $language === ''
            || mb_strlen($language) > 80
            || preg_match('/^[\p{L}\p{M}\p{N}\s._()\'’\-]+$/u', $language) !== 1
        ) {
            return '';
        }

        return $language;
    }


    /**
     * Normalizes and validates a BCP 47 language tag.
     *
     * @param string $languageCode Language tag.
     * @return string Normalized language tag or an empty string for invalid input.
     */
    private static function normalizeLanguageCode(string $languageCode): string
    {
        $languageCode = str_replace('_', '-', trim($languageCode));

        return preg_match('/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/', $languageCode) === 1
            ? $languageCode
            : '';
    }
}
