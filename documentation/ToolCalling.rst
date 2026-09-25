..  _ai-core-tool-calling:

Tool calling
============

Tool providers implement ``ToolProviderInterface`` and expose ``ToolDefinition`` objects. The
runtime normalizes model requests to ``ToolCall`` and returns ``ToolResult`` objects.

``ToolCallingService`` executes a bounded model/tool loop:

1. tool definitions are sent to the model;
2. requested calls are resolved through ``ToolRegistry``;
3. provider results are added to the conversation;
4. the model is called again until it returns an answer or the round limit is reached.

Providers that depend on the active assistant or pipeline step implement
``ContextAwareToolProviderInterface``. The host application remains responsible for authorization
and argument validation. A model-provided schema is not a security boundary.
