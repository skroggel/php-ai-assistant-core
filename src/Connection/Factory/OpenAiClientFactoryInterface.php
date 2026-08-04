<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use OpenAI\Contracts\ClientContract;

interface OpenAiClientFactoryInterface
{
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientContract;
}
