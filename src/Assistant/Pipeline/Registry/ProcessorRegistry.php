<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Registry;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Exception\AssistantException;

/**
 * Class ChatPipelineStepProcessorRegistry
 *
 * Resolves configured step types to executable processors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
