<?php
namespace Lubed\HttpApplication;

use Throwable;
use Lubed\Exceptions\RuntimeException;

final class AppExceptions
{
    const APP_INIT_FAILED=101201;

    public static function startFailed(
        string $message = "",
        $options = [],
        Throwable $previous = null) {
        throw new RuntimeException(self::APP_INIT_FAILED, $message, $options, $previous);
    }
}
