<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            // Workflow status
            $table->string('workflow_status')->default('pending_uw')->after('partner_status');
            // pending_uw, uw_approved, uw_rejected, pending_marketing, marketing_approved, sent, success, failed
            
            // Batch tracking
            $table->unsignedBigInteger('batch_id')->nullable()->after('workflow_status');
            $table->string('batch_month')->nullable()->after('batch_id'); // Format: 2024-01
            
            // UW fields
            $table->unsignedBigInteger('uw_approved_by')->nullable()->after('batch_month');
            $table->timestamp('uw_approved_at')->nullable()->after('uw_approved_by');
            $table->text('uw_notes')->nullable()->after('uw_approved_at');
            $table->string('uw_rejection_reason')->nullable()->after('uw_notes');
            
            // Marketing fields
            $table->unsignedBigInteger('marketing_approved_by')->nullable()->after('uw_rejection_reason');
            $table->timestamp('marketing_approved_at')->nullable()->after('marketing_approved_by');
            $table->text('marketing_notes')->nullable()->after('marketing_approved_at');
            
            // Indexes
            $table->index('workflow_status');
            $table->index('batch_id');
            $table->index('batch_month');
        });

        // Create batches table
        Schema::create('data_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique(); // e.g., BATCH-202401-001
            $table->string('month'); // Format: 2024-01
            $table->string('status')->default('pending_uw'); // pending_uw, uw_approved, uw_rejected, pending_marketing, marketing_approved, processing, completed
            $table->integer('total_records')->default(0);
            $table->integer('uw_approved_count')->default(0);
            $table->integer('uw_rejected_count')->default(0);
            $table->integer('marketing_approved_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('uw_approved_by')->nullable();
            $table->timestamp('uw_approved_at')->nullable();
            $table->unsignedBigInteger('marketing_approved_by')->nullable();
            $table->timestamp('marketing_approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('month');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            $table->dropIndex(['workflow_status']);
            $table->dropIndex(['batch_id']);
            $table->dropIndex(['batch_month']);
            $table->dropColumn([
                'workflow_status',
                'batch_id',
                'batch_month',
                'uw_approved_by',
                'uw_approved_at',
                'uw_notes',
                'uw_rejection_reason',
                'marketing_approved_by',
                'marketing_approved_at',
                'marketing_notes',
            ]);
        });

        Schema::dropIfExists('data_batches');
    }
};
