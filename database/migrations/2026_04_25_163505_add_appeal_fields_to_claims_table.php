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
            $table->timestamp('appealed_at')->nullable()->after('insurer_rejection_attachment');
            $table->unsignedInteger('appeal_count')->default(0)->after('appealed_at');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['appealed_at', 'appeal_count']);
        });
    }
};
