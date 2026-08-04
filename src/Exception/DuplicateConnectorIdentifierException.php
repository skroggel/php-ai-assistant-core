<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

final class DuplicateConnectorIdentifierException extends \LogicException
{
    public function __construct(string $kind, string $identifier)
    {
        parent::__construct(sprintf('Duplicate %s connector identifier "%s".', $kind, $identifier));
    }
}
