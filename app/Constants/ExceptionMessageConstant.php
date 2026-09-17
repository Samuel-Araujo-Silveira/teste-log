<?php

namespace App\Constants;

class ExceptionMessageConstant
{
    public const UNAUTHORIZED = 'Usuário não autenticado!';
    public const INTERNAL_ERROR = 'Erro interno!';
    public const VALIDATION_ERROR = 'Dados inválidos!';

    public const ACCOUNT_NOT_FOUND = 'Conta não encontrada!';
    public const ACCOUNT_ALREADY_CLOSED = 'Esta conta já está fechada!';
    public const PAYMENT_NOT_AUTHORIZED = 'Pagamento não autorizado!';
}
