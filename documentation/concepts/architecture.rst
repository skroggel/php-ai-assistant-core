Architecture
============

AI Core has two primary execution paths:

1. an **assistant path**, which receives a user request and executes a
   configurable pipeline;
2. an **indexing path**, which turns source content into vectors used by
   retrieval.

Assistant architecture
----------------------

.. code:: mermaid

   flowchart LR
       Request[AssistantRequest] --> Orchestrator
       Orchestrator --> Memory[(Memory)]
       Orchestrator --> ContextFactory
       ContextFactory --> Context
       Orchestrator --> Pipeline
       Pipeline --> Registry[ProcessorRegistry]
       Registry --> Processor[ProcessorInterface]
       Processor --> Context
       Processor --> PromptBuilder
       Processor --> AI[AiConnectorInterface]
       Processor --> VS[VectorStoreConnectorInterface]
       Pipeline --> Response[AssistantResponse]
       Response --> Orchestrator
       Orchestrator --> Memory

Orchestrator
~~~~~~~~~~~~

``Assistant\Application\Orchestrator`` owns the lifecycle of one
assistant interaction. It is responsible for:

- validating/resolving the request and assistant profile;
- starting conversation memory;
- loading history;
- creating the runtime ``Context``;
- resolving configured pipeline steps;
- invoking ``Pipeline`` synchronously or in streaming mode;
- appending the user query and assistant answer to memory;
- opening, finishing and failing chat-level logs.

It does **not** implement retrieval, prompt generation or
provider-specific behavior itself.

Pipeline
~~~~~~~~

``Assistant\Pipeline\Pipeline`` executes configured steps in the order
it receives them. For every step it:

1. resolves a processor by ``processorIdentifier + type``;
2. asks ``canProcess()`` whether required state exists;
3. optionally skips the step;
4. executes ``process()`` or ``processStream()``;
5. applies the configured failure strategy;
6. leaves state changes in the shared ``Context``.

Context
~~~~~~~

``Assistant\Context\Context`` is the shared mutable state of one
pipeline run. It prevents tight coupling between processors. A query
optimizer can mutate ``currentQuery``; a retriever stores retrieval
groups; an answer generator writes the answer candidate; a quality gate
writes the final answer.

Prompt subsystem
~~~~~~~~~~~~~~~~

LLM processors delegate prompt assembly to ``PromptBuilder``. Dynamic
context is supplied by independent ``ContextBuilderInterface``
implementations and merged by ``ContextBuilderRegistry``.

Connector layer
~~~~~~~~~~~~~~~

The pipeline does not depend directly on OpenAI, Gemini or Qdrant
implementations. Resolver classes select an implementation by connector
identifier:

.. code:: text

   configuration
       -> connector identifier
       -> resolver
       -> connector interface
       -> concrete provider implementation

Indexing architecture
---------------------

.. code:: mermaid

   flowchart LR
       Source[Source] --> Adapter[AdapterInterface]
       Adapter --> Doc[IndexableDocument]
       Doc --> Chunker[TextChunker]
       Chunker --> Embeddings[AiConnectorInterface / embedBatch]
       Embeddings --> Vectors[VectorDocument[]]
       Vectors --> Store[VectorStoreConnectorInterface]

``VectorDocumentIndexer`` is the core provider-independent indexing
service. Higher-level source-specific ``IndexerInterface``
implementations can use adapters and this service to build concrete
indexing workflows.

Dependency direction
--------------------

A useful mental model is:

.. code:: text

   Application / configuration
           ↓
   Core orchestration and contracts
           ↓
   Resolvers / registries
           ↓
   Provider and infrastructure implementations

Custom code should generally depend on the contracts
(``ProcessorInterface``, ``AiConnectorInterface``,
``VectorStoreConnectorInterface``, ``MemoryInterface``,
``AdapterInterface``, ``IndexerInterface``, ``ContextBuilderInterface``)
rather than on concrete standard implementations.
