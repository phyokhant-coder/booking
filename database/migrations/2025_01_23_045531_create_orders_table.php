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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('billing_address_id')->nullable()->constrained('billing_addresses')->onDelete('cascade');
            $table->string('total_amount')->nullable();
            $table->enum('status', [
                'PENDING',
                'CONFIRMED',
                'CANCEL',
                'SHIPPED',
                'DELIVERED'
            ])->default('PENDING')->nullable();
            $table->string('order_note')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
