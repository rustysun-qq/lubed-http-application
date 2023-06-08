<?php
namespace Lubed\HttpApplication;

use Throwable;
use Lubed\Exceptions\RuntimeException;

final class AppExceptions
{
    const APP_INIT_FAILED=101201;
    const DISPATCH_FAILED=101202;

    public static function dispatchFailed(
        string $message = "",
        $options = [],
        ?Throwable $previous = null) {
        throw new RuntimeException(self::DISPATCH_FAILED, $message, $options, $previous);
    }

    public static function startFailed(
        string $message = "",
        $options = [],
        ?Throwable $previous = null) {
        throw new RuntimeException(self::APP_INIT_FAILED, $message, $options, $previous);
    }
}
