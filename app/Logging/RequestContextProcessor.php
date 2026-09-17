<?php

namespace App\Logging;

use App\Constants\LogConstant;

class RequestContextProcessor
{
    public function __invoke(array $record)
    {
        if (app()->runningInConsole()) {
            $record['extra']['type'] = LogConstant::TYPE_COMMAND;

            return $record;
        }

        $request = request();

        $record['extra']['type'] = LogConstant::TYPE_HTTP;
        $record['extra']['user_id'] = $request->user_id;
        $record['extra']['establishment_id'] = $request->establishment_id;
        $record['extra']['http'] = [
            'method' => $request->method(),
            'path' => $request->path(),
        ];

        return $record;
    }
}
