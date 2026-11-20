<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only audit trail of every change to a claim.
     *
     * No foreign keys on purpose: audit rows must survive deletion of the
     * claim or the user, so the actor's name/email are snapshotted too.
     */
    public function up(): void
    {
        Schema::create('claim_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id')->index();
            $table->string('event', 20);                 // created | updated | deleted | restored
            $table->json('changes')->nullable();         // {"field": {"old": ..., "new": ...}}

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_roles')->nullable();

            $table->string('source', 20);                // web | console
            $table->string('action')->nullable();        // Livewire method(s), e.g. "confirmReject"
            $table->string('url', 2048)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_audit_logs');
    }
};
