# Cookbook: Standard RAG Pipeline

A typical retrieval-augmented generation pipeline can be modeled as:

```
flowchart LR
    Q[User query] --> QO[Query optimizer]
    QO --> R[Retriever]
    R --> CO[Context optimizer]
    CO --> AG[Answer generator]
    AG --> QG[Quality gate]
```

## Data evolution

After context creation:

```
request.query = original user input
currentQuery  = original user input
retrieval     = empty
answer        = empty
```

After query optimization:

```
request.query = original user input
currentQuery  = optimized retrieval query
```

After retrieval:

```
retrieval.groups[] = vector-search result group(s)
```

After context optimization:

```
retrieval.answerContext = condensed/attributed context
```

After answer generation:

```
answer.candidate = generated answer
```

After quality gate:

```
answer.final = reviewed/finalized answer
```

## Optional retrieval reuse

To reuse a retrieval across turns, make persistence explicit:

```
First turn:
QueryOptimizer -> Retriever -> RetrievalWrite -> AnswerGenerator

Follow-up turn:
RetrievalRead -> AnswerGenerator
```

This pattern makes the lifetime of retrieval state visible in
configuration and allows alternative follow-up pipelines to choose
whether they want previous retrieval state at all.

## Streaming variant

With:

```
QueryOptimizer -> Retriever -> AnswerGenerator -> QualityGate
```

`QualityGate` is the last user-visible answer step and will be streamed
when its processor supports streaming.

If the quality-gate step is removed, `AnswerGenerator` becomes the
streaming step.
