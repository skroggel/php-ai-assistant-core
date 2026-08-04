<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client as HttpClient;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use OpenAI\Contracts\ClientContract;

final class OpenAiClientFactory implements OpenAiClientFactoryInterface
{
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientContract {
        $factory = \OpenAI::factory()
            ->withApiKey($connection->getApiKey())
            ->withHttpClient(new HttpClient([
                'timeout' => $policy->getTimeoutSeconds(),
                'connect_timeout' => $policy->getConnectTimeoutSeconds(),
            ]));

        if ($connection->getBaseUrl() !== '') {
            $factory = $factory->withBaseUri($connection->getBaseUrl());
        }
        if ($connection->getOrganization() !== '') {
            $factory = $factory->withOrganization($connection->getOrganization());
        }
        if ($connection->getProject() !== '') {
            $factory = $factory->withProject($connection->getProject());
        }

        return $factory->make();
    }
}
