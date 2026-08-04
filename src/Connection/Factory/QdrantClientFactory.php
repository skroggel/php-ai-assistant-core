<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client as HttpClient;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Qdrant;

/**
 * Class QdrantClientFactory
 *
 * Creates native Qdrant SDK clients backed by a timeout-aware Guzzle client.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class QdrantClientFactory implements QdrantClientFactoryInterface
{
    /** @inheritDoc */
    public function create(
        VectorStoreConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): Qdrant {
        $config = new Config($connection->getEndpoint());
        if ($connection->getApiKey() !== '') {
            $config->setApiKey($connection->getApiKey());
        }

        $httpClient = new HttpClient([
            'timeout' => $policy->getTimeoutSeconds(),
            'connect_timeout' => $policy->getConnectTimeoutSeconds(),
        ]);

        return new Qdrant((new Builder($httpClient))->build($config));
    }
}
