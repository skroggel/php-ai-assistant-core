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

namespace Madj2k\AiCore\Connection\VectorStore\Filter\DTO;

/**
 * Class FilterGroup
 *
 * Groups vector payload conditions with a logical conjunction.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class FilterGroup
{
    /**
     * Constructor.
     *
     * @param FilterConjunction $conjunction Condition conjunction.
     * @param array<int,FilterCondition> $conditions Filter conditions.
     */
    public function __construct(
        public FilterConjunction $conjunction = FilterConjunction::And,
        public array $conditions = [],
    ) {
    }

    /**
     * Returns whether the group contains usable conditions.
     *
     * @return bool Usable condition flag.
     */
    public function isEmpty(): bool
    {
        return $this->conditions === [];
    }
}
