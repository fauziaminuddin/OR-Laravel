<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAsset extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'asset_id', 'asset_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function asset()
    {
        return $this->hasMany(Widget::class, 'asset_id', 'asset_id');
    }
}
