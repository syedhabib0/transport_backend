<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'document_type',
        'document_path',
    ];

    public function loads()
    {
        return $this->belongsTo(Load::class);
    }
}
