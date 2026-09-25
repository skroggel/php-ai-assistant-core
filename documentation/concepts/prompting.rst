Prompt Construction
===================

LLM-based processors use ``PromptBuilder`` rather than creating prompts
directly.

Message shape
-------------

Each LLM step receives two messages:

.. code:: text

   system
     assistant-level rules
     step-level rules
     runtime response requirements

   user
     optional chat history
     dynamic prompt context sections

``PromptBuilder::buildMessages()`` returns plain arrays, which
``AbstractLlmProcessor`` converts into ``AiMessage`` DTOs before
invoking an AI connector.

System prompt composition
-------------------------

The system prompt may include assistant-level sections depending on step
configuration:

- ``[Assistant Identity]``
- ``[Assistant Behavior Rules]``
- ``[Assistant Retrieval Rules]``
- ``[Assistant Output Rules]``

It can also include step-specific sections:

- ``[Step Identity]``
- ``[Step Behavior Rules]``
- ``[Step Retrieval Rules]``
- ``[Step Output Rules]``

For ``AnswerGenerator`` and ``QualityGate``, runtime chat options can
add explicit response-language and plain-language instructions.

A final precedence instruction states that runtime
response/accessibility requirements win over assistant/step output
preferences; assistant rules otherwise take precedence over step rules.

History
-------

History is controlled per pipeline step through ``HistoryMode``.

``None`` adds no history. ``LastN`` uses
``History::last(getHistoryLimit())`` and serializes messages as:

.. code:: text

   [Chat History]
   user: ...
   assistant: ...

Dynamic context builders
------------------------

The user-side context is extensible through ``ContextBuilderInterface``.

.. code:: mermaid

   flowchart LR
       Context --> Registry[ContextBuilderRegistry]
       Step --> Registry
       Registry --> Builders[matching ContextBuilderInterface instances]
       Builders --> Sections[PromptSection[]]
       Sections --> Sort[sort by priority]
       Sort --> Formatter
       Formatter --> UserPrompt[formatted context]

Built-in builders include:

- ``QueryContextBuilder`` - original/current query;
- ``RetrievalContextBuilder`` - retrieved/answer context;
- ``AnswerCandidateContextBuilder`` - answer candidate for quality-gate
  steps.

A custom builder can add new context without modifying
``PromptBuilder``.

Retrieval attribution
---------------------

When ``ContextOptimizerProcessor`` runs with retrieval groups, it adds a
system instruction requiring ``[Retrieval: ...]`` identifiers and
attribution boundaries to be preserved.

``RetrievalContextBuilder`` constructs grouped retrieval sections and
applies per-group/global context limits. When multiple retrieval groups
exist, its global character limiter distributes space across groups
instead of simply truncating everything after the first groups.

Design guidance
---------------

Use assistant-level rules for stable behavior shared across many steps.
Use step-level rules for the narrow responsibility of one processor. Use
context builders for structured runtime data; avoid embedding dynamic
application state directly into static assistant prompts.
