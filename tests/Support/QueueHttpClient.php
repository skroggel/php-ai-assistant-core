<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class QueueHttpClient implements ClientInterface
{
    /** @var array<int, ResponseInterface|\Throwable> */
    private array $queue;

    /** @var array<int, RequestInterface> */
    public array $requests = [];

    /** @param array<int, ResponseInterface|\Throwable> $queue */
    public function __construct(array $queue)
    {
        $this->queue = array_values($queue);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $response = array_shift($this->queue);
        if ($response instanceof \Throwable) {
            throw $response;
        }
        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('No queued HTTP response available.');
        }

        return $response;
    }
}
