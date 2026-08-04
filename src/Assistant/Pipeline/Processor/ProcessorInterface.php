<?php
declare(strict_types=1);

/*
 * This file is part of the AI Chat extension.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */


namespace Madj2k\AiCore\Assistant\Pipeline\Processor;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;


/**
 * Interface ChatPipelineStepProcessorInterface
 *
 * Contract for one typed chat pipeline processor.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ProcessorInterface
{

    /**
     * Tells which processor is to load
     * @return string
     */
    public function getIdentifier(): string;


    /**
     * Tells whether this processor supports a step type.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Step type.
     * @return bool
     */
    public function supports(AssistantPipelineProcessorType $type): bool;


    /**
     * Tells whether the current context contains required input slots.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step.
     * @return bool
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool;


    /**
     * Processes the step and writes its result to the context.
     *
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Step.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return void
     * @throws \Madj2k\AiCore\Exception\ApiException
     */
    public function process(
        Context                  $context,
        PipelineStepConfigurationInterface    $step,
        ?PipelineLogMetaData $logContext = null
    ): void;
}
