<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('paid_after', function (Blueprint $table) {
            $table->id();
            $table->date('committed_date');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('amount_paid', 10, 2);
            $table->unsignedBigInteger('payment_type');
            $table->unsignedBigInteger('account_id');
            $table->text('comment')->nullable();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('create_user');
            $table->unsignedBigInteger('debt_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('sales');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('payment_type')->references('id')->on('payment_methods');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('create_user')->references('id')->on('users');
            $table->foreign('debt_id')->references('id')->on('debts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_after');
    }
};
