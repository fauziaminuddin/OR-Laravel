<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use HasFactory;
    protected $fillable = [
        'dashboard_id',
        'widget_name',
        'asset_id',
        'attribute_name',
        'type'
    ];

    public function dashboard()
    {
        return $this->belongsTo(AttributeDashboard::class, 'dashboard_id');
    }
     // Add this method to define the relationship with UserAsset
     public function asset()
     {
         return $this->belongsTo(UserAsset::class, 'asset_id', 'asset_id');
     }
     public function widget()
    {
        return $this->hasMany(GaugeRange::class);
    }
}
