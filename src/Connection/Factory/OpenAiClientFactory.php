<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client as HttpClient;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use OpenAI\Contracts\ClientContract;

/**
 * Class OpenAiClientFactory
 *
 * Creates native OpenAI SDK clients backed by a timeout-aware Guzzle client.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class OpenAiClientFactory implements OpenAiClientFactoryInterface
{
    /** @inheritDoc */
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
