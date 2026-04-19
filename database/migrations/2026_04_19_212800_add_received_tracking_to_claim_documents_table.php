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
        Schema::table('claim_documents', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->change();
            $table->string('stored_filename')->nullable()->change();
            $table->string('path')->nullable()->change();
            $table->foreignId('uploaded_by')->nullable()->change();
            $table->boolean('is_received')->default(false)->after('uploaded_by');
            $table->timestamp('received_at')->nullable()->after('is_received');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete()->after('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_documents', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['is_received', 'received_at', 'received_by']);
        });
    }
};
