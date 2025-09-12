<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class EncryptionErrorException extends Exception
{
    protected $message = 'encryption fail';

    protected $code = 1;
}
