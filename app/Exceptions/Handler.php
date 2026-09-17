<?php

namespace App\Exceptions;

use App\Constants\ExceptionCodeConstant;
use App\Constants\ExceptionMessageConstant;
use App\Exceptions\CustomExceptions\CustomException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Erros de negócio já são registrados pelos services com o evento
     * correspondente, então não precisam do report genérico do framework.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        CustomException::class,
    ];

    /**
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->renderable(function (Throwable $throwable) {
            return $this->handleException($throwable);
        });
    }

    private function handleException(Throwable $throwable)
    {
        if ($throwable instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'errors' => $throwable->validator->errors()->getMessages(),
                'error' => [
                    'message' => ExceptionMessageConstant::VALIDATION_ERROR,
                    'code' => ExceptionCodeConstant::SHOW_ERROR_MESSAGE,
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($throwable instanceof CustomException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $throwable->getMessage(),
                    'code' => $throwable->getCode(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'message' => ExceptionMessageConstant::INTERNAL_ERROR,
                'code' => ExceptionCodeConstant::GENERIC_ERROR,
            ],
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
