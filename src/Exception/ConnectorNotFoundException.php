<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

final class ConnectorNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $kind, string $identifier, int $code)
    {
        parent::__construct(sprintf('No %s connector registered for identifier "%s".', $kind, $identifier), $code);
    }
}
