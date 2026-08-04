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

## License

GNU General Public License 2.0 or later. See [LICENSE](LICENSE).
