# Connections and authentication

AI connectors receive an `AiConnectionConfigurationInterface` and
translate provider-specific requests and responses into core DTOs.
Vector-store connectors use their corresponding connection contract.

AI connectors support API-key authentication and OAuth 2.0 Client
Credentials when the connection configuration exposes the OAuth
settings. OAuth tokens are cached in memory and renewed before expiry.
Authorization Code and interactive PKCE flows are application-specific
and are outside the core connector contract.

Connection health checks and retry policies are also provider-neutral.
Applications decide how diagnostic results and logs are stored.
