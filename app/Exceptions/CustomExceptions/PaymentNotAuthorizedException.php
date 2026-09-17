<?php

namespace App\Exceptions\CustomExceptions;

use App\Constants\ExceptionCodeConstant;
use App\Constants\ExceptionMessageConstant;

class PaymentNotAuthorizedException extends CustomException
{
    public function __construct()
    {
        $this->message = ExceptionMessageConstant::PAYMENT_NOT_AUTHORIZED;
        $this->code = ExceptionCodeConstant::SHOW_ERROR_MESSAGE;

        parent::__construct();
    }
}
