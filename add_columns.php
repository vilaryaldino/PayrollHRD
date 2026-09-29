<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

try {
    Schema::table('t_data_absensi', function (Blueprint $table) {
        $table->string('departemen')->nullable();
        $table->string('posisi')->nullable();
        $table->string('sn_perangkat')->nullable();
        $table->string('status')->nullable();
        $table->string('keterangan')->nullable();
        $table->string('method')->nullable();
    });
    echo "Columns added successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
