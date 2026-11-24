<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->unique(['user_id', 'opportunity_id']);
        });

        Schema::create('referral_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('status')->default('pending');
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_requests');
        Schema::dropIfExists('referral_offers');
    }
};
