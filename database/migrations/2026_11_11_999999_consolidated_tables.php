<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username')->unique()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('professional_title')->nullable();
            $table->string('technology_category')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('current_organization')->nullable();
            $table->text('education')->nullable();
            $table->text('bio')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('portfolio')->nullable();
            $table->string('website')->nullable();
            $table->json('areas_of_interest')->nullable();
            $table->string('preferred_participation')->nullable();
            $table->string('membership_type')->nullable();
            $table->boolean('is_public')->default(false);
            $table->integer('profile_completion')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('member_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('url');
            $table->timestamps();
        });
        Schema::create('technology_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->integer('year_established')->nullable();
            $table->string('organization_type')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('community_size')->nullable();
            $table->integer('active_members_count')->default(0);
            $table->text('programs_offered')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('x')->nullable();
            $table->string('youtube')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('hub_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('hub_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('editor');
            $table->timestamps();
            $table->unique(['hub_id', 'user_id']);
        });
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('chapter_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['chapter_id', 'user_id']);
        });
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('event_categories')->nullOnDelete();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('meeting_url')->nullable();
            $table->date('registration_deadline')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->string('registration_type')->default('free');
            $table->decimal('fee', 10, 2)->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });
        Schema::create('opportunity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('opportunity_categories')->nullOnDelete();
            $table->string('organization')->nullable();
            $table->foreignId('hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->string('application_link')->nullable();
            $table->date('deadline')->nullable();
            $table->text('eligibility')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamps();
        });
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
        Schema::create('member_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'skill_id']);
        });
        Schema::create('member_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'interest_id']);
        });
        Schema::create('hub_technology_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_area_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['hub_id', 'technology_area_id']);
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('facebook')->nullable()->after('linkedin');
            $table->string('twitter')->nullable()->after('facebook');
            $table->string('tiktok')->nullable()->after('twitter');
        });
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('technologies')->nullable();
            $table->timestamps();
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->text('likes')->nullable();
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->text('certifications')->nullable();
            $table->text('languages')->nullable();
            $table->string('availability')->nullable();
            $table->string('headline')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_technology_areas');
        Schema::dropIfExists('member_interests');
        Schema::dropIfExists('member_skills');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('opportunity_categories');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('chapter_members');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('hub_team_members');
        Schema::dropIfExists('hub_applications');
        Schema::dropIfExists('hubs');
        Schema::dropIfExists('member_social_links');
        Schema::dropIfExists('technology_areas');
        Schema::dropIfExists('interests');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('member_profiles');
        Schema::dropIfExists('user_profiles');
    }
};
