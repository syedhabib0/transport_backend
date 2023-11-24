<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'front_image',
        'back_image',
        'left_image',
        'right_image',
        'cargo_image',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
