<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai\DTO;

/**
 * Class EmbeddingRequest
 *
 * Contains a normalized embedding request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
     * Constructor.
     *
     * @param string $text Text to embed.
     * @param string $model Model identifier.
     * @param float|null $temperature Temperature.
     * @param array<string, mixed> $options Additional provider options.
     */
    public function __construct(string $text = '', string $model = '', ?float $temperature = null, array $options = [])
    {
        $this->text = $text;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->options = $options;
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
}
