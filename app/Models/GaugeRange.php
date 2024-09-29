<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaugeRange extends Model
{
    use HasFactory;
    protected $fillable = ['widget_id', 'min_value', 'max_value'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
