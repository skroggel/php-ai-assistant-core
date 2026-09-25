# Assistant Request Lifecycle

This chapter follows a normal `Orchestrator::handle()` call from input
to persisted conversation history.

## Synchronous request

```
sequenceDiagram
    participant App
    participant O as Orchestrator
    participant M as MemoryInterface
    participant CF as ContextFactory
    participant P as Pipeline

    App->>O: handle(AssistantRequest)
    O->>O: resolve query/profile/steps
    O->>M: start(chatIdentifier, startTimestamp)
    O->>M: getMessages(chatIdentifier)
    M-->>O: history[]
    O->>CF: create(request, history)
    CF-->>O: Context
    O->>P: run(context, steps)
    P-->>O: AssistantResponse
    O->>M: addMessage(user, query)
    O->>M: addMessage(assistant, answer)
    O-->>App: AssistantResponse
```

History is loaded **before** the current user query is appended. The
active query is already available through `Context::getRequest()` and
`currentQuery`, so history represents previous turns.

## Streaming request

`handleStream()` executes the same logical lifecycle but delegates to
pipeline streaming.

`createStreamProducer()` separates preparation from emission. It starts
memory, resolves steps, opens chat logging, loads history and creates
the context immediately; it returns a closure that later performs
`Pipeline::runStream()` and then persists the messages.

This is useful for frameworks where the response object must be
constructed before the streaming body starts producing data.

## Direct interaction

`handleDirect()` is a separate lightweight route. It deliberately
bypasses:

- pipeline processors;
- retrieval;
- vector-store queries.

It resolves the assistant AI connection and performs a direct chat call
with `DirectInteraction` settings. Conversation persistence only occurs
when `DirectInteraction::$remember` is true.

Use this route for small explicit LLM interactions that do not require
pipeline semantics.

## Logging lifecycle

The orchestrator creates `PipelineLogMetaData` and emits chat-level
lifecycle calls:

```
createMetaData()
startChat()
    ... pipeline / direct request ...
finishChat()
```

On uncaught failure it calls `failChat()` and rethrows the exception.

Individual pipeline steps and provider calls add more detailed log
events inside that envelope.
