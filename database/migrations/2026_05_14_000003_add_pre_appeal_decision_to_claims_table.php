<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('pre_appeal_insurer_decision')->nullable()->after('insurer_decision');
            $table->timestamp('pre_appeal_insurer_decided_at')->nullable()->after('pre_appeal_insurer_decision');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['pre_appeal_insurer_decision', 'pre_appeal_insurer_decided_at']);
        });
    }
};
