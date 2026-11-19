<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->text('inbound_secret')->nullable()->after('inbound_token');
            $table->boolean('requires_signature')->default(false)->after('inbound_secret');
        });
    }

    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropColumn(['inbound_secret', 'requires_signature']);
        });
    }
};
