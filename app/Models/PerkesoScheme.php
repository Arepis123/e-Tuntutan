<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkesoScheme extends Model
{
    protected $fillable = ['value', 'label', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function ordered()
    {
        return static::orderBy('sort_order')->orderBy('id');
    }

    public static function active()
    {
        return static::ordered()->where('is_active', true);
    }
}
