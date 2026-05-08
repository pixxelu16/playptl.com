<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registration form fields (Singles/Doubles primary player) + partner (Doubles)
     * and latest payment snapshot on the user row.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('city')->nullable()->after('phone');
            $table->string('state', 64)->nullable()->after('city');
            $table->string('age_group', 32)->nullable()->after('state');
            $table->string('skill_level', 32)->nullable()->after('age_group');
            $table->string('sex', 32)->nullable()->after('skill_level');
            $table->foreignId('league_id')->nullable()->after('sex')->constrained('leagues')->nullOnDelete();
            $table->string('registration_type', 16)->nullable()->after('league_id'); // singles | doubles

            $table->string('partner_first_name')->nullable()->after('registration_type');
            $table->string('partner_last_name')->nullable()->after('partner_first_name');
            $table->string('partner_email')->nullable()->after('partner_last_name');
            $table->string('partner_phone', 32)->nullable()->after('partner_email');

            $table->string('payment_status', 32)->nullable()->default('pending')->after('partner_phone');
            $table->string('transaction_id')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['league_id']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'city',
                'state',
                'age_group',
                'skill_level',
                'sex',
                'league_id',
                'registration_type',
                'partner_first_name',
                'partner_last_name',
                'partner_email',
                'partner_phone',
                'payment_status',
                'transaction_id',
            ]);
        });
    }
};
