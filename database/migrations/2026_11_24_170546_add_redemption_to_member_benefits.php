<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_benefits', function (Blueprint $table) {
            $table->string('redemption_code')->nullable()->after('url');
            $table->text('redemption_instructions')->nullable()->after('redemption_code');
        });

        Schema::create('benefit_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_redemptions');
        Schema::table('member_benefits', function (Blueprint $table) {
            $table->dropColumn(['redemption_code', 'redemption_instructions']);
        });
    }
};
