<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'type',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}