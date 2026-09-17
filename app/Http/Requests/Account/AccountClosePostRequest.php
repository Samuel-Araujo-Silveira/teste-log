<?php

namespace App\Http\Requests\Account;

use App\Constants\ValidationMessagesConstant;
use Illuminate\Foundation\Http\FormRequest;

class AccountClosePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_method' => 'required|string|max:30',
            'force_gateway_failure' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => ValidationMessagesConstant::REQUIRED,
            'payment_method.string' => ValidationMessagesConstant::STRING,
            'payment_method.max' => ValidationMessagesConstant::MAX,
            'force_gateway_failure.boolean' => ValidationMessagesConstant::BOOLEAN,
        ];
    }
}
