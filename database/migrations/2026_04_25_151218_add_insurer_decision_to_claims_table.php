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
            $table->string('insurer_decision')->nullable()->after('submitted_to_insurer_by'); // approved, rejected
            $table->timestamp('insurer_decided_at')->nullable()->after('insurer_decision');
            $table->foreignId('insurer_decided_by')->nullable()->after('insurer_decided_at')->constrained('users')->nullOnDelete();
            $table->string('insurer_approval_letter')->nullable()->after('insurer_decided_by');
            $table->text('insurer_rejection_reason')->nullable()->after('insurer_approval_letter');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['insurer_decided_by']);
            $table->dropColumn(['insurer_decision', 'insurer_decided_at', 'insurer_decided_by', 'insurer_approval_letter', 'insurer_rejection_reason']);
        });
    }
};
