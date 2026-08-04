<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Assistant\Enum;

enum AssistantPipelineProcessorType: string
{
    case QueryOptimizer = 'query_optimizer';
    case Retriever = 'retriever';
    case ContextOptimizer = 'context_optimizer';
    case AnswerGenerator = 'answer_generator';
    case QualityGate = 'quality_gate';
    case Memory = 'memory';

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
