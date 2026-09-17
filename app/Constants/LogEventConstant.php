<?php

namespace App\Constants;

class LogEventConstant
{
    public const ACCOUNT_OPEN_STARTED = 'account.open.started';
    public const ACCOUNT_OPENED = 'account.opened';

    public const ACCOUNT_CLOSE_STARTED = 'account.close.started';
    public const ACCOUNT_CLOSED = 'account.closed';
    public const ACCOUNT_CLOSE_REJECTED = 'account.close.rejected';

    public const INTEGRATION_PAY_CHARGE_SUCCEEDED = 'integration.pay.charge.succeeded';
    public const INTEGRATION_PAY_CHARGE_FAILED = 'integration.pay.charge.failed';
}
