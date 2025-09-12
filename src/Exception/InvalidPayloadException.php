<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class InvalidPayloadException extends Exception
{
    protected $message = 'invalid payload';

    protected $code = 1;
}
