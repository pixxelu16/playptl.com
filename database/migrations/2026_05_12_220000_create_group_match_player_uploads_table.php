<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Player-uploaded images for a scheduled group match (stored with upload calendar date + optional notes).
     */
    public function up(): void
    {
        Schema::create('group_match_player_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_match_id')->constrained('group_matches')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('upload_date');
            $table->string('image_path', 512);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['group_match_id', 'upload_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_match_player_uploads');
    }
};
