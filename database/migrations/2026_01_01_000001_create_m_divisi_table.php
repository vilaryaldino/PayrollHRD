<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_divisi', function (Blueprint $table) {
            $table->increments('ID_DIVISI');
            $table->string('NAMA_DIVISI', 100)->unique('UQ_DIVISI_NAMA');
            $table->string('KETERANGAN', 255)->nullable();
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_divisi');
    }
};