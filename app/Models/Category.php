<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable  = [
        'name',
        'slug',
        'type',
    ];


    public function setNameAttribute($value) {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }


    public function enterprise()
    {
        return $this->hasMany(Enterprise::class);
    }

}
