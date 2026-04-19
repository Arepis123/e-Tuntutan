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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // employer who submitted
            $table->enum('claim_type', ['fwhs', 'green_card', 'perkeso']);
            $table->enum('claim_category', ['hospitalization', 'death']);
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->enum('forwarded_to', ['perkeso', 'green_card'])->nullable();
            $table->date('incident_date')->nullable();
            $table->text('incident_description')->nullable();
            $table->string('hospital_name')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('discharge_date')->nullable();
            $table->date('submitted_at')->nullable();
            $table->date('approved_at')->nullable();
            $table->date('rejected_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
