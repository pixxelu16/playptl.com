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
        Schema::table('leagues', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->unique();
        });

        $taken = [];

        DB::table('leagues')
            ->orderBy('id')
            ->select(['id', 'name'])
            ->get()
            ->each(function (object $league) use (&$taken): void {
                $baseSlug = Str::slug((string) $league->name);
                $baseSlug = $baseSlug !== '' ? $baseSlug : 'league';
                $slug = $baseSlug;
                $counter = 2;

                while (in_array($slug, $taken, true) || DB::table('leagues')->where('slug', $slug)->where('id', '!=', $league->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                DB::table('leagues')
                    ->where('id', $league->id)
                    ->update(['slug' => $slug]);

                $taken[] = $slug;
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
