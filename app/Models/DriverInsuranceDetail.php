<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverInsuranceDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function getInsurancePhotoFrontAttribute()
    {
        return isset($this->attributes['insurance_photo_front'])
        ? asset('storage/' . $this->attributes['insurance_photo_front'])
        : null;
    }

    public function getInsurancePhotoBackAttribute()
    {
        return isset($this->attributes['insurance_photo_back'])
        ? asset('storage/' . $this->attributes['insurance_photo_back'])
        : null;
    }
}
