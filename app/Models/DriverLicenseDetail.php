<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicenseDetail extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function getLicensePhotoFrontAttribute()
    {
        return isset($this->attributes['license_photo_front'])
        ? asset('storage/' . $this->attributes['license_photo_front'])
        : null;
    }

    public function getLicensePhotoBackAttribute()
    {
        return isset($this->attributes['license_photo_back'])
        ? asset('storage/' . $this->attributes['license_photo_back'])
        : null;
    }
}
