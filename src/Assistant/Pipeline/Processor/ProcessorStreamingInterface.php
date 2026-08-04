<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;

/**
 * Interface ProcessorStreamingInterface
 *
 * Contract for processors that can stream their output.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface ProcessorStreamingInterface
{
    /**
     * Processes the step and streams output chunks to the callback.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step.
     * @param callable $onData Callback for streamed output chunks.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return void
     * @throws \Madj2k\AiCore\Exception\ApiException
     */
    public function processStream(
        Context $context,
        PipelineStepConfigurationInterface $step,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): void;
}
