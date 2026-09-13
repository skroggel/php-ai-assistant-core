<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Ai\Enum;

/**
 * Enum EmbeddingPurpose
 *
 * Describes the provider-neutral purpose of an embedding request.
 *
 * Connectors may translate the purpose into provider-specific request
 * options when the selected embedding model supports such a distinction.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
enum EmbeddingPurpose: string
{
    case Unspecified = 'unspecified';
    case RetrievalDocument = 'retrieval_document';
    case RetrievalQuery = 'retrieval_query';
}
