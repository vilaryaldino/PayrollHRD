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
        Schema::table('t_register_lembur', function (Blueprint $table) {
            if (!Schema::hasColumn('t_register_lembur', 'kategori')) {
                $table->string('kategori', 50)->default('Harian')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_register_lembur', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
