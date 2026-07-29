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
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('menu_order')->default(0)->after('name');
        });

        // Set default orders: Singles = 1, Doubles = 2, Mixed = 3, Youth = 4
        \Illuminate\Support\Facades\DB::table('categories')->where('name', 'Singles')->update(['menu_order' => 1]);
        \Illuminate\Support\Facades\DB::table('categories')->where('name', 'Doubles')->update(['menu_order' => 2]);
        \Illuminate\Support\Facades\DB::table('categories')->where('name', 'Mixed')->update(['menu_order' => 3]);
        \Illuminate\Support\Facades\DB::table('categories')->where('name', 'Youth')->update(['menu_order' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('menu_order');
        });
    }
};
