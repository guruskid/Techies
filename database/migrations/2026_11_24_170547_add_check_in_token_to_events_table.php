<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('check_in_token')->nullable()->unique()->after('status');
        });

        DB::table('events')->orderBy('id')->get(['id'])->each(function ($event): void {
            DB::table('events')->where('id', $event->id)->update([
                'check_in_token' => Str::upper(Str::random(24)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('check_in_token');
        });
    }
};
