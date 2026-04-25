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
        Schema::create('document_configs', function (Blueprint $table) {
            $table->id();
            $table->string('claim_type');         // fwhs, green_card, perkeso
            $table->string('claim_category');     // hospitalization, death
            $table->string('incident_type')->nullable(); // accident, non_accident, null for death
            $table->string('document_type');      // e.g. accident_fcl, original_receipt
            $table->string('label');              // display name
            $table->boolean('is_required')->default(true);    // tracked & shown to client
            $table->boolean('is_downloadable')->default(false); // shows in download section
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_configs');
    }
};
