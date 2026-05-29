<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Charity donations from the public charity page.
     * status examples: pending, completed, failed, refunded
     */
    public function up(): void
    {
        Schema::create('charity_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('donor_name');
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state', 64);
            $table->string('zip', 20)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 32)->index();
            $table->string('transaction_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charity_donations');
    }
};
