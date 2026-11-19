<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_account_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_manage')->default(false);
            $table->timestamps();

            $table->unique(['email_account_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_account_user');
    }
};
