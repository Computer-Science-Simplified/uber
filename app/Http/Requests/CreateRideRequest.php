<?php

namespace App\Http\Requests;

use App\Models\Car;
use App\ValueObjects\Location;
use Illuminate\Foundation\Http\FormRequest;

class CreateRideRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
        ];
    }

    public function getLocation(): Location
    {
        return Location::create(
            $this->longitude,
            $this->latitude,
        );
    }
}
