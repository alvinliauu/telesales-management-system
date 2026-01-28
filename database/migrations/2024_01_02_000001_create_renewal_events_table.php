<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event_type')->default('renewal');
            $table->year('year');
            $table->tinyInteger('month');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['event_type', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_events');
    }
};
