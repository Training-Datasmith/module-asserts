<?php

declare(strict_types=1);

namespace Support\Data;

use PDOException;

final class SqlStatePdoException extends PDOException
{
    public function __construct(string $message, string $sqlState)
    {
        parent::__construct($message);
        $this->code = $sqlState;
    }
}
