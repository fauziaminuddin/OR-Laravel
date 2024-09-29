<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUser extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'serviceuser_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

