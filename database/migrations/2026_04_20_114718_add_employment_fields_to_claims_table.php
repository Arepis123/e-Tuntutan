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
            // Section I — e) Date of Employment
            $table->date('date_of_employment')->nullable()->after('insurance_policy_no');

            // Section I — g) Working Hours
            $table->time('working_hour_from')->nullable()->after('date_of_employment');
            $table->time('working_hour_to')->nullable()->after('working_hour_from');

            // Section I — h) Facilities Provided
            $table->boolean('facility_meals')->nullable()->after('working_hour_to');
            $table->boolean('facility_accommodation')->nullable()->after('facility_meals');
            $table->boolean('facility_transportation')->nullable()->after('facility_accommodation');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_employment',
                'working_hour_from',
                'working_hour_to',
                'facility_meals',
                'facility_accommodation',
                'facility_transportation',
            ]);
        });
    }
};
