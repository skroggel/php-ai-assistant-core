..  _ai-core-architecture:

Architecture
============

AI Core has no TYPO3 or Symfony container dependency. Host applications provide configuration
objects and compose connectors, providers and resolvers through constructor injection.

The core contains:

* assistant context, memory and pipeline execution;
* provider-neutral AI and vector-store contracts;
* tool-calling contracts and bounded tool execution;
* indexing, adapters, chunking and source identities;
* resilience, connection health and normalized DTOs.

TYPO3 persistence, TCA, controllers, backend modules and HTTP integration belong to the host
integration. MCP protocol support is implemented by ``madj2k/ai-mcp``.
