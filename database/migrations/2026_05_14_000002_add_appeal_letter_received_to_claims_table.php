<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->timestamp('appeal_letter_received_at')->nullable()->after('appeal_count');
            $table->foreignId('appeal_letter_received_by')->nullable()->constrained('users')->nullOnDelete()->after('appeal_letter_received_at');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['appeal_letter_received_by']);
            $table->dropColumn(['appeal_letter_received_at', 'appeal_letter_received_by']);
        });
    }
};
