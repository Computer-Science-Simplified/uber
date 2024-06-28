<?php

namespace App\Http\Requests;

use App\Models\Car;
use App\Models\Driver;
use App\ValueObjects\Location;
use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'driver_id' => ['required', 'exists:drivers,id'],
            'car_id' => ['required', 'exists:cars,id'],
        ];
    }

    public function getDriver(): Driver
    {
        return Driver::findOrFail($this->driver_id);
    }

    public function getCar(): Car
    {
        return Car::findOrFail($this->car_id);
    }
}
