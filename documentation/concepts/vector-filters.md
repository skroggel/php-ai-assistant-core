# Vector Filters

Vector retrieval filters are defined independently of a specific vector-store
vendor under:

```text
Connection/VectorStore/Filter/DTO/
```

## Filter DTOs

`FilterCondition` contains a payload field, operator and value. Conditions are
combined by `FilterGroup` using `FilterConjunction::And` or `Or`.

Supported operators currently include:

- `equals` for one scalar value;
- `in` for a list of values;
- `exists` for payload presence.

`VectorSearchRequest` carries the optional `FilterGroup` separately from
technical search parameters such as `hnsw_ef` and `exact`.

## Resolver boundary

`FilterValueResolverInterface` resolves runtime values before the request is
sent to a vector-store connector. `ai-core` provides an identity resolver.
Integrations can replace it with a resolver for application-specific
placeholders.

This keeps CMS- or request-specific concepts out of the generic DTOs and out of
vendor connectors.

## Connector mapping

Each connector maps the generic filter to its native query language. The Qdrant
connector maps `equals`, `in`, and `exists` to Qdrant payload conditions.
Unsupported operations should be rejected or handled by the connector rather
than leaking vendor-specific structures into pipeline configuration.
