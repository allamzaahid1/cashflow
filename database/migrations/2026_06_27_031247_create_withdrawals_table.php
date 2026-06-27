<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            $table->decimal('admin_fee', 15, 2)
                ->default(0);

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->date('withdrawal_date');

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
