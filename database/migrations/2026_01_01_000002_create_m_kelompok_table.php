<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_kelompok', function (Blueprint $table) {
            $table->increments('ID_KELOMPOK');
            $table->string('NAMA_KELOMPOK', 100);
            $table->string('KETERANGAN', 255)->nullable();
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_kelompok');
    }
};