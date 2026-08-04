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
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;
use Madj2k\AiCore\Assistant\Prompt\Context\Builder\ContextBuilderInterface;
use Madj2k\AiCore\Assistant\Prompt\Context\Formatter;
use Madj2k\AiCore\Assistant\Prompt\Context\PromptSection;
use Madj2k\AiCore\Assistant\Prompt\Context\Registry\ContextBuilderRegistry;
use Madj2k\AiCore\Assistant\Prompt\PromptBuilder;
use Madj2k\AiCore\Tests\Support\PipelineStep;
use PHPUnit\Framework\TestCase;

final class PromptBuilderTest extends TestCase
{
    public function testBuildsSystemHistoryAndCurrentContextMessages(): void
    {
        $context = new Context(
            new AssistantContext(
                identityPrompt: 'Core identity',
                behaviorRules: 'Core behavior',
                outputRules: 'Core output',
            ),
            new Request('current question', 'chat-id'),
            new History([
                ['role' => 'user', 'content' => 'old question'],
                ['role' => 'assistant', 'content' => 'old answer'],
                ['role' => 'user', 'content' => 'recent question'],
            ]),
            new RetrievalResult(),
            new AnswerState(),
            new ProcessingTrace(),
        );
        $builder = new class implements ContextBuilderInterface {
            public function supports(AssistantPipelineProcessorType $type): bool
            {
                return $type === AssistantPipelineProcessorType::AnswerGenerator;
            }

            public function build(Context $context, PipelineStepConfigurationInterface $step): array
            {
                return [new PromptSection('Original User Query', $context->getRequest()->getQuery(), 10)];
            }
        };
        $promptBuilder = new PromptBuilder(
            new ContextBuilderRegistry([$builder]),
            new Formatter(),
        );
        $step = new PipelineStep(
            historyMode: HistoryMode::LastN,
            historyLimit: 2,
            includeIdentity: true,
            includeBehavior: true,
            includeOutput: true,
            stepIdentity: 'Generate the answer',
        );

        $messages = $promptBuilder->buildMessages($context, $step);

        self::assertSame('system', $messages[0]['role']);
        self::assertStringContainsString("[Assistant Identity]\nCore identity", $messages[0]['content']);
        self::assertStringContainsString("[Assistant Behavior Rules]\nCore behavior", $messages[0]['content']);
        self::assertStringContainsString("[Assistant Output Rules]\nCore output", $messages[0]['content']);
        self::assertStringContainsString("[Step Identity]\nGenerate the answer", $messages[0]['content']);
        self::assertSame('user', $messages[1]['role']);
        self::assertStringNotContainsString('old question', $messages[1]['content']);
        self::assertStringContainsString('assistant: old answer', $messages[1]['content']);
        self::assertStringContainsString('user: recent question', $messages[1]['content']);
        self::assertStringContainsString("[Original User Query]\ncurrent question", $messages[1]['content']);
    }
}
