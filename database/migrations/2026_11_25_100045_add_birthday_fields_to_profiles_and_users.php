<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('birthday_wished_at')->nullable()->after('feedback_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('birthday_wished_at');
        });
    }
};
