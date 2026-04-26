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
        Schema::table('workers', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('passport_expiry')->nullable()->after('passport_number');
            $table->string('permit_expiry')->nullable()->after('passport_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['gender', 'passport_expiry', 'permit_expiry']);
        });
    }
};
