<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Assistant;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Answer\AnswerState;
use Madj2k\AiCore\Assistant\Context\Assistant\AssistantContext;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Context\Request\History;
use Madj2k\AiCore\Assistant\Context\Request\Request;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\Context\Trace\ProcessingTrace;
use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Pipeline\Pipeline;
use Madj2k\AiCore\Assistant\Pipeline\PipelineValidator;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Tests\Support\PipelineStep;
use PHPUnit\Framework\TestCase;

final class PipelineTest extends TestCase
{
    public function testRunsProcessorAndExportsDeduplicatedSources(): void
    {
        $processor = new class implements ProcessorInterface {
            public function getIdentifier(): string { return 'test.processor'; }
            public function supports(AssistantPipelineProcessorType $type): bool { return $type === AssistantPipelineProcessorType::AnswerGenerator; }
            public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool { return true; }
            public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
            {
                $context->getAnswer()->setCandidate('Generated answer');
            }
        };
        $context = $this->context();
        $context->getRetrieval()->setAnswerContext('Grounded context');
        $context->getRetrieval()->setResults([
            $this->document('one', 'First title', 'https://example.test/page'),
            $this->document('two', 'Duplicate title', 'https://example.test/page'),
        ]);
        $pipeline = new Pipeline(
            new ProcessorRegistry([$processor]),
            new PipelineValidator(),
            $this->createStub(PipelineLoggerInterface::class),
        );

        $response = $pipeline->run($context, [new PipelineStep()]);

        self::assertSame('Generated answer', $response->answer);
        self::assertSame('Grounded context', $response->context['answerContext']);
        self::assertSame(2, $response->context['retrievalCount']);
        self::assertSame([
            ['title' => 'First title', 'url' => 'https://example.test/page'],
        ], $response->context['sources']);
    }

    public function testContinuesAfterFailureWhenConfigured(): void
    {
        $pipeline = $this->failingPipeline();

        $response = $pipeline->run($this->context(), [new PipelineStep(
            failureStrategy: AssistantPipelineFailureStrategy::Continue,
        )]);

        self::assertSame('', $response->answer);
    }

    public function testLegacyFallbackContinuesAfterFailure(): void
    {
        $pipeline = $this->failingPipeline();

        $response = $pipeline->run($this->context(), [new PipelineStep(
            failureStrategy: AssistantPipelineFailureStrategy::Fallback,
        )]);

        self::assertSame('', $response->answer);
    }

    public function testStopsAfterFailureWhenConfigured(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('processor failed');

        $this->failingPipeline()->run($this->context(), [new PipelineStep(
            failureStrategy: AssistantPipelineFailureStrategy::Stop,
        )]);
    }

    private function failingPipeline(): Pipeline
    {
        $processor = new class implements ProcessorInterface {
            public function getIdentifier(): string { return 'test.processor'; }
            public function supports(AssistantPipelineProcessorType $type): bool { return true; }
            public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool { return true; }
            public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
            {
                throw new \RuntimeException('processor failed');
            }
        };

        return new Pipeline(
            new ProcessorRegistry([$processor]),
            new PipelineValidator(),
            $this->createStub(PipelineLoggerInterface::class),
        );
    }

    private function context(): Context
    {
        return new Context(
            new AssistantContext(),
            new Request('Question', 'chat-id'),
            new History([]),
            new RetrievalResult(),
            new AnswerState(),
            new ProcessingTrace(),
        );
    }

    private function document(string $id, string $title, string $url): RetrievalDocument
    {
        return new RetrievalDocument(
            $id,
            0.9,
            'Document content',
            new DocumentMetadata('page', $id, $title, $url),
        );
    }
}
