<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Exception;

/**
 * Class JsonRecordIdentityException
 *
 * Exception for JSON records that cannot be mapped to unique source identities.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class JsonRecordIdentityException extends IndexingException
{
    /**
     * @param string $message Exception message.
     * @param array<string, mixed> $details Structured diagnostic details.
     * @param int $code Exception code.
     */
    public function __construct(
        string $message,
        private readonly array $details = [],
        int $code = 1782295401
    ) {
        parent::__construct($message, $code);
    }


    /**
     * Returns structured diagnostic details.
     *
     * @return array<string, mixed> Details.
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
