<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'note',
        'address',
        'delivery_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
