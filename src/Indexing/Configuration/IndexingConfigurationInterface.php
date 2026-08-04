<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Configuration;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

/**
 * Interface IndexingConfigurationInterface
 *
 * Defines connector and chunking settings required to index documents.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface IndexingConfigurationInterface
{
    /** Returns the configured vector collection name. */
    public function getCollection(): string;

    /** Returns the AI connection used to create embeddings. */
    public function getAiConnection(): ?AiConnectionConfigurationInterface;

    /** Returns the vector store connection used to persist documents. */
    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface;

    /** Returns the target chunk length in characters. */
    public function getChunkSize(): int;

    /** Returns the chunk overlap in characters. */
    public function getChunkOverlap(): int;

    /** Returns the maximum number of chunks per document. */
    public function getMaxChunks(): int;

    /** Returns the minimum retained chunk length in characters. */
    public function getMinChunkChars(): int;
}
