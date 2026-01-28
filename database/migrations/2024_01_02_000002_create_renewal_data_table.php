<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_event_id')->constrained('renewal_events')->onDelete('cascade');
            
            $table->string('no_kontrak')->index();
            $table->string('ano')->index();
            $table->date('end_date')->nullable();
            $table->string('nama_tertanggung')->nullable();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->year('tahun_kendaraan')->nullable();
            $table->decimal('nilai_pertanggungan', 18, 2)->default(0);
            $table->decimal('premi_comprehensive', 18, 2)->default(0);
            $table->decimal('premi_tlo', 18, 2)->default(0);
            $table->decimal('premi_comprehensive_extended', 18, 2)->default(0);
            $table->string('no_polis')->nullable()->index();
            $table->string('wilayah')->nullable();
            $table->integer('jumlah_klaim')->default(0);
            $table->integer('tahun_renewal')->default(1);
            $table->string('jaminan_existing')->nullable();
            
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            
            $table->enum('call_status', [
                'pending', 'called', 'no_answer', 'callback',
                'interested', 'renewed', 'declined', 'invalid_contact'
            ])->default('pending');
            $table->text('call_notes')->nullable();
            $table->datetime('last_call_at')->nullable();
            $table->datetime('callback_at')->nullable();
            $table->string('selected_package')->nullable();
            $table->decimal('agreed_premium', 18, 2)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('last_called_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            $table->unique(['renewal_event_id', 'no_kontrak']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_data');
    }
};
