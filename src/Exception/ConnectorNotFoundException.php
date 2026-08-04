<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

/**
 * Class ConnectorNotFoundException
 *
 * Raised when a connector identifier cannot be resolved.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class ConnectorNotFoundException extends \InvalidArgumentException
{
    /**
     * @param string $kind Human-readable connector kind.
     * @param string $identifier Missing connector identifier.
     * @param int $code Application-specific exception code.
     */
    public function __construct(string $kind, string $identifier, int $code)
    {
        parent::__construct(sprintf('No %s connector registered for identifier "%s".', $kind, $identifier), $code);
    }
}
