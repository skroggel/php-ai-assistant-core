<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Indexing\Indexer;

use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;

/**
 * Interface IndexerInterface
 *
 * Defines a discoverable source indexer and its batch indexing entry point.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface IndexerInterface
{
    /** Returns the unique indexer identifier. */
    public function getIdentifier(): string;

    /** Returns the human-readable indexer label. */
    public function getLabel(): string;

    /** Returns the indexed source type. */
    public function getSourceType(): string;

    /** Executes one indexing batch. */
    public function index(IndexingRequest $request): IndexingResult;
}
