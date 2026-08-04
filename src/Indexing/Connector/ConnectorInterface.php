<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Indexing\Connector;

/**
 * Interface ConnectorInterface
 *
 * Defines an external indexing connector that can fetch remote source data.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface ConnectorInterface
{
    /**
     * Returns the connector identifier.
     *
     * @return string Connector identifier.
     */
    public function getIdentifier(): string;


    /**
     * Returns the connector label.
     *
     * @return string Connector label.
     */
    public function getLabel(): string;
}
