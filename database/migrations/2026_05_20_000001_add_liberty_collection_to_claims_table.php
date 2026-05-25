<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->timestamp('liberty_collection_email_sent_at')->nullable()->after('submitted_to_insurer_by');
            $table->foreignId('liberty_collection_email_sent_by')->nullable()->constrained('users')->nullOnDelete()->after('liberty_collection_email_sent_at');
            $table->timestamp('liberty_collected_at')->nullable()->after('liberty_collection_email_sent_by');
            $table->foreignId('liberty_collected_by')->nullable()->constrained('users')->nullOnDelete()->after('liberty_collected_at');
            $table->string('liberty_collection_proof')->nullable()->after('liberty_collected_by');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['liberty_collection_email_sent_by']);
            $table->dropForeign(['liberty_collected_by']);
            $table->dropColumn([
                'liberty_collection_email_sent_at',
                'liberty_collection_email_sent_by',
                'liberty_collected_at',
                'liberty_collected_by',
                'liberty_collection_proof',
            ]);
        });
    }
};
