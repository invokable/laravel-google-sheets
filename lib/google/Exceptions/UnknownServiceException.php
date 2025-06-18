<?php

namespace Revolution\Google\Client\Exceptions;

use Throwable;

class UnknownServiceException extends \Exception
{
    public static function throwForService(string $service, int $code = 0, ?Throwable $previous = null)
    {
        throw new static("Unknown service [$service].", $code, $previous);
    }
}
