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

namespace Madj2k\AiCore\Connection\Authentication;

use GuzzleHttp\Client;
use Madj2k\AiCore\Exception\ApiException;

/**
 * Class OAuth2ClientCredentialsTokenProvider
 *
 * Resolves and caches OAuth 2.0 client-credentials tokens for AI connections.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
final class OAuth2ClientCredentialsTokenProvider
{
    /** @var array<string, array{token: string, expiresAt: int}> */
    private static array $tokens = [];


    /**
     * Returns an OAuth access token when the configuration selects OAuth.
     *
     * @param object $connection AI connection configuration.
     * @return string Access token or API key.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Madj2k\AiCore\Exception\ApiException
     */
    public function resolve(object $connection): string
    {
        if (!method_exists($connection, 'getAuthentication') || $connection->getAuthentication() !== 'oauth_client_credentials') {
            return method_exists($connection, 'getApiKey') ? (string)$connection->getApiKey() : '';
        }

        $endpoint = (string)$connection->getOauthTokenEndpoint();
        $clientId = (string)$connection->getOauthClientId();
        $clientSecret = (string)$connection->getOauthClientSecret();
        $scope = method_exists($connection, 'getOauthScope') ? (string)$connection->getOauthScope() : '';
        $key = sha1(implode('|', [$endpoint, $clientId, $scope]));
        if (isset(self::$tokens[$key]) && time() < self::$tokens[$key]['expiresAt']) {
            return self::$tokens[$key]['token'];
        }

        if ($endpoint === '' || $clientId === '' || $clientSecret === '') {
            throw new ApiException('OAuth token endpoint, client ID and client secret are required.', 1789004001);
        }

        $form = ['grant_type' => 'client_credentials'];
        if ($scope !== '') {
            $form['scope'] = $scope;
        }
        $response = (new Client())->request('POST', $endpoint, [
            'auth' => [$clientId, $clientSecret],
            'form_params' => $form,
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => false,
        ]);
        $payload = json_decode((string)$response->getBody(), true);
        $token = is_array($payload) ? trim((string)($payload['access_token'] ?? '')) : '';
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || $token === '') {
            throw new ApiException('OAuth token endpoint returned an invalid response.', 1789004002);
        }

        self::$tokens[$key] = [
            'token' => $token,
            'expiresAt' => time() + max(1, (int)($payload['expires_in'] ?? 300) - 60),
        ];

        return $token;
    }
}
