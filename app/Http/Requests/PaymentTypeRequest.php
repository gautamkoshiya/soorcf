<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Name' => 'required|unique:payment_types'
        ];
    }

    public function messages()
    {
        return [
            'Name.unique' => 'Already Exist.',
            'Name.required' => 'Name Cannot be empty.',
        ];
    }
}
