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

namespace Madj2k\AiCore\DTO;

/**
 * Class DocumentMetadata
 *
 * Contains structured and additional source metadata for indexable and retrieved content.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
class DocumentMetadata
{
    /**
     * Source type.
     *
     * @var string
     */
    protected string $sourceType = '';


    /**
     * Source identifier.
     *
     * @var string
     */
    protected string $sourceIdentifier = '';


    /**
     * Source title.
     *
     * @var string
     */
    protected string $title = '';


    /**
     * Source URL.
     *
     * @var string
     */
    protected string $url = '';


    /**
     * Language uid.
     *
     * @var int
     */
    protected int $language = 0;


    /**
     * Page uid.
     *
     * @var int
     */
    protected int $pageId = 0;


    /**
     * File path.
     *
     * @var string
     */
    protected string $path = '';


    /**
     * File name.
     *
     * @var string
     */
    protected string $filename = '';


    /**
     * Last changed timestamp.
     *
     * @var int
     */
    protected int $changedAt = 0;


    /**
     * Additional non-standard metadata.
     *
     * @var array<string, mixed>
     */
    protected array $additional = [];


    /**
     * Constructor.
     *
     * @param string $sourceType Source type.
     * @param string $sourceIdentifier Source identifier.
     * @param string $title Source title.
     * @param string $url Source URL.
     * @param int $language Language uid.
     * @param int $pageId Page uid.
     * @param string $path File path.
     * @param string $filename File name.
     * @param int $changedAt Last changed timestamp.
     * @param array<string, mixed> $additional Additional non-standard metadata.
     */
    public function __construct(
        string $sourceType = '',
        string $sourceIdentifier = '',
        string $title = '',
        string $url = '',
        int $language = 0,
        int $pageId = 0,
        string $path = '',
        string $filename = '',
        int $changedAt = 0,
        array $additional = []
    ) {
        $this->sourceType = trim($sourceType);
        $this->sourceIdentifier = trim($sourceIdentifier);
        $this->title = trim($title);
        $this->url = trim($url);
        $this->language = $language;
        $this->pageId = $pageId;
        $this->path = trim($path);
        $this->filename = trim($filename);
        $this->changedAt = $changedAt;
        $this->additional = $additional;
    }


    /**
     * Returns the source type.
     *
     * @return string Source type.
     */
    public function getSourceType(): string
    {
        return $this->sourceType;
    }


    /**
     * Sets the source type.
     *
     * @param string $sourceType Source type.
     * @return void
     */
    public function setSourceType(string $sourceType): void
    {
        $this->sourceType = trim($sourceType);
    }


    /**
     * Returns the source identifier.
     *
     * @return string Source identifier.
     */
    public function getSourceIdentifier(): string
    {
        return $this->sourceIdentifier;
    }


    /**
     * Sets the source identifier.
     *
     * @param string $sourceIdentifier Source identifier.
     * @return void
     */
    public function setSourceIdentifier(string $sourceIdentifier): void
    {
        $this->sourceIdentifier = trim($sourceIdentifier);
    }


    /**
     * Returns the source title.
     *
     * @return string Source title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets the source title.
     *
     * @param string $title Source title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }


    /**
     * Returns the source URL.
     *
     * @return string Source URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * Sets the source URL.
     *
     * @param string $url Source URL.
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = trim($url);
    }


    /**
     * Returns the language uid.
     *
     * @return int Language uid.
     */
    public function getLanguage(): int
    {
        return $this->language;
    }


    /**
     * Sets the language uid.
     *
     * @param int $language Language uid.
     * @return void
     */
    public function setLanguage(int $language): void
    {
        $this->language = $language;
    }


    /**
     * Returns the page uid.
     *
     * @return int Page uid.
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }


    /**
     * Sets the page uid.
     *
     * @param int $pageId Page uid.
     * @return void
     */
    public function setPageId(int $pageId): void
    {
        $this->pageId = $pageId;
    }


    /**
     * Returns the file path.
     *
     * @return string File path.
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Sets the file path.
     *
     * @param string $path File path.
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = trim($path);
    }


    /**
     * Returns the file name.
     *
     * @return string File name.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }


    /**
     * Sets the file name.
     *
     * @param string $filename File name.
     * @return void
     */
    public function setFilename(string $filename): void
    {
        $this->filename = trim($filename);
    }


    /**
     * Returns the last changed timestamp.
     *
     * @return int Last changed timestamp.
     */
    public function getChangedAt(): int
    {
        return $this->changedAt;
    }


    /**
     * Sets the last changed timestamp.
     *
     * @param int $changedAt Last changed timestamp.
     * @return void
     */
    public function setChangedAt(int $changedAt): void
    {
        $this->changedAt = $changedAt;
    }


    /**
     * Returns additional metadata.
     *
     * @return array<string, mixed> Additional metadata.
     */
    public function getAdditional(): array
    {
        return $this->additional;
    }


    /**
     * Sets additional metadata.
     *
     * @param array<string, mixed> $additional Additional metadata.
     * @return void
     */
    public function setAdditional(array $additional): void
    {
        $this->additional = $additional;
    }


    /**
     * Adds one additional metadata value.
     *
     * @param string $key Metadata key.
     * @param mixed $value Metadata value.
     * @return void
     */
    public function addAdditional(string $key, mixed $value): void
    {
        $key = trim($key);
        if ($key === '') {
            return;
        }

        $this->additional[$key] = $value;
    }


    /**
     * Returns one normalized metadata value.
     *
     * @param string $field Metadata field name.
     * @return mixed Metadata value.
     */
    public function getValue(string $field): mixed
    {
        return match ($field) {
            'source_type', 'sourceType' => $this->sourceType,
            'source_identifier', 'source_id', 'sourceIdentifier' => $this->sourceIdentifier,
            'title' => $this->title,
            'url' => $this->url,
            'language' => $this->language,
            'page_id', 'pageId' => $this->pageId,
            'path' => $this->path,
            'filename' => $this->filename,
            'changed_at', 'changedAt' => $this->changedAt,
            'additional' => $this->additional,
            default => $this->additional[$field] ?? null,
        };
    }


    /**
     * Returns selected metadata fields.
     *
     * @param array<int,string> $fields Field names.
     * @return array<string,mixed> Selected metadata.
     */
    public function toSelectedArray(array $fields): array
    {
        $selected = [];

        foreach ($fields as $field) {
            $value = $this->getValue($field);
            if ($value === null || $value === '') {
                continue;
            }

            $selected[$field] = $value;
        }

        return $selected;
    }


    /**
     * Exports the metadata as array.
     *
     * The legacy key source_id is kept as alias for existing vector payloads.
     *
     * @return array<string, mixed> Metadata array.
     */
    public function toArray(): array
    {
        return [
            'source_type' => $this->sourceType,
            'source_identifier' => $this->sourceIdentifier,
            'source_id' => $this->sourceIdentifier,
            'title' => $this->title,
            'url' => $this->url,
            'language' => $this->language,
            'page_id' => $this->pageId,
            'path' => $this->path,
            'filename' => $this->filename,
            'changed_at' => $this->changedAt,
            'additional' => $this->additional,
        ];
    }


    /**
     * Creates metadata from an array representation.
     *
     * @param array<string,mixed> $data Metadata array.
     * @return self Metadata.
     */
    public static function fromArray(array $data): self
    {
        /** @var mixed $additional */
        $additional = $data['additional'] ?? [];
        $additional = is_array($additional) ? $additional : [];

        foreach ($data as $key => $value) {
            if (in_array($key, [
                'source_type',
                'source_identifier',
                'source_id',
                'title',
                'url',
                'language',
                'page_id',
                'path',
                'filename',
                'changed_at',
                'additional',
            ], true)) {
                continue;
            }

            $additional[(string)$key] = $value;
        }

        return new self(
            (string)($data['source_type'] ?? ''),
            (string)($data['source_identifier'] ?? $data['source_id'] ?? ''),
            (string)($data['title'] ?? ''),
            (string)($data['url'] ?? ''),
            (int)($data['language'] ?? 0),
            (int)($data['page_id'] ?? 0),
            (string)($data['path'] ?? ''),
            (string)($data['filename'] ?? ''),
            (int)($data['changed_at'] ?? 0),
            $additional
        );
    }
}
