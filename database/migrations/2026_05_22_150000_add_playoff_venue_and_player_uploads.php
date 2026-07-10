<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('playoff_matches')) {
            Schema::table('playoff_matches', function (Blueprint $table) {
                if (! Schema::hasColumn('playoff_matches', 'venue')) {
                    $table->string('venue', 255)->nullable()->after('start_time');
                }
                if (! Schema::hasColumn('playoff_matches', 'court')) {
                    $table->string('court', 64)->nullable()->after('venue');
                }
            });
        }

        if (! Schema::hasTable('playoff_match_player_uploads')) {
            Schema::create('playoff_match_player_uploads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('playoff_match_id')->constrained('playoff_matches')->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->date('upload_date');
                $table->string('image_path', 512);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['playoff_match_id', 'upload_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_match_player_uploads');

        if (Schema::hasTable('playoff_matches')) {
            Schema::table('playoff_matches', function (Blueprint $table) {
                foreach (['court', 'venue'] as $col) {
                    if (Schema::hasColumn('playoff_matches', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
