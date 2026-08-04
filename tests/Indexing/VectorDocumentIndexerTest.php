<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Indexing;

use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfiguration;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfiguration;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorWriteResult;
use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\Identity\SourceIdentityGenerator;
use Madj2k\AiCore\Indexing\TextChunker;
use Madj2k\AiCore\Indexing\VectorDocumentIndexer;
use PHPUnit\Framework\TestCase;

final class VectorDocumentIndexerTest extends TestCase
{
    public function testIndexesChunksAndReplacesCurrentAndLegacySourceHashes(): void
    {
        $aiConnector = new class implements AiConnectorInterface {
            public function getIdentifier(): string { return 'test-ai'; }
            public function chat(AiConnectionConfigurationInterface $connection, AiRequest $request): AiResponse { return new AiResponse(); }
            public function streamChat(AiConnectionConfigurationInterface $connection, AiRequest $request, callable $onData): void {}
            public function embed(AiConnectionConfigurationInterface $connection, EmbeddingRequest $request): EmbeddingResponse { return new EmbeddingResponse([1.0, 2.0]); }
            public function embedBatch(AiConnectionConfigurationInterface $connection, array $requests): array
            {
                return array_map(static fn (): EmbeddingResponse => new EmbeddingResponse([1.0, 2.0]), $requests);
            }
        };
        $vectorConnector = new class implements VectorStoreConnectorInterface {
            /** @var array<int, string> */
            public array $deletedHashes = [];
            /** @var array<int, VectorDocument> */
            public array $writtenDocuments = [];
            public function getIdentifier(): string { return 'test-vector'; }
            public function ensureCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): bool { return true; }
            public function upsert(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, array $documents): VectorWriteResult
            {
                $this->writtenDocuments = $documents;
                return new VectorWriteResult(count($documents));
            }
            public function search(VectorStoreConnectionConfigurationInterface $connection, VectorSearchRequest $request): array { return []; }
            public function listCollections(VectorStoreConnectionConfigurationInterface $connection): array { return []; }
            public function deleteBySourceHash(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection, string $sourceHash): VectorDeleteResult
            {
                $this->deletedHashes[] = $sourceHash;
                return new VectorDeleteResult();
            }
            public function deleteCollection(VectorStoreConnectionConfigurationInterface $connection, VectorCollection $collection): VectorDeleteResult { return new VectorDeleteResult(); }
        };
        $aiConnection = new AiConnectionConfiguration(apiKey: 'secret', connectorIdentifier: 'test-ai');
        $vectorConnection = new VectorStoreConnectionConfiguration(endpoint: 'https://vector.test', connectorIdentifier: 'test-vector');
        $configuration = new class($aiConnection, $vectorConnection) implements IndexingConfigurationInterface {
            public function __construct(
                private AiConnectionConfigurationInterface $ai,
                private VectorStoreConnectionConfigurationInterface $vector,
            ) {}
            public function getCollection(): string { return 'documents'; }
            public function getAiConnection(): ?AiConnectionConfigurationInterface { return $this->ai; }
            public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface { return $this->vector; }
            public function getChunkSize(): int { return 5; }
            public function getChunkOverlap(): int { return 1; }
            public function getMaxChunks(): int { return 0; }
            public function getMinChunkChars(): int { return 1; }
        };
        $identity = new SourceIdentityGenerator();
        $indexer = new VectorDocumentIndexer(
            new AiConnectorResolver([$aiConnector]),
            new VectorStoreConnectorResolver([$vectorConnector]),
            new TextChunker(),
            $identity,
        );
        $document = new IndexableDocument('abcdefgh', new DocumentMetadata('page', '42', language: 1));

        $written = $indexer->index($configuration, $document, 'documents', false, ['legacy-hash']);

        self::assertSame(2, $written);
        self::assertSame(['legacy-hash', $identity->createSourceHash($document)], $vectorConnector->deletedHashes);
        self::assertCount(2, $vectorConnector->writtenDocuments);
        self::assertSame('documents', $vectorConnector->writtenDocuments[0]->getVectorName());
        self::assertSame($identity->createSourceHash($document), $vectorConnector->writtenDocuments[0]->getPayload()['meta']['source_hash']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/', $vectorConnector->writtenDocuments[0]->getId());
    }
}
