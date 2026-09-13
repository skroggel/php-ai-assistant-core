# API Reference Overview

This is a navigation-oriented reference. For method-level parameter details, the PHPDoc in the source remains the canonical low-level reference.

## Assistant entry points

### `Assistant\Application\Orchestrator`

- `handle(AssistantRequest): AssistantResponse`
- `handleStream(AssistantRequest, callable): AssistantResponse`
- `createStreamProducer(AssistantRequest, callable): callable`
- `handleDirect(AssistantRequest, DirectInteraction): AssistantResponse`

## Configuration contracts

### `AssistantConfigurationInterface`

Provides assistant identity/rules, AI/vector connections and pipeline steps.

### `PipelineStepConfigurationInterface`

Provides processor type/identifier/stage, prompt flags, history policy, model options, retrieval options, context limits, source metadata fields and failure strategy.

## Pipeline contracts

### `ProcessorInterface`

- `getIdentifier()`
- `supports(AssistantPipelineProcessorType)`
- `canProcess(Context, PipelineStepConfigurationInterface)`
- `process(Context, PipelineStepConfigurationInterface, ?PipelineLogMetaData)`

### `ProcessorStreamingInterface`

- `processStream(Context, PipelineStepConfigurationInterface, callable, ?PipelineLogMetaData)`

## Built-in processor identifiers

| Identifier | Type |
|---|---|
| `aiassistant.query_optimizer.default` | Query optimizer |
| `aiassistant.retriever.default` | Retriever |
| `aiassistant.context_optimizer.default` | Context optimizer |
| `aiassistant.answer_generator.default` | Answer generator |
| `aiassistant.quality_gate.default` | Quality gate |
| `aiassistant.memory.retrieval_read` | Memory |
| `aiassistant.memory.retrieval_write` | Memory |

## Runtime state

### `Context`

Shared state with assistant, request, history, retrieval, answer, current query and processing trace.

### `RetrievalResult`

Owns retrieval groups/documents, raw-result counts and optional optimized answer context.

### `AnswerState`

Owns answer candidate and final answer.

## Prompt contracts

### `ContextBuilderInterface`

- `supports(AssistantPipelineProcessorType)`
- `build(Context, PipelineStepConfigurationInterface): PromptSection[]`

### `PromptBuilder`

- `buildMessages(Context, PipelineStepConfigurationInterface)`
- `getContextSectionContent(Context, PipelineStepConfigurationInterface, string)`

## Connection contracts

### `AiConnectorInterface`

- `getIdentifier()`
- `chat()`
- `streamChat()`
- `embed()`
- `embedBatch()`

Concrete implementations in the source include `OpenAiConnector` and `GeminiConnector`.

### `VectorStoreConnectorInterface`

- `getIdentifier()`
- `ensureCollection()`
- `upsert()`
- `search()`
- `listCollections()`
- `deleteBySourceHash()`
- `deleteObsoleteSourceGenerations()`
- `deleteCollection()`

A concrete Qdrant implementation is provided by `QdrantVectorStoreConnector`.

## Memory contract

### `MemoryInterface`

Owns conversation history and last-retrieval persistence for a chat identifier.

The source includes session-backed memory via `SessionMemory` and `PhpSessionStore`.

## Indexing contracts

### `IndexingConfigurationInterface`

Provides collection, AI/vector connections and chunking limits.

### `AdapterInterface`

Normalizes source files to text.

### `MultiDocumentAdapterInterface`

Allows one source file to produce multiple `IndexableDocument` values.

### `IndexerInterface`

Defines a source-specific indexing workflow:

- `getIdentifier()`
- `getLabel()`
- `getSourceType()`
- `index(IndexingRequest): IndexingResult`

### `VectorDocumentIndexer`

Provider-independent chunk/embed/upsert service for one `IndexableDocument`.
