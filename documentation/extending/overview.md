# Extension Points

AI Core is designed around replaceable contracts. Prefer adding
implementations of these contracts over modifying orchestration code.

| Goal | Contract / mechanism |
|----|----|
| Add pipeline behavior | `ProcessorInterface` |
| Add streaming behavior | `ProcessorStreamingInterface` |
| Add prompt runtime context | `ContextBuilderInterface` |
| Add an LLM/embedding provider | `AiConnectorInterface` |
| Add a vector database | `VectorStoreConnectorInterface` |
| Replace conversation storage | `MemoryInterface` |
| Add source-file normalization | `AdapterInterface` / `MultiDocumentAdapterInterface` |
| Add a source-specific indexing workflow | `IndexerInterface` |
| Add indexing source connectors | `Indexing\Connector\ConnectorInterface` |

## Custom processor

A processor has four responsibilities:

``` php
final class MyProcessor implements ProcessorInterface
{
    public function getIdentifier(): string
    {
        return 'myvendor.my_processor';
    }

    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::Memory;
    }

    public function canProcess(
        Context $context,
        PipelineStepConfigurationInterface $step
    ): bool {
        return trim($context->getCurrentQuery()) !== '';
    }

    public function process(
        Context $context,
        PipelineStepConfigurationInterface $step,
        ?PipelineLogMetaData $logContext = null
    ): void {
        // Read state from $context and write the result back to $context.
    }
}
```

### Processor design rules

- Keep `getIdentifier()` stable; configuration persists this identifier.
- `supports()` is a configuration/registration guard, not a runtime
  precondition.
- Put runtime prerequisites in `canProcess()` so missing state causes a
  clean skip.
- Put results in explicit context slots or documented context objects.
- If a processor can become the visible final answer and supports
  chunked output, also implement `ProcessorStreamingInterface`.
- Do not rely on execution-stage sorting inside `Pipeline`; step order
  must already be correct.

## Custom prompt context

A context builder contributes typed prompt sections:

``` php
final class TenantContextBuilder implements ContextBuilderInterface
{
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::AnswerGenerator;
    }

    public function build(
        Context $context,
        PipelineStepConfigurationInterface $step
    ): array {
        return [new PromptSection(
            title: 'Tenant Context',
            content: '...',
            priority: 25,
        )];
    }
}
```

The registry combines all supporting builders and sorts `PromptSection`s
by priority.

## Custom AI connector

Implement `AiConnectorInterface` when adding a provider:

```
getIdentifier()
chat()
streamChat()
embed()
embedBatch()
```

The connector is selected by
`AiConnectionConfigurationInterface::getConnectorIdentifier()` via
`AiConnectorResolver`.

A good connector should translate provider-specific failures into the
library's provider/error model and use shared retry infrastructure where
appropriate.

## Custom vector-store connector

Implement `VectorStoreConnectorInterface` for another vector database.
Besides collection/search/upsert operations, support the deletion
methods used by indexing:

```
deleteBySourceHash()
deleteObsoleteSourceGenerations()
```

These are part of the generation-based replacement semantics and should
not be treated as optional conveniences.

## Custom memory

`MemoryInterface` owns both conversation history and last-retrieval
state. A custom implementation can back this with Redis, database
storage or another session mechanism while leaving the orchestrator
unchanged.

## Custom adapter

Use `AdapterInterface` when one source file maps to one normalized text
document. Use `MultiDocumentAdapterInterface` when one source may
produce several logical documents.

Keep source identity/metadata stable across runs so indexing can replace
old vectors deterministically.
