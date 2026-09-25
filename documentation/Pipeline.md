# Pipeline

Pipeline steps run in configured order. Their stage describes semantic
position and does not reorder the steps. A common retrieval-augmented
pipeline is:

1.  Query optimizer;
2.  retriever;
3.  optional context optimizer;
4.  answer generator;
5.  optional quality gate.

Failure strategies are:

- `stop` aborts execution and rethrows the exception;
- `continue` logs the failure and runs the next step;
- `fallback` is a deprecated alias of `continue`.

The pipeline validates processor/type compatibility, dependencies,
duplicate step identifiers and answer-generator or quality-gate
constraints before execution.
