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
        Schema::table('claims', function (Blueprint $table) {
            $table->timestamp('submitted_to_insurer_at')->nullable()->after('documents_received_by');
            $table->foreignId('submitted_to_insurer_by')->nullable()->after('submitted_to_insurer_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['submitted_to_insurer_by']);
            $table->dropColumn(['submitted_to_insurer_at', 'submitted_to_insurer_by']);
        });
    }
};
