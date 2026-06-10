<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE claims MODIFY COLUMN status ENUM('open', 'in_progress', 'closed', 'cancelled') NOT NULL DEFAULT 'open'");

        Schema::table('claims', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('closed_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });

        DB::statement("ALTER TABLE claims MODIFY COLUMN status ENUM('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open'");
    }
};
