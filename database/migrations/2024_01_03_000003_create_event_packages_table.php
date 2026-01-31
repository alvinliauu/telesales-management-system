<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Packages for each event
        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_event_id')->constrained('renewal_events')->onDelete('cascade');
            $table->string('name'); // "TLO Basic", "Comprehensive Plus"
            $table->string('code')->nullable(); // "tlo_basic", "comp_plus"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['renewal_event_id', 'name']);
        });

        // Extensions in each package with rates per car type
        Schema::create('event_package_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_package_id')->constrained('event_packages')->onDelete('cascade');
            $table->foreignId('extension_id')->constrained('extensions')->onDelete('cascade');
            $table->foreignId('car_type_id')->constrained('car_types')->onDelete('cascade');
            $table->enum('rate_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('rate_value', 18, 4)->default(0); // e.g., 2.5 for 2.5% or 500000 for flat
            $table->timestamps();

            $table->unique(['event_package_id', 'extension_id', 'car_type_id'], 'pkg_ext_car_unique');
        });

        // Car types available for each event
        Schema::create('event_car_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_event_id')->constrained('renewal_events')->onDelete('cascade');
            $table->foreignId('car_type_id')->constrained('car_types')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['renewal_event_id', 'car_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_car_types');
        Schema::dropIfExists('event_package_extensions');
        Schema::dropIfExists('event_packages');
    }
};
