<?php

namespace App\Http\Requests;

use App\Models\Car;
use App\Models\Driver;
use App\ValueObjects\Location;
use Illuminate\Foundation\Http\FormRequest;

class DropOffRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'driver_id' => ['required', 'exists:drivers,id'],
            'car_id' => ['required', 'exists:cars,id'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
        ];
    }

    public function getDriver(): Driver
    {
        return Driver::findOrFail($this->driver_id);
    }

    public function getLocation(): Location
    {
        return Location::create(
            $this->longitude,
            $this->latitude,
        );
    }
}
