<?php

use App\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Duas rotas para exercitar a arquitetura de logs: uma de sucesso e uma
| que atravessa integração externa e pode falhar.
|
*/

Route::group(['middleware' => ['request.context']], function () {
    Route::post('/accounts', [AccountController::class, 'store'])->name('account.store');
    Route::post('/accounts/{account}/close', [AccountController::class, 'close'])->name('account.close');
});
