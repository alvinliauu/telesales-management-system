<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            $table->string('partner_status')->default('pending')->after('call_status'); // pending, queued, success, failed
            $table->string('partner_request_id')->nullable()->after('partner_status');
            $table->text('partner_error_message')->nullable()->after('partner_request_id');
            $table->string('partner_error_code')->nullable()->after('partner_error_message');
            $table->timestamp('partner_sent_at')->nullable()->after('partner_error_code');
            $table->integer('partner_retry_count')->default(0)->after('partner_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            $table->dropColumn([
                'partner_status',
                'partner_request_id',
                'partner_error_message',
                'partner_error_code',
                'partner_sent_at',
                'partner_retry_count',
            ]);
        });
    }
};
