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
