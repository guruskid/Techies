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
        Schema::table('users', function (Blueprint $table) {
            $table->string('member_number')->nullable()->unique()->after('membership_notes');
            $table->string('referral_code')->nullable()->unique()->after('member_number');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->boolean('is_founding_member')->default(false)->after('referred_by');
            $table->timestamp('feedback_requested_at')->nullable()->after('is_founding_member');
            $table->timestamp('opportunity_match_sent_at')->nullable()->after('feedback_requested_at');
        });

        DB::table('users')->orderBy('id')->get(['id', 'created_at'])->each(function ($user): void {
            $year = $user->created_at ? date('Y', strtotime((string) $user->created_at)) : date('Y');

            DB::table('users')->where('id', $user->id)->update([
                'member_number' => 'MWTT-'.$year.'-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'referral_code' => Str::upper(Str::random(8)),
                'is_founding_member' => true,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn(['member_number', 'referral_code', 'is_founding_member', 'feedback_requested_at', 'opportunity_match_sent_at']);
        });
    }
};
