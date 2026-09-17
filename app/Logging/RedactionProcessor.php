<?php

namespace App\Logging;

class RedactionProcessor
{
    public const REDACTED = '[REDACTED]';

    private $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'cookie',
        'secret',
        'api_key',
        'uuid',
        'card',
        'credit_card',
        'pan',
        'cvv',
        'cvv2',
        'pin',
        'cpf',
        'cnpj',
    ];

    public function __invoke(array $record)
    {
        $record['context'] = $this->redact($record['context']);
        $record['extra'] = $this->redact($record['extra']);

        return $record;
    }

    private function redact(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redact($value);

                continue;
            }

            if (in_array(strtolower((string) $key), $this->sensitiveKeys, true)) {
                $data[$key] = self::REDACTED;
            }
        }

        return $data;
    }
}
