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
 * Class VectorCollection
 *
 * Contains vector collection configuration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class VectorCollection
{
    /**
     * Collection name.
     *
     * @var string
     */
    protected string $name = '';


    /**
     * Vector size.
     *
     * @var int
     */
    protected int $vectorSize = 1536;


    /**
     * Distance strategy.
     *
     * @var string
     */
    protected string $distance = 'Cosine';


    /**
     * Constructor.
     *
     * @param string $name Collection name.
     * @param int $vectorSize Vector size.
     * @param string $distance Distance strategy.
     */
    public function __construct(string $name = '', int $vectorSize = 1536, string $distance = 'Cosine')
    {
        $this->name = $name;
        $this->vectorSize = $vectorSize;
        $this->distance = $distance;
    }


    /**
     * Returns the collection name.
     *
     * @return string Collection name.
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Sets the collection name.
     *
     * @param string $name Collection name.
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = trim($name);
    }


    /**
     * Returns the vector size.
     *
     * @return int Vector size.
     */
    public function getVectorSize(): int
    {
        return $this->vectorSize;
    }


    /**
     * Sets the vector size.
     *
     * @param int $vectorSize Vector size.
     * @return void
     */
    public function setVectorSize(int $vectorSize): void
    {
        $this->vectorSize = $vectorSize;
    }


    /**
     * Returns the distance strategy.
     *
     * @return string Distance strategy.
     */
    public function getDistance(): string
    {
        return $this->distance;
    }


    /**
     * Sets the distance strategy.
     *
     * @param string $distance Distance strategy.
     * @return void
     */
    public function setDistance(string $distance): void
    {
        $this->distance = trim($distance);
    }
}
