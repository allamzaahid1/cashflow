<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    /** @use HasFactory<\Database\Factories\WithdrawalFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'payment_method_id',
        'amount',
        'admin_fee',
        'status',
        'withdrawal_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'withdrawal_date' => 'date',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
