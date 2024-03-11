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

    public function getFrontImageAttribute()
    {
        return isset($this->attributes['front_image'])
        ? asset('storage/' . $this->attributes['front_image'])
        : null;
    }

    public function getBackImageAttribute()
    {
        return isset($this->attributes['back_image'])
        ? asset('storage/' . $this->attributes['back_image'])
        : null;
    }

    public function getLeftImageAttribute()
    {
        return isset($this->attributes['left_image'])
        ? asset('storage/' . $this->attributes['left_image'])
        : null;
    }

    public function getRightImageAttribute()
    {
        return isset($this->attributes['right_image'])
        ? asset('storage/' . $this->attributes['right_image'])
        : null;
    }

    public function getCargoImageAttribute()
    {
        return isset($this->attributes['cargo_image'])
        ? asset('storage/' . $this->attributes['cargo_image'])
        : null;
    }
}
