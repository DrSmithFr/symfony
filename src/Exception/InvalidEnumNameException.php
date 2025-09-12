<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class InvalidEnumNameException extends Exception
{
    protected $code = 1;

    public function __construct(string $name, string $enumClass)
    {
        parent::__construct(sprintf('"%s" invalid name for %s', $name, $enumClass));
    }
}
