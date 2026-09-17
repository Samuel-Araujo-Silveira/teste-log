<?php

namespace App\Services;

use App\Constants\AuditActionConstant;
use App\Constants\LogComponentConstant;
use App\Constants\LogConstant;
use App\Constants\LogEventConstant;
use App\Constants\StatusAccountConstant;
use App\Exceptions\CustomExceptions\AccountAlreadyClosedException;
use App\Exceptions\CustomExceptions\AccountNotFoundException;
use App\Repositories\AccountRepository;
use Illuminate\Support\Str;

class AccountService
{
    private $accountRepository;
    private $auditService;
    private $payGatewayService;

    public function __construct()
    {
        $this->accountRepository = new AccountRepository();
        $this->auditService = new AuditService();
        $this->payGatewayService = new PayGatewayService();
    }

    public function store(array $content)
    {
        logDiagnostic(
            LogConstant::LEVEL_INFO,
            'Abertura de conta iniciada',
            LogEventConstant::ACCOUNT_OPEN_STARTED,
            LogComponentConstant::ACCOUNT_SERVICE
        );

        $account = $this->accountRepository->add([
            'uuid' => (string) Str::uuid(),
            'name' => $content['name'],
            'establishment_id' => request()->establishment_id,
            'status_account_id' => StatusAccountConstant::OPEN,
            'total' => $content['total'] ?? 0,
        ]);

        logDiagnostic(
            LogConstant::LEVEL_INFO,
            'Conta aberta',
            LogEventConstant::ACCOUNT_OPENED,
            LogComponentConstant::ACCOUNT_SERVICE,
            [
                'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                'entity_id' => $account->id,
            ]
        );

        $this->auditService->register(
            AuditActionConstant::ACCOUNT_OPENED,
            AuditActionConstant::RESULT_SUCCESS,
            AuditActionConstant::SUBJECT_ACCOUNT,
            $account->id,
            ['total' => $account->total]
        );

        return $account;
    }

    public function close($accountId, array $content)
    {
        $account = $this->accountRepository
            ->byParams(['id' => $accountId])
            ->first();

        if (!$account) {
            throw new AccountNotFoundException();
        }

        logDiagnostic(
            LogConstant::LEVEL_INFO,
            'Fechamento de conta iniciado',
            LogEventConstant::ACCOUNT_CLOSE_STARTED,
            LogComponentConstant::ACCOUNT_SERVICE,
            [
                'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                'entity_id' => $account->id,
                'payment_method' => $content['payment_method'],
            ]
        );

        if ($account->status_account_id == StatusAccountConstant::CLOSED) {
            logDiagnostic(
                LogConstant::LEVEL_WARNING,
                'Fechamento recusado: conta já fechada',
                LogEventConstant::ACCOUNT_CLOSE_REJECTED,
                LogComponentConstant::ACCOUNT_SERVICE,
                [
                    'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                    'entity_id' => $account->id,
                ]
            );

            throw new AccountAlreadyClosedException();
        }

        $this->payGatewayService->charge($account, $content);

        $this->accountRepository
            ->byParams(['id' => $account->id])
            ->update([
                'status_account_id' => StatusAccountConstant::CLOSED,
            ]);

        logDiagnostic(
            LogConstant::LEVEL_INFO,
            'Conta fechada',
            LogEventConstant::ACCOUNT_CLOSED,
            LogComponentConstant::ACCOUNT_SERVICE,
            [
                'entity_type' => AuditActionConstant::SUBJECT_ACCOUNT,
                'entity_id' => $account->id,
                'payment_method' => $content['payment_method'],
            ]
        );

        $this->auditService->register(
            AuditActionConstant::ACCOUNT_CLOSED,
            AuditActionConstant::RESULT_SUCCESS,
            AuditActionConstant::SUBJECT_ACCOUNT,
            $account->id,
            [
                'total' => $account->total,
                'payment_method' => $content['payment_method'],
            ]
        );

        return $this->accountRepository
            ->byParams(['id' => $account->id])
            ->first();
    }
}
