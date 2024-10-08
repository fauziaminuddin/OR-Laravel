<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'user_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
    public function collaborators()
    {
        return $this->hasMany(Collaborator::class);
    }
    
}
