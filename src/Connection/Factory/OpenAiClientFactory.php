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

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client as HttpClient;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Authentication\OAuth2ClientCredentialsTokenProvider;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use OpenAI\Contracts\ClientContract;

/**
 * Class OpenAiClientFactory
 *
 * Creates native OpenAI SDK clients backed by a timeout-aware Guzzle client.
 *
 * @internal Use OpenAiClientFactoryInterface to provide custom client creation.
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class OpenAiClientFactory implements OpenAiClientFactoryInterface
{
    /** @inheritDoc */
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientContract {
        $factory = \OpenAI::factory()
            ->withApiKey((new OAuth2ClientCredentialsTokenProvider())->resolve($connection))
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
