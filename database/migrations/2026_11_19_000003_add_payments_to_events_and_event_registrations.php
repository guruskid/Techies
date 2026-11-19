<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('payment_url')->nullable()->after('fee');
            $table->text('payment_instructions')->nullable()->after('payment_url');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('payment_status')->default('not_required')->after('status');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_reference', 'paid_at', 'refunded_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'payment_instructions']);
        });
    }
};
