<?php

namespace App\Services;

use App\Constants\AuditActionConstant;
use App\Constants\LogComponentConstant;
use App\Constants\LogConstant;
use App\Constants\LogEventConstant;
use App\Exceptions\CustomExceptions\PaymentNotAuthorizedException;
use App\Models\Account;

class PayGatewayService
{
    public function charge(Account $account, array $content)
    {
        $response = $this->send($account, $content);

        if (!$response['success']) {
            logDiagnostic(
                LogConstant::LEVEL_ERROR,
                'Cobrança recusada pelo gateway de pagamento',
                LogEventConstant::INTEGRATION_PAY_CHARGE_FAILED,
                LogComponentConstant::PAY_GATEWAY_SERVICE,
                [
                    'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                    'entity_id' => $account->id,
                    'http_status' => $response['status'],
                    'payment_method' => $content['payment_method'],
                ]
            );

            throw new PaymentNotAuthorizedException();
        }

        logDiagnostic(
            LogConstant::LEVEL_INFO,
            'Cobrança autorizada pelo gateway de pagamento',
            LogEventConstant::INTEGRATION_PAY_CHARGE_SUCCEEDED,
            LogComponentConstant::PAY_GATEWAY_SERVICE,
            [
                'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                'entity_id' => $account->id,
                'http_status' => $response['status'],
                'payment_method' => $content['payment_method'],
            ]
        );

        return $response;
    }

    /**
     * Simula a chamada HTTP ao gateway. O header de correlação é montado
     * aqui para demonstrar a propagação do ID para fora da aplicação.
     */
    private function send(Account $account, array $content)
    {
        $headers = [
            LogConstant::CORRELATION_ID_HEADER => request()->correlation_id,
        ];

        $authorized = empty($content['force_gateway_failure']);

        return [
            'success' => $authorized,
            'status' => $authorized ? 201 : 502,
            'headers' => $headers,
            'external_reference' => $account->uuid,
        ];
    }
}
