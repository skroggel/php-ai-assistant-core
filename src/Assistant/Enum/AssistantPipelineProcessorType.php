<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Enum;

/**
 * Enum AssistantPipelineProcessorType
 *
 * Identifies the semantic role of a processor within an assistant pipeline.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
enum AssistantPipelineProcessorType: string
{
    case QueryOptimizer = 'query_optimizer';
    case Retriever = 'retriever';
    case ContextOptimizer = 'context_optimizer';
    case AnswerGenerator = 'answer_generator';
    case QualityGate = 'quality_gate';
    case Memory = 'memory';

    /**
     * Returns a human-readable processor type label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::QueryOptimizer => 'Query optimizer',
            self::Retriever => 'Retriever',
            self::ContextOptimizer => 'Context optimizer',
            self::AnswerGenerator => 'Answer generator',
            self::QualityGate => 'Quality gate',
            self::Memory => 'Memory',
        };
    }
}
