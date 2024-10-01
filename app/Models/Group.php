<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    // The attributes that are mass assignable.
    protected $fillable = [
        'name',
        'classroom_id',
    ];

    // Define the relationship to the Classroom model
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
