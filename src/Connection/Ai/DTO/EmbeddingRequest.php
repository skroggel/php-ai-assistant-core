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

namespace Madj2k\AiCore\Connection\Ai\DTO;

use Madj2k\AiCore\Connection\Ai\Enum\EmbeddingPurpose;

/**
 * Class EmbeddingRequest
 *
 * Contains a normalized embedding request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class EmbeddingRequest
{
    /**
     * Text to embed.
     *
     * @var string
     */
    protected string $text = '';


    /**
     * Model identifier.
     *
     * @var string
     */
    protected string $model = '';


    /**
     * Temperature.
     *
     * @var float|null
     */
    protected ?float $temperature = null;


    /**
     * Additional provider options.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];


    /**
     * Embedding purpose. Unspecified preserves the previous embedding behavior.
     *
     * @var \Madj2k\AiCore\Connection\Ai\Enum\EmbeddingPurpose
     */
    protected EmbeddingPurpose $purpose = EmbeddingPurpose::Unspecified;


    /**
     * Constructor.
     *
     * @param string $text Text to embed.
     * @param string $model Model identifier.
     * @param float|null $temperature Temperature.
     * @param array<string, mixed> $options Additional provider options.
     * @param \Madj2k\AiCore\Connection\Ai\Enum\EmbeddingPurpose $purpose Provider-neutral embedding purpose.
     */
    public function __construct(
        string $text = '',
        string $model = '',
        ?float $temperature = null,
        array $options = [],
        EmbeddingPurpose $purpose = EmbeddingPurpose::Unspecified,
    ) {
        $this->text = $text;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->options = $options;
        $this->purpose = $purpose;
    }


    /**
     * Returns the text to embed.
     *
     * @return string Text to embed.
     */
    public function getText(): string
    {
        return $this->text;
    }


    /**
     * Sets the text to embed.
     *
     * @param string $text Text to embed.
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }


    /**
     * Returns the model identifier.
     *
     * @return string Model identifier.
     */
    public function getModel(): string
    {
        return $this->model;
    }


    /**
     * Sets the model identifier.
     *
     * @param string $model Model identifier.
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = trim($model);
    }


    /**
     * Returns the temperature.
     *
     * @return float|null Temperature.
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }


    /**
     * Sets the temperature.
     *
     * @param float|null $temperature Temperature.
     * @return void
     */
    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }


    /**
     * Returns additional provider options.
     *
     * @return array<string, mixed> Additional provider options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * Sets additional provider options.
     *
     * @param array<string, mixed> $options Additional provider options.
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }


    /**
     * Returns the provider-neutral embedding purpose.
     *
     * @return \Madj2k\AiCore\Connection\Ai\Enum\EmbeddingPurpose Embedding purpose.
     */
    public function getPurpose(): EmbeddingPurpose
    {
        return $this->purpose;
    }


    /**
     * Sets the provider-neutral embedding purpose.
     *
     * @param \Madj2k\AiCore\Connection\Ai\Enum\EmbeddingPurpose $purpose Embedding purpose.
     * @return void
     */
    public function setPurpose(EmbeddingPurpose $purpose): void
    {
        $this->purpose = $purpose;
    }
}
