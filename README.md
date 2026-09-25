# AI Core

`madj2k/ai-core` contains the framework-independent runtime and shared building blocks for Madj2k
AI integrations:

- assistant context, memory and pipeline execution;
- provider-neutral AI and vector-store contracts;
- tool-calling contracts and bounded tool execution;
- indexing, adapters, chunking and source identities;
- resilience, connection health and normalized DTOs.

The package has no TYPO3 or Symfony container dependency. Host applications provide configuration
objects and compose connectors, providers and resolvers through constructor injection. TYPO3
persistence, TCA, controllers, backend modules and HTTP integration belong to
`madj2k/t3-ai-assistant`. MCP protocol support belongs to `madj2k/ai-mcp`.

## Requirements

- PHP 8.2 or newer
- JSON and mbstring extensions

## Installation

```bash
composer require madj2k/ai-core
```

## Public API

Integrations should depend on public interfaces, DTOs, configuration contracts and facades. Custom
pipeline processors, prompt context builders, AI connectors, vector-store connectors, tool
providers, indexers and adapters are registered through their corresponding contracts.

Classes marked with `@internal` are bundled implementations or provider-specific helpers. They may
change without backward-compatibility guarantees.

## Tests

```bash
composer install
composer test
```

The test suite is framework-independent and does not bootstrap TYPO3.

See [Documentation](documentation/index.md) for architecture, pipeline, tool-calling and
extension-point details.

## License

GNU General Public License version 3. See [LICENSE](LICENSE).
