<?php

use App\Support\MatchScoreReader;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist match outcome: home or away side won (singles or doubles team).
     */
    public function up(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->string('winner_side', 8)->nullable()->after('score');
        });

        foreach (DB::table('group_matches')->select(['id', 'score'])->whereNotNull('score')->where('score', '!=', '')->cursor() as $row) {
            $parsed = MatchScoreReader::homeSideWon(trim((string) $row->score));
            if ($parsed !== null) {
                DB::table('group_matches')->where('id', $row->id)->update([
                    'winner_side' => $parsed ? 'home' : 'away',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->dropColumn('winner_side');
        });
    }
};
