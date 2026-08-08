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
        Schema::create('rule_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_number')->default('2.3');
            $table->string('last_updated')->default('August 1, 2026');
            $table->text('changelog')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });

        Schema::create('rule_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_section_id')->constrained('rule_sections')->onDelete('cascade');
            $table->string('item_number')->nullable();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_highlighted')->default(false);
            $table->string('highlight_type')->default('info'); // info, warning, important, success
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('rule_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_faqs');
        Schema::dropIfExists('rule_items');
        Schema::dropIfExists('rule_sections');
        Schema::dropIfExists('rule_versions');
    }
};
