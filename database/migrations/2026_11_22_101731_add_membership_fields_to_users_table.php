<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('membership_status')->default('active')->after('status');
            $table->string('membership_reference')->nullable()->after('membership_status');
            $table->timestamp('membership_paid_at')->nullable()->after('membership_reference');
            $table->foreignId('membership_confirmed_by')->nullable()->after('membership_paid_at')->constrained('users')->nullOnDelete();
            $table->text('membership_notes')->nullable()->after('membership_confirmed_by');

            $table->index('membership_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('membership_confirmed_by');
            $table->dropIndex(['membership_status']);
            $table->dropColumn(['membership_status', 'membership_reference', 'membership_paid_at', 'membership_notes']);
        });
    }
};
