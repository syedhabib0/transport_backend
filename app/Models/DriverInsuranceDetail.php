<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverInsuranceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'insurance_number',
        'insurance_expiry_date',
        'insurance_photo_front',
        'insurance_photo_back',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
