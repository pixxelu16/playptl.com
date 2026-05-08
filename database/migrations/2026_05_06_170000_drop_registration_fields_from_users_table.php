<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'league_id')) {
                $table->dropForeign(['league_id']);
            }

            $columns = [
                'partner_first_name',
                'partner_last_name',
                'partner_email',
                'partner_phone',
                'payment_status',
                'league_id',
                'age_group',
                'skill_level',
            ];

            $existing = array_values(array_filter($columns, fn ($c) => Schema::hasColumn('users', $c)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'age_group')) {
                $table->string('age_group', 32)->nullable()->after('state');
            }
            if (! Schema::hasColumn('users', 'skill_level')) {
                $table->string('skill_level', 32)->nullable()->after('age_group');
            }
            if (! Schema::hasColumn('users', 'league_id')) {
                $table->foreignId('league_id')->nullable()->after('sex')->constrained('leagues')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'partner_first_name')) {
                $table->string('partner_first_name')->nullable()->after('registration_type');
            }
            if (! Schema::hasColumn('users', 'partner_last_name')) {
                $table->string('partner_last_name')->nullable()->after('partner_first_name');
            }
            if (! Schema::hasColumn('users', 'partner_email')) {
                $table->string('partner_email')->nullable()->after('partner_last_name');
            }
            if (! Schema::hasColumn('users', 'partner_phone')) {
                $table->string('partner_phone', 32)->nullable()->after('partner_email');
            }
            if (! Schema::hasColumn('users', 'payment_status')) {
                $table->string('payment_status', 32)->nullable()->default('pending')->after('partner_phone');
            }
        });
    }
};

