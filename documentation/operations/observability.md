# Logging, Tracing and Failure Analysis

AI Core exposes two complementary observability mechanisms.

## PipelineLoggerInterface

`PipelineLoggerInterface` is the external logging contract. It covers:

- chat start/finish/failure;
- step start/finish;
- LLM request/response;
- retrieval request/response;
- generic events and errors.

`PipelineLogMetaData` carries trace ID, query, chat identifier,
assistant profile, route and start time.

A production implementation should make the trace ID searchable across
all step/provider events.

## ProcessingTrace

`ProcessingTrace` lives in the runtime `Context` and is written directly
by processors. Built-in processors use event names such as:

```
query_optimizer.completed
retriever.completed
context_optimizer.completed
answer_generator.completed
quality_gate.completed
memory.retrieval_read.completed
memory.retrieval_write.completed
```

Use this for structured runtime introspection/debug payloads, while
`PipelineLoggerInterface` is better suited to persistent operational
logging.

## Skips are not failures

When `canProcess()` returns false, `Pipeline` records `step.skipped`
with reason `Required context slot missing.` and proceeds.

When debugging a missing answer or missing retrieval, check skipped
steps before assuming provider failure.

## Pipeline validation

`PipelineValidator` is executed before processing. Validation messages
are logged as `pipeline.validation.warning` when log metadata is
available. Validation warnings do not by themselves abort execution.

## Failure strategy

A thrown processor exception is logged as `step.failed`. It is rethrown
when:

- the step failure strategy is `Stop`; or
- the step is the visible streaming answer step and at least one chunk
  has already been emitted.

Otherwise execution continues.

## Provider resilience

The connection layer includes `RetryPolicy`, `RetryExecutor`,
`ExceptionClassifier` and `RetryExhaustedException`. Provider connectors
should use this infrastructure consistently so transient failures can be
retried without duplicating retry logic in pipeline processors.

Operationally, distinguish:

```
configuration/resolution errors
pipeline precondition skips
provider request failures
retry exhaustion
vector-store failures
indexing consistency failures
```

These classes of error have very different remediation paths.
