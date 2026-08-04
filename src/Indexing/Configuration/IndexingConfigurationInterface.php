<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Configuration;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

interface IndexingConfigurationInterface
{
    public function getCollection(): string;

    public function getAiConnection(): ?AiConnectionConfigurationInterface;

    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface;

    public function getChunkSize(): int;

    public function getChunkOverlap(): int;

    public function getMaxChunks(): int;

    public function getMinChunkChars(): int;
}
