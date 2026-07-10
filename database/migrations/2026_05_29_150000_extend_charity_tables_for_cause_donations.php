<?php

use App\Models\CharityCause;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charity_causes', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        CharityCause::query()->each(function (CharityCause $cause): void {
            if ($cause->slug !== null && $cause->slug !== '') {
                return;
            }

            $base = Str::slug((string) $cause->title);
            $base = $base !== '' ? $base : 'charity-cause-'.$cause->id;
            $slug = $base;
            $counter = 2;

            while (CharityCause::query()->where('slug', $slug)->whereKeyNot($cause->id)->exists()) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            $cause->update(['slug' => $slug]);
        });

        Schema::table('charity_donations', function (Blueprint $table) {
            $table->foreignId('charity_cause_id')->nullable()->after('user_id')->constrained('charity_causes')->nullOnDelete();
            $table->string('donation_type', 32)->default('money')->after('charity_cause_id')->index();
            $table->string('phone', 32)->nullable()->after('email');
            $table->decimal('quantity', 10, 2)->nullable()->after('amount');
            $table->string('material_detail')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('charity_donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('charity_cause_id');
            $table->dropColumn(['donation_type', 'phone', 'quantity', 'material_detail']);
        });

        Schema::table('charity_causes', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
