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
            $table->string('company_pic_name')->nullable()->after('sst_no');
            $table->string('company_pic_phone')->nullable()->after('company_pic_name');
            $table->string('company_pic_email')->nullable()->after('company_pic_phone');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['company_pic_name', 'company_pic_phone', 'company_pic_email']);
        });
    }
};
