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
 * Class AiRequest
 *
 * Contains a normalized AI chat request.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AiRequest
{
    /**
     * AI messages.
     *
     * @var array<int, \Madj2k\AiCore\Connection\Ai\DTO\AiMessage>
     */
    protected array $messages = [];


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
     * Maximum tokens.
     *
     * @var int
     */
    protected int $maxTokens = 1000;


    /**
     * Additional provider options.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];


    /**
     * Constructor.
     *
     * @param array<int, \Madj2k\AiCore\Connection\Ai\DTO\AiMessage> $messages AI messages.
     * @param string $model Model identifier.
     * @param float|null $temperature Temperature.
     * @param int $maxTokens Maximum tokens.
     * @param array<string, mixed> $options Additional provider options.
     */
    public function __construct(
        array $messages = [],
        string $model = '',
        ?float $temperature = null,
        int $maxTokens = 1000,
        array $options = []
    ) {
        $this->setMessages($messages);
        $this->model = $model;
        $this->temperature = $temperature;
        $this->maxTokens = $maxTokens;
        $this->options = $options;
    }


    /**
     * Returns the AI messages.
     *
     * @return array<int, \Madj2k\AiCore\Connection\Ai\DTO\AiMessage> AI messages.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }


    /**
     * Sets the AI messages.
     *
     * @param array<int, \Madj2k\AiCore\Connection\Ai\DTO\AiMessage> $messages AI messages.
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->messages = [];

        foreach ($messages as $message) {
            if ($message instanceof AiMessage) {
                $this->messages[] = $message;
            }
        }
    }


    /**
     * Adds one AI message.
     *
     * @param \Madj2k\AiCore\Connection\Ai\DTO\AiMessage $message AI message.
     * @return void
     */
    public function addMessage(AiMessage $message): void
    {
        $this->messages[] = $message;
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
     * Returns the maximum tokens.
     *
     * @return int Maximum tokens.
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }


    /**
     * Sets the maximum tokens.
     *
     * @param int $maxTokens Maximum tokens.
     * @return void
     */
    public function setMaxTokens(int $maxTokens): void
    {
        $this->maxTokens = $maxTokens;
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
     * Converts messages into API-compatible arrays.
     *
     * @return array<int, array{role: string, content: string}> API-compatible messages.
     */
    public function toMessageArray(): array
    {
        return array_map(
            static fn (AiMessage $message): array => $message->toApiArray(),
            $this->messages
        );
    }


    /**
     * Converts messages into trace-compatible arrays.
     *
     * @return array<int, array<string, mixed>> Trace-compatible messages.
     */
    public function toTraceMessageArray(): array
    {
        return array_map(
            static fn (AiMessage $message): array => $message->toTraceArray(),
            $this->messages
        );
    }
}
