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
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\Identity\SourceIdentityGenerator;
use Madj2k\AiCore\Indexing\TextChunker;
use Madj2k\AiCore\Indexing\VectorDocumentIndexer;
use Madj2k\AiCore\Tests\Support\RecordingVectorStoreConnector;
use PHPUnit\Framework\TestCase;

final class VectorDocumentIndexerTest extends TestCase
{
    public function testIndexesChunksAndReplacesCurrentAndLegacySourceHashes(): void
    {
        $vectorConnector = new RecordingVectorStoreConnector();
        [$indexer, $configuration, $document, $identity] = $this->createFixture($vectorConnector);

        $written = $indexer->index($configuration, $document, 'documents', false, ['legacy-hash']);

        $sourceHash = $identity->createSourceHash($document);
        self::assertSame(2, $written);
        self::assertSame(['legacy-hash'], $vectorConnector->deletedHashes);
        self::assertSame($sourceHash, $vectorConnector->generationCleanups[0]['sourceHash']);
        self::assertNotSame('', $vectorConnector->generationCleanups[0]['indexGeneration']);
        self::assertSame([
            'upsert',
            'delete:legacy-hash',
            'cleanup:' . $sourceHash,
        ], $vectorConnector->operations);
        self::assertCount(2, $vectorConnector->writtenDocuments);
        self::assertSame('documents', $vectorConnector->writtenDocuments[0]->getVectorName());
        self::assertSame($sourceHash, $vectorConnector->writtenDocuments[0]->getPayload()['meta']['source_hash']);
        self::assertSame(
            $vectorConnector->generationCleanups[0]['indexGeneration'],
            $vectorConnector->writtenDocuments[0]->getPayload()['meta']['index_generation'],
        );
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/', $vectorConnector->writtenDocuments[0]->getId());
    }

    public function testKeepsExistingVectorsWhenUpsertFails(): void
    {
        $vectorConnector = new RecordingVectorStoreConnector();
        $vectorConnector->failUpsert = true;
        [$indexer, $configuration, $document] = $this->createFixture($vectorConnector);

        try {
            $indexer->index($configuration, $document, 'documents', false, ['legacy-hash']);
            self::fail('Expected the simulated upsert failure.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Simulated upsert failure.', $exception->getMessage());
        }

        self::assertSame(['upsert'], $vectorConnector->operations);
        self::assertSame([], $vectorConnector->deletedHashes);
        self::assertSame([], $vectorConnector->generationCleanups);
    }

    /**
     * @return array{VectorDocumentIndexer,IndexingConfigurationInterface,IndexableDocument,SourceIdentityGenerator}
     */
    private function createFixture(RecordingVectorStoreConnector $vectorConnector): array
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

        return [$indexer, $configuration, $document, $identity];
    }
}
