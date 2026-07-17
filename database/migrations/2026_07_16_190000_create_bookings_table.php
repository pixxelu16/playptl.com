<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->enum('provider_type', ['mentor', 'coach']);

            // Booking details
            $table->text('message')->nullable();
            $table->string('student_location')->nullable();
            $table->string('student_phone')->nullable();

            // Date & hours
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('hours_per_day', 5, 2);
            $table->unsignedInteger('total_days');
            $table->decimal('total_hours', 8, 2);

            // Pricing snapshot (taken at booking time)
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);   // % e.g. 20.00
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('provider_amount', 10, 2)->default(0);  // total - commission

            // Status
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])
                  ->default('pending');

            // Stripe
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_refund_id')->nullable();

            // Payout (admin manual tracking)
            $table->enum('payout_status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('payout_paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
