<?php

namespace App\Exceptions\CustomExceptions;

use App\Constants\ExceptionCodeConstant;
use App\Constants\ExceptionMessageConstant;

class AccountNotFoundException extends CustomException
{
    public function __construct()
    {
        $this->message = ExceptionMessageConstant::ACCOUNT_NOT_FOUND;
        $this->code = ExceptionCodeConstant::SHOW_ERROR_MESSAGE;

        parent::__construct();
    }
}
