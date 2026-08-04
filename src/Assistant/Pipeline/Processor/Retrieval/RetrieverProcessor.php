<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Pipeline\Processor\Retrieval;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\DTO\RetrievalDocument;
use Madj2k\AiCore\Assistant\Pipeline\Processor\AbstractRetrieverProcessor;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;

/**
 * Class RetrievalProcessor
 *
 * Pipeline processor that delegates retrieval to a connector.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class RetrieverProcessor extends AbstractRetrieverProcessor
{

    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Connection\Resolver\AiConnectorResolver $aiConnectorResolver AI connector registry.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorResolver Vector store connector registry.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface $pipelineLogger Pipeline logger.
     */
    public function __construct(
        private AiConnectorResolver          $aiConnectorResolver,
        private VectorStoreConnectorResolver $vectorStoreConnectorResolver,
        private PipelineLoggerInterface               $pipelineLogger,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.retriever.default';
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, PipelineStepConfigurationInterface $step): bool
    {
        return trim($context->getCurrentQuery()) !== ''
            && trim($context->getAssistant()->getCollection()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(Context $context, PipelineStepConfigurationInterface $step, ?PipelineLogMetaData $logContext = null): void
    {

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logRetrievalRequest($logContext, $step->getTitle(), $this->getIdentifier(), [
                'query' => $context->getCurrentQuery(),
                'collection' => $context->getAssistant()->getCollection(),
                'max_retrieval_results' => $step->getMaxRetrievalResults(),
                'score_threshold' => $step->getScoreThreshold(),
                'prompt_metadata_fields' => $step->getPromptMetadataFieldList(),
            ]);
        }

        $aiConnection = $context->getAssistant()->getAiConnection();
        if ($aiConnection === null) {
            throw new \RuntimeException('No AI connection configured for assistant profile.', 1780573301);
        }

        $embedding = $this->aiConnectorResolver
            ->get($aiConnection->getConnectorIdentifier())
            ->embed($aiConnection, new EmbeddingRequest($context->getCurrentQuery()))
            ->getEmbedding();

        $vectorStoreConnection = $context->getAssistant()->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            throw new \RuntimeException('No vector store connection configured for assistant profile.', 1780573302);
        }

        $rows = $this->vectorStoreConnectorResolver
            ->get($vectorStoreConnection->getConnectorIdentifier())
            ->search($vectorStoreConnection, new VectorSearchRequest(
                collection: $context->getAssistant()->getCollection(),
                vector: $embedding,
                limit: $step->getMaxRetrievalResults(),
                params: [
                    'hnsw_ef' => 128,
                    'exact' => false,
                ],
                withPayload: true,
                withVector: false,
                vectorName: $context->getAssistant()->getCollection()
            ));


        $documents = [];
        foreach ($rows as $row) {
            $payload = $row->getPayload();
            $score = $row->getScore();

            if ($step->getScoreThreshold() > 0.0 && $score < $step->getScoreThreshold()) {
                continue;
            }

            $documentMetadata = $this->extractMetadata($payload, $step);
            $documents[] = new RetrievalDocument(
                id: $row->getId(),
                score: $score,
                text: $this->extractText($payload),
                documentMetadata: $documentMetadata,
            );
        }

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logRetrievalResponse($logContext, $step->getTitle(), $this->getIdentifier(), [
                'result_count' => count($documents),
                'results' => $this->normalizeRawResults($rows),
            ]);
        }


        $context->getRetrieval()->setProcessorIdentifier($this->getIdentifier());
        $context->getRetrieval()->setRawResults($this->normalizeRawResults($rows));

        foreach ($documents as $document) {
            $context->getRetrieval()->addResult($document);
        }

        // trace
        $context->getProcessingTrace()->add('retriever.completed',
            $step->getUid(),
            $context->getCurrentQuery(),
            $documents,
        );

    }


    /**
     * Normalizes raw connector rows for logging and context storage.
     *
     * @param array<int,mixed> $rows Raw connector rows.
     * @return array<int,mixed> Normalized raw rows.
     */
    private function normalizeRawResults(array $rows): array
    {
        $rawResults = [];

        foreach ($rows as $row) {
            if (is_object($row)) {
                if (method_exists($row, 'toArray')) {
                    $rawResults[] = $row->toArray();
                }
                continue;
            }

            $rawResults[] = $row;
        }

        return $rawResults;
    }
}
