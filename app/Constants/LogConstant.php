<?php

namespace App\Constants;

class LogConstant
{
    public const CORRELATION_ID_HEADER = 'X-Correlation-ID';

    public const TYPE_HTTP = 'http';
    public const TYPE_COMMAND = 'command';

    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
}
