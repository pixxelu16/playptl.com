<?php

use App\Enums\GroupMatchFormat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singles: store winning player's user id. Doubles: null (team win is winner_side only).
     */
    public function up(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->foreignId('winner_user_id')
                ->nullable()
                ->after('winner_side')
                ->constrained('users')
                ->nullOnDelete();
        });

        foreach (DB::table('group_matches')->select(['id', 'format', 'winner_side', 'home_user_id', 'away_user_id'])->cursor() as $row) {
            if ($row->format !== GroupMatchFormat::Singles->value) {
                continue;
            }
            if ($row->winner_side === 'home') {
                DB::table('group_matches')->where('id', $row->id)->update(['winner_user_id' => (int) $row->home_user_id]);
            } elseif ($row->winner_side === 'away') {
                DB::table('group_matches')->where('id', $row->id)->update(['winner_user_id' => (int) $row->away_user_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_user_id');
        });
    }
};
