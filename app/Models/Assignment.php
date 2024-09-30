<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id', 'title', 'note', 'file_path', 'dashboard', 'user_id'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function replies()
{
    return $this->hasMany(Reply::class);
}


}
