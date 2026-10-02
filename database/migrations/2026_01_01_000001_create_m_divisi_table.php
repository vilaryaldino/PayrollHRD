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
        if (!Schema::hasTable('m_divisi')) {
            Schema::create('m_divisi', function (Blueprint $table) {
                $table->increments('ID_DIVISI'); // Primary Key int(10) UNSIGNED AUTO_INCREMENT
                $table->string('NAMA_DIVISI', 100)->unique();
                $table->string('KETERANGAN', 255)->nullable();
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_divisi');
    }
};