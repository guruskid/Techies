<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showcase_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('image')->nullable();
            $table->json('stack')->nullable();
            $table->boolean('looking_for_collaborators')->default(false);
            $table->string('status')->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showcase_projects');
    }
};
