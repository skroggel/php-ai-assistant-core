<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */



namespace Madj2k\AiCore\Assistant\Context\Trace;

/**
 * Class ProcessingTrace
 *
 * Collects deterministic trace information for a pipeline run.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class ProcessingTrace
{
    /**
     * @var array<int,array<string,mixed>>
     */
    private array $entries = [];


    /**
     * Adds a trace entry.
     *
     * @param string $event Event name.
     * @param int|null $stepId Step identifier, or null for an unpersisted step.
     * @param mixed $input Input value.
     * @param mixed $output Output value.
     * @param array<string, mixed> $payload Event payload.
     * @return void
     */
    public function add(string $event, ?int $stepId, mixed $input, mixed $output, array $payload = []): void
    {
        $this->entries[] = [
            'event' => $event,
            'step_id' => (int)$stepId,
            'input' => $this->stringifyTraceValue($input),
            'output' => $this->stringifyTraceValue($output),
            'payload' => $payload,
        ];
    }

    /**
     * Converts a trace value into a readable string representation.
     *
     * @param mixed $value Value to convert.
     * @return string String representation.
     */
    private function stringifyTraceValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        if ($value instanceof \Stringable) {
            return (string)$value;
        }

        /** @var string|false $json */
        $json = json_encode(
            $this->normalizeTraceValue($value),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($json !== false) {
            return $json;
        }

        return sprintf('[unserializable:%s]', get_debug_type($value));
    }


    /**
     * Normalizes a trace value before JSON encoding.
     *
     * @param mixed $value Value to normalize.
     * @return mixed Normalized value.
     */
    private function normalizeTraceValue(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if ($value instanceof \Stringable) {
            return (string)$value;
        }

        if (is_array($value)) {
            /** @var array<string|int, mixed> $normalized */
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeTraceValue($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return [
                '_type' => $value::class,
            ];
        }

        return sprintf('[unsupported:%s]', get_debug_type($value));
    }


    /**
     * Returns all trace entries.
     *
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return $this->entries;
    }
}
