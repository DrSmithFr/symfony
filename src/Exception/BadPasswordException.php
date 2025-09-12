<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class BadPasswordException extends Exception
{
    protected $message = 'bad password';

    protected $code = 2;
}
