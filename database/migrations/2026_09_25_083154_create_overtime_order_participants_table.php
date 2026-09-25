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
        Schema::create('overtime_order_participants', function (Blueprint $table) {
            $table->id();
            $table->uuid('overtime_order_id');
            $table->unsignedInteger('employee_id');
            $table->boolean('meal_allowance')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('overtime_order_id')->references('id')->on('overtime_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_order_participants');
    }
};
