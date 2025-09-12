<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class DecryptionErrorException extends Exception
{
    protected $message = 'Decryption fail';

    protected $code = 1;
}
