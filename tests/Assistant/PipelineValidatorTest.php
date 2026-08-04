<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Assistant;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Pipeline\PipelineValidator;
use Madj2k\AiCore\Tests\Support\PipelineStep;
use PHPUnit\Framework\TestCase;

final class PipelineValidatorTest extends TestCase
{
    public function testRequiresAnAnswerProducingStep(): void
    {
        $messages = (new PipelineValidator())->validate([
            new PipelineStep(type: AssistantPipelineProcessorType::Retriever),
        ]);

        self::assertSame(
            ['The pipeline needs at least one answer generator or memory step.'],
            $messages,
        );
    }

    public function testAcceptsAnswerGeneratorAndMemorySteps(): void
    {
        $validator = new PipelineValidator();

        self::assertSame([], $validator->validate([
            new PipelineStep(type: AssistantPipelineProcessorType::AnswerGenerator),
        ]));
        self::assertSame([], $validator->validate([
            new PipelineStep(type: AssistantPipelineProcessorType::Memory),
        ]));
    }
}
