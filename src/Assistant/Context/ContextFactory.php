<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */


namespace Madj2k\AiCore\Assistant\Context;

use Madj2k\AiCore\Assistant\DTO\AssistantRequest;
use Madj2k\AiCore\Assistant\Context\Assistant\AssistantContextFactory;


/**
 * Class ContextFactory
 *
 * Creates the state object for one pipeline run.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class ContextFactory
{

    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Assistant\AssistantContextFactory $assistantContextFactory Assistant context factory.
     */
    public function __construct(
        private AssistantContextFactory $assistantContextFactory,
    ) {
    }


    /**
     * Creates the context.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\AssistantRequest $chatTurnRequest
     * @param array<int,array<string,string>> $history Visible history messages.
     * @return \Madj2k\AiCore\Assistant\Context\Context
     */
    public function create(
        AssistantRequest $chatTurnRequest,
        array            $history
    ): Context {
        return new Context(
            $this->assistantContextFactory->create($chatTurnRequest->assistantProfile),
            new Request\Request(
                $chatTurnRequest->query,
                $chatTurnRequest->chatIdentifier,
                $chatTurnRequest->serverRequest,
                $chatTurnRequest->runtimeSettings
            ),
            new Request\History($history),
            new Retrieval\RetrievalResult(),
            new Answer\AnswerState(),
            new Trace\ProcessingTrace()
        );
    }
}
