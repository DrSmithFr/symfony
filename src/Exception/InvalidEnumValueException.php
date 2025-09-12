<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class InvalidEnumValueException extends Exception
{
    protected $code = 1;

    public function __construct(string $name, string $enumClass)
    {
        parent::__construct(sprintf('"%s" invalid value for %s', $name, $enumClass));
    }
}
