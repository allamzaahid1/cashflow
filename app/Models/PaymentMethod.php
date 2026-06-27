<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'type',
        'account_name',
        'account_number',
        'qr_image',
        'is_active',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}
