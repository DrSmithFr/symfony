<?php

namespace App\Exception;

use Exception;
use Throwable;

class TokenNotFoundException extends Exception
{
    public function __construct(string $tokenName = "", Throwable $previous = null)
    {
        $message = 'token validated';

        if ($tokenName) {
            $message = sprintf('token "%s" not found', $tokenName);
        }

        parent::__construct($message, 3, $previous);
    }
}
