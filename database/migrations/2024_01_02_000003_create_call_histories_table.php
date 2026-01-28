<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_data_id')->constrained('renewal_data')->onDelete('cascade');
            $table->foreignId('called_by')->constrained('users');
            $table->datetime('called_at');
            $table->enum('result', [
                'answered', 'no_answer', 'busy', 'voicemail', 'wrong_number',
                'callback_requested', 'interested', 'renewed', 'declined'
            ]);
            $table->text('notes')->nullable();
            $table->datetime('callback_scheduled')->nullable();
            $table->timestamps();
            
            $table->index(['renewal_data_id', 'called_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_histories');
    }
};
