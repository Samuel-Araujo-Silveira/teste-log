<?php

namespace App\Exceptions\CustomExceptions;

use App\Constants\ExceptionCodeConstant;
use App\Constants\ExceptionMessageConstant;

class AccountAlreadyClosedException extends CustomException
{
    public function __construct()
    {
        $this->message = ExceptionMessageConstant::ACCOUNT_ALREADY_CLOSED;
        $this->code = ExceptionCodeConstant::SHOW_ERROR_MESSAGE;

        parent::__construct();
    }
}
