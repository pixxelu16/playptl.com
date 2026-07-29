<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        // Backfill existing group_cards category_id matching tag column
        $categories = DB::table('categories')->get();
        foreach ($categories as $cat) {
            $catName = strtolower($cat->name);
            $tags = match ($catName) {
                'singles', 'single' => ['single', 'singles'],
                'doubles', 'double' => ['doubles', 'double'],
                default => [$catName],
            };

            DB::table('group_cards')
                ->whereIn(DB::raw('LOWER(tag)'), $tags)
                ->whereNull('category_id')
                ->update(['category_id' => $cat->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
