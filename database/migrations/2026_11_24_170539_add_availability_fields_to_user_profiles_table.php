<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('open_to_work')->default(false)->after('availability');
            $table->boolean('open_to_mentor')->default(false)->after('open_to_work');
            $table->boolean('open_to_collaborate')->default(false)->after('open_to_mentor');
            $table->boolean('available_for_speaking')->default(false)->after('open_to_collaborate');
            $table->boolean('hiring')->default(false)->after('available_for_speaking');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['open_to_work', 'open_to_mentor', 'open_to_collaborate', 'available_for_speaking', 'hiring']);
        });
    }
};
