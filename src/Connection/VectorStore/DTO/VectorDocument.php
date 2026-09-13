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

namespace Madj2k\AiCore\Connection\VectorStore\DTO;

/**
 * Class VectorDocument
 *
 * Contains one vector store document.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class VectorDocument
{
    /**
     * Document identifier.
     *
     * @var string
     */
    protected string $id = '';


    /**
     * Vector values.
     *
     * @var array<int, float>
     */
    protected array $vector = [];


    /**
     * Payload.
     *
     * @var array<string, mixed>
     */
    protected array $payload = [];


    /**
     * Vector name.
     *
     * @var string
     */
    protected string $vectorName = '';


    /**
     * Constructor.
     *
     * @param string $id Document identifier.
     * @param array<int, float> $vector Vector values.
     * @param array<string, mixed> $payload Payload.
     * @param string $vectorName Vector name.
     */
    public function __construct(string $id = '', array $vector = [], array $payload = [], string $vectorName = '')
    {
        $this->id = $id;
        $this->vector = $vector;
        $this->payload = $payload;
        $this->vectorName = $vectorName;
    }


    /**
     * Returns the document identifier.
     *
     * @return string Document identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Sets the document identifier.
     *
     * @param string $id Document identifier.
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = trim($id);
    }


    /**
     * Returns the vector values.
     *
     * @return array<int, float> Vector values.
     */
    public function getVector(): array
    {
        return $this->vector;
    }


    /**
     * Sets the vector values.
     *
     * @param array<int, float> $vector Vector values.
     * @return void
     */
    public function setVector(array $vector): void
    {
        $this->vector = $vector;
    }


    /**
     * Returns the payload.
     *
     * @return array<string, mixed> Payload.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }


    /**
     * Sets the payload.
     *
     * @param array<string, mixed> $payload Payload.
     * @return void
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }


    /**
     * Returns the vector name.
     *
     * @return string Vector name.
     */
    public function getVectorName(): string
    {
        return $this->vectorName;
    }


    /**
     * Sets the vector name.
     *
     * @param string $vectorName Vector name.
     * @return void
     */
    public function setVectorName(string $vectorName): void
    {
        $this->vectorName = trim($vectorName);
    }
}
