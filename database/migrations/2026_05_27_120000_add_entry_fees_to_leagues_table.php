<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            if (! Schema::hasColumn('leagues', 'singles_entry_fee_cents')) {
                $table->unsignedInteger('singles_entry_fee_cents')
                    ->default((int) config('services.stripe.singles_amount_cents', 3000))
                    ->after('type');
            }
            if (! Schema::hasColumn('leagues', 'doubles_entry_fee_cents')) {
                $table->unsignedInteger('doubles_entry_fee_cents')
                    ->default((int) config('services.stripe.doubles_amount_cents', 4500))
                    ->after('singles_entry_fee_cents');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('leagues', 'singles_entry_fee_cents')) {
                $columns[] = 'singles_entry_fee_cents';
            }
            if (Schema::hasColumn('leagues', 'doubles_entry_fee_cents')) {
                $columns[] = 'doubles_entry_fee_cents';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
