<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'car_id' => ['required', 'exists:cars,id'],
        ];
    }
}
