<?php

use Illuminate\Support\Facades\Log;

/**
 * Registra um log de diagnóstico seguindo a taxonomia do projeto:
 * event (o que aconteceu) + component (onde no código).
 * O correlation_id vem do contexto do Monolog e os demais atributos
 * de execução vêm do RequestContextProcessor.
 */
function logDiagnostic($level, $message, $event, $component, array $context = [])
{
    Log::log($level, $message, array_merge([
        'event' => $event,
        'component' => $component,
    ], $context));
}
