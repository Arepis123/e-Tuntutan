<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Accident-specific (FCL Accident Form — Section II)
            $table->time('incident_time')->nullable()->after('incident_date');
            $table->text('incident_location')->nullable()->after('incident_time');
            $table->string('injury_types')->nullable()->after('incident_location'); // comma-separated: fractured, burn, death, dismemberment, others
            $table->string('injury_type_other')->nullable()->after('injury_types');
            $table->text('injury_description')->nullable()->after('injury_type_other');

            // Non-accident-specific (FCL Non-Accident Form — Section II)
            $table->string('disease_type')->nullable()->after('injury_description');
            $table->boolean('is_historical_disease')->nullable()->after('disease_type');
            $table->boolean('is_work_related')->nullable()->after('is_historical_disease');
            $table->text('work_related_description')->nullable()->after('is_work_related');

            // Insurance coverage (Section III — both forms)
            $table->string('insurance_policy_no')->nullable()->after('work_related_description');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn([
                'incident_time',
                'incident_location',
                'injury_types',
                'injury_type_other',
                'injury_description',
                'disease_type',
                'is_historical_disease',
                'is_work_related',
                'work_related_description',
                'insurance_policy_no',
            ]);
        });
    }
};
