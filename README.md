# AI Core

`madj2k/ai-core` contains the framework-independent assistant runtime plus AI, vector-store and indexing building blocks. The indexing core owns source identities, chunking, embedding generation, vector replacement and indexer discovery.
It has no TYPO3 or Symfony container dependency. Applications provide configuration objects and
compose connectors and resolvers through constructor injection.

TYPO3 source discovery, persistence, TCA, controllers, session-backed memory, persistent logging and HTTP integration remain in `madj2k/ai-assistant`.

## Requirements

- PHP 8.2 or newer
- JSON and mbstring extensions

## Installation

```bash
composer require madj2k/ai-core
```

## Tests

```bash
composer install
composer test
```

The test suite is framework-independent and does not bootstrap TYPO3.

## Public API and extension points

Integrations should depend on the provided interfaces, DTOs, configuration contracts and public
facades. Custom pipeline processors, prompt context builders, connectors, client factories,
indexers and file adapters are integrated through their corresponding interfaces or abstract base
classes.

Classes marked with `@internal` are bundled implementations or provider-specific helpers. They may
change without backward-compatibility guarantees and should not be extended or referenced by
integrations. The annotation does not restrict direct use in tests.

## Pipeline configuration

Pipeline steps run in their configured order. Their stage describes the semantic position of the
step; it does not reorder the pipeline. A common retrieval-augmented pipeline is:

1. Query optimizer (`pre_retrieval`)
2. Retriever (`retrieval`)
3. Optional query optimizer (`post_retrieval`)
4. Optional second retriever (`retrieval`)
5. Context optimizer (`post_retrieval`)
6. Answer generator (`pre_answer`)
7. Optional quality gate (`post_answer`)

The validator uses the following stage and dependency rules:

| Processor type | Expected stage | Dependency |
| --- | --- | --- |
| Query optimizer | `pre_retrieval` or `post_retrieval` | A post-retrieval optimizer should follow a retriever or memory step. |
| Retriever | `retrieval` | None |
| Context optimizer | `post_retrieval` | Must follow a retriever or memory step. |
| Answer generator | `pre_answer` | Must not run after a quality gate. |
| Quality gate | `post_answer` | Must follow an answer generator. |
| Memory | Any | None |

Invalid dependencies, duplicate persisted step UIDs, unknown processors and processor/type
mismatches stop execution before the first processor runs. Unexpected stages, multiple answer
generators or quality gates, and unusual failure strategies are reported as validation warnings.
This permits custom pipelines without silently accepting configurations that cannot work.

Failure strategies apply when a processor throws an exception:

- `stop` aborts the pipeline and rethrows the exception.
- `continue` logs the failure and runs the next step.
- `fallback` is a deprecated alias of `continue`; it does not execute a separate fallback action.

Answer generators and quality gates should normally use `stop`, because they define the visible
answer. During streaming, only the final answer-producing step streams to the user. Once that step
has emitted data, its failure always stops execution to avoid returning a partial answer as if it
were complete.

## Logging and diagnostics

The core does not select a log file or storage backend. Applications inject a
`PipelineLoggerInterface` implementation and decide whether events are written to a database,
PSR logger, file or observability service. Validation warnings use the event name
`pipeline.validation.warning`; failed steps use `step.failed`.

The TYPO3 `madj2k/ai-assistant` integration stores pipeline events in
`tx_aiassistant_pipeline_trace`. Configure tracing under **AI Assistant > Configuration**:

- `chat.pipelineLog.mode = errors` stores failed events only.
- `chat.pipelineLog.mode = verbose` stores the complete pipeline trace, including validation warnings.
- `chat.pipelineLog.writePsrLog = 1` additionally forwards enabled events to TYPO3's PSR logger.

Stored traces can be inspected under **AI Assistant > Diagnostics** and filtered by chat identifier.
This is the primary place to follow one request through its pipeline steps.

The extension configures its PSR log file as `var/log/tx_aiassistant.log`. General TYPO3 errors are
usually written to files matching `var/log/typo3_*.log`. In a DDEV project, the files can be followed
from the project root with:

```bash
ddev exec tail -f /var/www/html/var/log/tx_aiassistant.log
ddev exec sh -c 'tail -f /var/www/html/var/log/typo3_*.log'
```

Normal pipeline events are debug-level diagnostics and are most reliably inspected in the backend
Diagnostics view with pipeline log mode set to `verbose`. File output depends on the application's
PSR log-level configuration.

## Connector resilience

OpenAI and Qdrant clients are created through injectable factories. The default factories use
Guzzle with explicit request and connection timeouts. Provider requests use bounded exponential
backoff for transient network errors, rate limits and selected HTTP status codes.

The default policy uses three attempts, a 250 ms initial delay, a 2 second maximum delay, a
30 second request timeout and a 10 second connection timeout. Streaming requests are retried only
before the first response chunk has been emitted, preventing duplicate output.

Applications can adjust the policy without implementing a provider connector:

```php
use Madj2k\AiCore\Connection\Ai\OpenAiConnector;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;

$connector = new OpenAiConnector(
    retryPolicy: new RetryPolicy(
        maxAttempts: 4,
        initialDelayMilliseconds: 500,
        timeoutSeconds: 45.0,
        connectTimeoutSeconds: 10.0,
    ),
);
```

For isolated tests or custom transports, implement `OpenAiClientFactoryInterface` or
`QdrantClientFactoryInterface` and inject the factory into the connector. Final provider errors
expose the provider, operation, HTTP status, retryability and number of attempts through
`ApiException` or `VectorDatabaseException`.

## License

GNU General Public License 2.0 or later. See [LICENSE](LICENSE).
