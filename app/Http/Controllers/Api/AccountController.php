<?php

namespace App\Http\Controllers\Api;

use App\Constants\AuditActionConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AccountClosePostRequest;
use App\Http\Requests\Account\AccountStorePostRequest;
use App\Services\AccountService;
use App\Services\AuditService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountController extends Controller
{
    private $accountService;
    private $auditService;

    public function __construct()
    {
        $this->accountService = new AccountService();
        $this->auditService = new AuditService();
    }

    public function store(AccountStorePostRequest $request)
    {
        try {
            DB::beginTransaction();

            $account = $this->accountService->store($request->validated());

            DB::commit();

            return parent::response($account, Response::HTTP_CREATED);
        } catch (Throwable $throwable) {
            DB::rollBack();

            $this->registerFailure(AuditActionConstant::ACCOUNT_OPENED, null, $throwable);

            throw $throwable;
        }
    }

    public function close(AccountClosePostRequest $request, $account)
    {
        try {
            DB::beginTransaction();

            $closedAccount = $this->accountService->close($account, $request->validated());

            DB::commit();

            return parent::response($closedAccount);
        } catch (Throwable $throwable) {
            DB::rollBack();

            $this->registerFailure(AuditActionConstant::ACCOUNT_CLOSED, $account, $throwable);

            throw $throwable;
        }
    }

    /**
     * A auditoria da falha é gravada após o rollback para não ser
     * desfeita junto com a transação do fluxo de negócio.
     */
    private function registerFailure($action, $subjectId, Throwable $throwable)
    {
        $this->auditService->register(
            $action,
            AuditActionConstant::RESULT_FAILURE,
            AuditActionConstant::SUBJECT_ACCOUNT,
            $subjectId,
            ['reason' => class_basename($throwable)]
        );
    }
}
