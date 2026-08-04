<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Indexer;

use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;

interface IndexerInterface
{
    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getSourceType(): string;

    public function index(IndexingRequest $request): IndexingResult;
}
