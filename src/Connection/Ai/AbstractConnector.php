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

namespace Madj2k\AiCore\Connection\Ai;

use Madj2k\AiCore\Connection\Resilience\ExceptionClassifier;
use Madj2k\AiCore\Connection\Resilience\RetryExecutor;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractConnector
 *
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
abstract class AbstractConnector implements AiConnectorInterface
{

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * Runtime client cache.
     *
     * @var array<string, \GuzzleHttp\ClientInterface|\OpenAI\Contracts\ClientContract>
     */
    protected array $clients = [];


    /**
     * @var \Madj2k\AiCore\Connection\Resilience\RetryPolicy $retryPolicy
     */
    protected RetryPolicy $retryPolicy;


    /**
     * @var \Madj2k\AiCore\Connection\Resilience\RetryExecutor $retryExecutor
     */
    protected RetryExecutor $retryExecutor;


    /**
     * @var \Madj2k\AiCore\Connection\Resilience\ExceptionClassifier $exceptionClassifier
     */
    protected ExceptionClassifier $exceptionClassifier;

}
