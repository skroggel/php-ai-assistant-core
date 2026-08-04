<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Registry;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Indexer\IndexerInterface;

final class IndexerRegistry
{
    /** @var array<string, IndexerInterface> */
    private array $indexers = [];

    /** @param iterable<IndexerInterface> $indexers */
    public function __construct(iterable $indexers)
    {
        foreach ($indexers as $indexer) {
            $identifier = trim($indexer->getIdentifier());
            if ($identifier === '') {
                throw new IndexingException('Indexer identifiers must not be empty.', 1781001001);
            }
            if (isset($this->indexers[$identifier])) {
                throw new IndexingException(sprintf('Duplicate indexer identifier "%s".', $identifier), 1781001002);
            }
            $this->indexers[$identifier] = $indexer;
        }
    }

    public function get(string $identifier): IndexerInterface
    {
        return $this->indexers[$identifier]
            ?? throw new IndexingException(sprintf('No indexer registered for identifier "%s".', $identifier), 1760001001);
    }

    /** @return array<int, IndexerInterface> */
    public function findBySourceType(string $sourceType): array
    {
        return array_values(array_filter(
            $this->indexers,
            static fn (IndexerInterface $indexer): bool => $indexer->getSourceType() === $sourceType,
        ));
    }

    /** @return array<int, IndexerInterface> */
    public function all(): array
    {
        return array_values($this->indexers);
    }

    /** @return array<int, string> */
    public function getIdentifiers(): array
    {
        return array_keys($this->indexers);
    }
}
