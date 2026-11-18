<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Notifications\ContentSubmitted;
use App\Notifications\HubApplicationSubmitted;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewerAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_submitting_a_standard_article_emails_admins_and_super_admins(): void
    {
        Notification::fake();

        $admin = $this->staff('admin');
        $superAdmin = $this->staff('super_admin');
        $author = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $article = $this->seoReadyArticle($author);

        $this->actingAs($author)
            ->post(route('member.content.submit', $article))
            ->assertRedirect(route('member.content.index'));

        Notification::assertSentTo($admin, ContentSubmitted::class);
        Notification::assertSentTo($superAdmin, ContentSubmitted::class);
        Notification::assertNotSentTo($author, ContentSubmitted::class);
    }

    public function test_super_admin_topics_only_email_super_admins(): void
    {
        Notification::fake();

        $admin = $this->staff('admin');
        $superAdmin = $this->staff('super_admin');
        $author = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $article = $this->seoReadyArticle($author, 'policy-governance');

        $this->actingAs($author)
            ->post(route('member.content.submit', $article))
            ->assertRedirect(route('member.content.index'));

        Notification::assertSentTo($superAdmin, ContentSubmitted::class);
        Notification::assertNotSentTo($admin, ContentSubmitted::class);
    }

    public function test_new_hub_applications_email_admins(): void
    {
        Notification::fake();

        $admin = $this->staff('admin');
        $owner = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($owner)
            ->post(route('hubs.register.store'), [
                'name' => 'Abuja Builders Hub',
                'description' => 'A community for builders in Abuja.',
            ])
            ->assertRedirect(route('hub.dashboard'));

        Notification::assertSentTo($admin, HubApplicationSubmitted::class);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    private function seoReadyArticle(User $author, string $topic = 'engineering'): Article
    {
        return Article::create([
            'user_id' => $author->id,
            'title' => 'Building reliable Laravel APIs for growing teams',
            'topic' => $topic,
            'excerpt' => str_repeat('A practical guide to building reliable Laravel APIs. ', 3),
            'body' => str_repeat('Laravel APIs help teams ship reliable software. ', 25),
            'cover_image' => 'https://example.com/cover.jpg',
            'status' => Article::STATUS_DRAFT,
            'seo_title' => 'Building reliable Laravel APIs for growing teams',
            'seo_description' => str_repeat('Reliable Laravel APIs for growing teams. ', 3),
            'seo_keywords' => 'laravel apis, api design',
        ]);
    }
}
