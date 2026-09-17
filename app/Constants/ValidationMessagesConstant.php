<?php

namespace App\Constants;

class ValidationMessagesConstant
{
    public const REQUIRED = 'O campo :attribute é obrigatório.';
    public const STRING = 'O campo :attribute deve ser um texto.';
    public const NUMERIC = 'O campo :attribute deve ser numérico.';
    public const BOOLEAN = 'O campo :attribute deve ser verdadeiro ou falso.';
    public const MAX = 'O campo :attribute excede o tamanho permitido.';
    public const MIN = 'O campo :attribute é menor que o permitido.';
}
