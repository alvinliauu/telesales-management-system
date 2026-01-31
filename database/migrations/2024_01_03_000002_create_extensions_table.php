<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // TLO, COMPREHENSIVE, etc.
            $table->string('code')->unique(); // tlo, comprehensive, etc.
            $table->text('description')->nullable();
            $table->boolean('is_main_coverage')->default(false); // true for TLO & COMPREHENSIVE
            $table->integer('max_vehicle_age')->nullable(); // e.g., 5 for AUTHORIZED WORKSHOP
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extensions');
    }
};
