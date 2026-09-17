<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;

class UseJsonFormatter
{
    /**
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());

            // Monolog executa os processors na ordem inversa do push:
            // o RequestContextProcessor precisa rodar antes para que o
            // RedactionProcessor consiga mascarar o que ele adicionou.
            $handler->pushProcessor(new RedactionProcessor());
            $handler->pushProcessor(new RequestContextProcessor());
        }
    }
}
