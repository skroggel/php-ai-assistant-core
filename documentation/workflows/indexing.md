# Vector Indexing Lifecycle

`VectorDocumentIndexer` converts one normalized `IndexableDocument` into
vector documents and updates a vector-store collection safely.

## Flow

```
flowchart TD
    D[IndexableDocument] --> C[TextChunker]
    C --> Empty{chunks?}
    Empty -- no --> Z[return 0]
    Empty -- yes --> Dry{dry run?}
    Dry -- yes --> Count[return chunk count]
    Dry -- no --> E[embedBatch]
    E --> Dim[validate dimensions]
    Dim --> V[create VectorDocument[]]
    V --> U[upsert new vectors]
    U --> OldHash[delete explicitly obsolete source hashes]
    OldHash --> Gen[delete obsolete generations for current source]
    Gen --> Result[return written count]
```

## Collection resolution

`resolveCollection()` uses this precedence:

1.  explicit collection override;
2.  `IndexingConfigurationInterface::getCollection()`;
3.  vector-store connection default collection.

`index()` itself requires a non-empty collection name.

## Chunking

The indexer delegates to `TextChunker` with configured values for:

- chunk size;
- overlap;
- maximum chunk count;
- minimum chunk characters.

Non-positive configuration values are passed as `null`, allowing
`TextChunker` defaults to apply.

## Dry runs

A dry run performs chunking only and returns the number of chunks that
would be processed. It does not require AI/vector-store connectivity and
does not generate embeddings.

## Embeddings

Every chunk becomes an `EmbeddingRequest` with
`EmbeddingPurpose::RetrievalDocument`. Requests are sent as a batch
through the configured AI connector.

The indexer verifies two invariants:

1.  all non-empty provider embeddings have the same dimension;
2.  the actual dimension matches
    `AiConnectionConfigurationInterface::getEmbeddingDimension()`.

A mismatch raises `IndexingException` rather than writing incompatible
vectors.

## Source identity

`SourceIdentityGenerator` creates a stable source hash and deterministic
vector-document IDs based on source identity plus chunk index.

The index generation is a SHA-256 hash over chunk lengths and contents.
It therefore changes when content **or chunk boundaries** change.

## Safe replacement order

The write order is intentional:

```
1. upsert new generation
2. delete explicitly obsolete source hashes
3. delete obsolete generations for current source hash
```

New vectors are therefore available before old generations are removed.
A failed upsert does not first delete the currently indexed source
generation.

## Adapters and higher-level indexers

`AdapterInterface` converts a source path to normalized text.
`MultiDocumentAdapterInterface` can split one source into multiple
`IndexableDocument` instances.

`IndexerInterface` represents a higher-level source-specific indexing
workflow. `IndexerRegistry` resolves indexers by identifier or source
type.

The library currently includes `PlainAdapter` and `JsonAdapter` as
concrete text adapters.
