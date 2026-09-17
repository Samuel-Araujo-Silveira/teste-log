<?php

namespace App\Http\Requests\Account;

use App\Constants\ValidationMessagesConstant;
use Illuminate\Foundation\Http\FormRequest;

class AccountStorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'total' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => ValidationMessagesConstant::REQUIRED,
            'name.string' => ValidationMessagesConstant::STRING,
            'name.max' => ValidationMessagesConstant::MAX,
            'total.numeric' => ValidationMessagesConstant::NUMERIC,
            'total.min' => ValidationMessagesConstant::MIN,
        ];
    }
}
