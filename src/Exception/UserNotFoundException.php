<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'user not found';

    protected $code = 404;
}
