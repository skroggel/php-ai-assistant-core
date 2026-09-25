# Extension points

Integrations should depend on public interfaces, DTOs, configuration
contracts and facades. The main extension points are:

- pipeline processors;
- prompt context builders;
- AI connectors;
- vector-store connectors;
- tool providers;
- indexers;
- source adapters;
- session stores;
- client factories and resilience policies.

Classes marked with `@internal` are bundled implementations or
provider-specific helpers. They may change without
backward-compatibility guarantees.
