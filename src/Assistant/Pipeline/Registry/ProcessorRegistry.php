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


namespace Madj2k\AiCore\Assistant\Pipeline\Registry;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Exception\AssistantException;

/**
 * Class ProcessorRegistry
 *
 * Resolves configured step types to executable processors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final readonly class ProcessorRegistry
{

    /**
     * Constructor - list of processors is loaded via Services.yaml
     *
     * @param iterable<\Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface> $processors Processors.
     */
    public function __construct(
        private iterable $processors,
    ) {
    }


    /**
     * Look up processor by identifier
     *
     * @param string $identifier
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type
     * @return \Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface
     * @throws \Madj2k\AiCore\Exception\AssistantException
     */
    public function get(string $identifier, AssistantPipelineProcessorType $type): ProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->getIdentifier() === $identifier) {

                // check if processor supports given type!
                if (!$processor->supports($type)) {
                    throw new AssistantException(
                        sprintf(
                            'Processor "%s" does not support step type "%s".',
                            $identifier,
                            $type->value
                        ),
                        1780572973
                    );
                }

                return $processor;
            }
        }

        throw new AssistantException(
            sprintf('No chat pipeline processor registered for identifier "%s".', $identifier),
            1780572973
        );
    }


    /**
     * Returns all registered processors.
     *
     * @return array<int, \Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface> Processors.
     */
    public function all(): array
    {
        return is_array($this->processors)
            ? $this->processors
            : iterator_to_array($this->processors, false);
    }

}
