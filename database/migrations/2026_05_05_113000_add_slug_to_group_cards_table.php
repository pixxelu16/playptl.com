<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('group_cards')
            ->orderBy('id')
            ->select(['id', 'name'])
            ->get()
            ->each(function (object $groupCard): void {
                $baseSlug = Str::slug((string) $groupCard->name);
                $slug = $baseSlug !== '' ? $baseSlug : 'group-card';

                DB::table('group_cards')
                    ->where('id', $groupCard->id)
                    ->update(['slug' => $slug]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
