<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

/**
 * Class DuplicateConnectorIdentifierException
 *
 * Raised when multiple connectors register the same identifier.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class DuplicateConnectorIdentifierException extends \LogicException
{
    /**
     * @param string $kind Human-readable connector kind.
     * @param string $identifier Duplicate connector identifier.
     */
    public function __construct(string $kind, string $identifier)
    {
        parent::__construct(sprintf('Duplicate %s connector identifier "%s".', $kind, $identifier));
    }
}
