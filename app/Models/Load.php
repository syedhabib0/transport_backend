<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'load_type',
        'weight',
        'destination',
        'delivery_date',
        'status',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function documents()
    {
        return $this->hasMany(LoadDocument::class);
    }
}
