<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_match_player_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('group_match_id')->nullable()->change();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_match_player_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('group_match_id')->nullable(false)->change();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable(false)->change();
        });
    }
};
