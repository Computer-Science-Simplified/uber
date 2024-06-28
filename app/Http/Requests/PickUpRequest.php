<?php

namespace App\Http\Requests;

use App\Models\Driver;
use Illuminate\Foundation\Http\FormRequest;

class PickUpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'driver_id' => ['required', 'exists:drivers,id'],
        ];
    }

    public function getDriver(): Driver
    {
        return Driver::findOrFail($this->driver_id);
    }
}
