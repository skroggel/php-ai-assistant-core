<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Madj2k\AiCore\Assistant\Context\Assistant;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;

/**
 * Class AssistantContext
 *
 * Contains the stable assistant configuration used by every pipeline run.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AssistantContext
{
    /**
     * Assistant profile uid.
     *
     * @var int
     */
    protected int $uid = 0;


    /**
     * Assistant profile title.
     *
     * @var string
     */
    protected string $title = '';


    /**
     * Public assistant label.
     *
     * @var string
     */
    protected string $assistantLabel = '';


    /**
     * AI connection.
     *
     * @var \Madj2k\AiCore\Connection\Domain\Model\AiConnectionConfigurationInterface|null
     */
    protected ?AiConnectionConfigurationInterface $aiConnection = null;


    /**
     * Vector store connection.
     *
     * @var \Madj2k\AiCore\Connection\Domain\Model\VectorStoreConnectionConfigurationInterface|null
     */
    protected ?VectorStoreConnectionConfigurationInterface $vectorStoreConnection = null;


    /**
     * Vector-store collection used by retrieval steps.
     *
     * @var string
     */
    protected string $collection = '';


    /**
     * Stable assistant identity prompt.
     *
     * @var string
     */
    protected string $identityPrompt = '';


    /**
     * Stable assistant behavior rules.
     *
     * @var string
     */
    protected string $behaviorRules = '';


    /**
     * Stable assistant retrieval rules.
     *
     * @var string
     */
    protected string $retrievalRules = '';


    /**
     * Stable assistant output rules.
     *
     * @var string
     */
    protected string $outputRules = '';


    /**
     * Constructor
     *
     * @param int $uid
     * @param string $title
     * @param string $assistantLabel
     * @param \Madj2k\AiCore\Connection\Domain\Model\AiConnectionConfigurationInterface|null $aiConnection
     * @param \Madj2k\AiCore\Connection\Domain\Model\VectorStoreConnectionConfigurationInterface|null $vectorStoreConnection
     * @param string $collection
     * @param string $identityPrompt
     * @param string $behaviorRules
     * @param string $retrievalRules
     * @param string $outputRules
     */
    public function __construct(
        int $uid = 0,
        string $title = '',
        string $assistantLabel = '',
        ?AiConnectionConfigurationInterface $aiConnection = null,
        ?VectorStoreConnectionConfigurationInterface $vectorStoreConnection = null,
        string $collection = '',
        string $identityPrompt = '',
        string $behaviorRules = '',
        string $retrievalRules = '',
        string $outputRules = ''

    ) {
        $this->uid = $uid;
        $this->title = $title;
        $this->assistantLabel = $assistantLabel;
        $this->aiConnection = $aiConnection;
        $this->vectorStoreConnection = $vectorStoreConnection;
        $this->collection = $collection;
        $this->identityPrompt = $identityPrompt;
        $this->behaviorRules = $behaviorRules;
        $this->retrievalRules = $retrievalRules;
        $this->outputRules = $outputRules;

    }


    /**
     * Returns the assistant profile uid.
     *
     * @return int Assistant profile uid.
     */
    public function getUid(): int
    {
        return $this->uid;
    }


    /**
     * Sets the assistant profile uid.
     *
     * @param int $uid Assistant profile uid.
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }


    /**
     * Returns the assistant profile title.
     *
     * @return string Assistant profile title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets the assistant profile title.
     *
     * @param string $title Assistant profile title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns the public assistant label.
     *
     * @return string Public assistant label.
     */
    public function getAssistantLabel(): string
    {
        return $this->assistantLabel;
    }


    /**
     * Sets the public assistant label.
     *
     * @param string $assistantLabel Public assistant label.
     * @return void
     */
    public function setAssistantLabel(string $assistantLabel): void
    {
        $this->assistantLabel = $assistantLabel;
    }


    /**
     * Returns the AI connection.
     *
     * @return \Madj2k\AiCore\Connection\Domain\Model\AiConnectionConfigurationInterface|null AI connection.
     */
    public function getAiConnection(): ?AiConnectionConfigurationInterface
    {
        return $this->aiConnection;
    }


    /**
     * Returns the vector store connection.
     *
     * @return \Madj2k\AiCore\Connection\Domain\Model\VectorStoreConnectionConfigurationInterface|null Vector store connection.
     */
    public function getVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface
    {
        return $this->vectorStoreConnection;
    }


    /**
     * Returns the vector-store collection used by retrieval steps.
     *
     * @return string Vector-store collection used by retrieval steps.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }


    /**
     * Sets the vector-store collection used by retrieval steps.
     *
     * @param string $collection Vector-store collection used by retrieval steps.
     * @return void
     */
    public function setCollection(string $collection): void
    {
        $this->collection = $collection;
    }


    /**
     * Returns the stable assistant identity prompt.
     *
     * @return string Stable assistant identity prompt.
     */
    public function getIdentityPrompt(): string
    {
        return $this->identityPrompt;
    }


    /**
     * Sets the stable assistant identity prompt.
     *
     * @param string $identityPrompt Stable assistant identity prompt.
     * @return void
     */
    public function setIdentityPrompt(string $identityPrompt): void
    {
        $this->identityPrompt = $identityPrompt;
    }


    /**
     * Returns the stable assistant behavior rules.
     *
     * @return string Stable assistant behavior rules.
     */
    public function getBehaviorRules(): string
    {
        return $this->behaviorRules;
    }


    /**
     * Sets the stable assistant behavior rules.
     *
     * @param string $behaviorRules Stable assistant behavior rules.
     * @return void
     */
    public function setBehaviorRules(string $behaviorRules): void
    {
        $this->behaviorRules = $behaviorRules;
    }


    /**
     * Returns the stable assistant retrieval rules.
     *
     * @return string Stable assistant retrieval rules.
     */
    public function getRetrievalRules(): string
    {
        return $this->retrievalRules;
    }


    /**
     * Sets the stable assistant retrieval rules.
     *
     * @param string $retrievalRules Stable assistant retrieval rules.
     * @return void
     */
    public function setRetrievalRules(string $retrievalRules): void
    {
        $this->retrievalRules = $retrievalRules;
    }


    /**
     * Returns the stable assistant output rules.
     *
     * @return string Stable assistant output rules.
     */
    public function getOutputRules(): string
    {
        return $this->outputRules;
    }


    /**
     * Sets the stable assistant output rules.
     *
     * @param string $outputRules Stable assistant output rules.
     * @return void
     */
    public function setOutputRules(string $outputRules): void
    {
        $this->outputRules = $outputRules;
    }
}
