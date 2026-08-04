<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Support;

use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorDeleteResult;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorWriteResult;
use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;

final class RecordingVectorStoreConnector implements VectorStoreConnectorInterface
{
    /** @var array<int, string> */
    public array $operations = [];

    /** @var array<int, string> */
    public array $deletedHashes = [];

    /** @var array<int, array{sourceHash:string,indexGeneration:string}> */
    public array $generationCleanups = [];

    /** @var array<int, \Madj2k\AiCore\Connection\VectorStore\DTO\VectorDocument> */
    public array $writtenDocuments = [];

    public ?VectorCollection $upsertCollection = null;

    public bool $failUpsert = false;

    public function getIdentifier(): string { return 'test-vector'; }

    public function ensureCollection(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
    ): bool {
        return true;
    }

    public function upsert(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
        array $documents,
    ): VectorWriteResult {
        $this->operations[] = 'upsert';
        $this->upsertCollection = $collection;
        if ($this->failUpsert) {
            throw new \RuntimeException('Simulated upsert failure.');
        }

        $this->writtenDocuments = $documents;
        return new VectorWriteResult(count($documents));
    }

    public function search(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorSearchRequest $request,
    ): array {
        return [];
    }

    public function listCollections(VectorStoreConnectionConfigurationInterface $connection): array
    {
        return [];
    }

    public function deleteBySourceHash(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
        string $sourceHash,
    ): VectorDeleteResult {
        $this->operations[] = 'delete:' . $sourceHash;
        $this->deletedHashes[] = $sourceHash;
        return new VectorDeleteResult();
    }

    public function deleteObsoleteSourceGenerations(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
        string $sourceHash,
        string $indexGeneration,
    ): VectorDeleteResult {
        $this->operations[] = 'cleanup:' . $sourceHash;
        $this->generationCleanups[] = [
            'sourceHash' => $sourceHash,
            'indexGeneration' => $indexGeneration,
        ];
        return new VectorDeleteResult();
    }

    public function deleteCollection(
        VectorStoreConnectionConfigurationInterface $connection,
        VectorCollection $collection,
    ): VectorDeleteResult {
        return new VectorDeleteResult();
    }
}
