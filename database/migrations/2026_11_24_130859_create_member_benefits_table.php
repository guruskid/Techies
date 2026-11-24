<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_benefits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_label')->nullable();
            $table->string('category')->default('Partner offer');
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_benefits');
    }
};
