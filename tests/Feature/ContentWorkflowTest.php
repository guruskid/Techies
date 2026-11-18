<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_member_can_create_a_draft_article(): void
    {
        $member = $this->member();

        $response = $this->actingAs($member)->post(route('member.content.store'), [
            'title' => 'My first community article',
            'topic' => 'community',
            'excerpt' => 'A short introduction to the work our community is doing across the country.',
            'body' => str_repeat('Community is built one conversation at a time. ', 12),
            'action' => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('articles', [
            'user_id' => $member->id,
            'title' => 'My first community article',
            'topic' => 'community',
            'status' => Article::STATUS_DRAFT,
        ]);
    }

    public function test_submission_requires_a_minimum_seo_score(): void
    {
        $member = $this->member();
        $article = Article::create([
            'user_id' => $member->id,
            'title' => 'A short note',
            'topic' => 'engineering',
            'body' => str_repeat('Short body. ', 12),
            'status' => Article::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($member)->post(route('member.content.submit', $article));

        $response->assertRedirect(route('member.content.edit', $article));
        $response->assertSessionHas('error');

        $this->assertSame(Article::STATUS_DRAFT, $article->fresh()->status);
    }

    public function test_member_can_submit_a_seo_ready_article_for_review(): void
    {
        $member = $this->member();
        $article = $this->seoReadyArticle($member);

        $response = $this->actingAs($member)->post(route('member.content.submit', $article));

        $response->assertRedirect(route('member.content.index'));
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertSame(Article::STATUS_PENDING, $article->status);
        $this->assertNotNull($article->submitted_at);
        $this->assertGreaterThanOrEqual(Article::MINIMUM_SEO_SCORE, $article->seoScore());
    }

    public function test_admin_can_approve_a_standard_topic_article(): void
    {
        $admin = $this->staff('admin');
        $article = $this->seoReadyArticle($this->member(), ['status' => Article::STATUS_PENDING, 'submitted_at' => now()]);

        $response = $this->actingAs($admin)->post(route('admin.content.approve', $article), [
            'review_notes' => 'Great practical detail.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertSame(Article::STATUS_APPROVED, $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertSame($admin->id, $article->reviewed_by);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'content.approved',
            'model_id' => $article->id,
        ]);
    }

    public function test_admin_cannot_approve_super_admin_topics_but_a_super_admin_can(): void
    {
        $admin = $this->staff('admin');
        $superAdmin = $this->staff('super_admin');
        $article = $this->seoReadyArticle($this->member(), [
            'topic' => 'policy-governance',
            'status' => Article::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.content.approve', $article))
            ->assertForbidden();

        $this->assertSame(Article::STATUS_PENDING, $article->fresh()->status);

        $this->actingAs($superAdmin)
            ->post(route('admin.content.approve', $article))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Article::STATUS_APPROVED, $article->fresh()->status);
    }

    public function test_admin_can_request_changes_and_the_author_can_resubmit(): void
    {
        $admin = $this->staff('admin');
        $member = $this->member();
        $article = $this->seoReadyArticle($member, ['status' => Article::STATUS_PENDING, 'submitted_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.content.reject', $article), ['review_notes' => 'Please add a concrete example.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $article->refresh();
        $this->assertSame(Article::STATUS_REJECTED, $article->status);
        $this->assertSame('Please add a concrete example.', $article->review_notes);

        $this->actingAs($member)
            ->patch(route('member.content.update', $article), [
                'title' => $article->title,
                'topic' => $article->topic,
                'excerpt' => $article->excerpt,
                'body' => $article->body.' Added a concrete example from a real project.',
                'cover_image' => $article->cover_image,
                'seo_title' => $article->seo_title,
                'seo_description' => $article->seo_description,
                'seo_keywords' => $article->seo_keywords,
                'action' => 'submit',
            ])
            ->assertRedirect(route('member.content.index'))
            ->assertSessionHas('success');

        $this->assertSame(Article::STATUS_PENDING, $article->fresh()->status);
    }

    public function test_only_approved_articles_are_publicly_visible(): void
    {
        $approved = $this->seoReadyArticle($this->member(), [
            'status' => Article::STATUS_APPROVED,
            'published_at' => now(),
        ]);
        $draft = $this->seoReadyArticle($this->member(), [
            'title' => 'An unpublished draft article',
            'seo_title' => 'An unpublished draft article for the community',
        ]);

        $this->get(route('articles.show', $approved))->assertOk()->assertSee($approved->title);
        $this->get(route('articles'))->assertOk()->assertSee($approved->title)->assertDontSee($draft->title);
        $this->get(route('articles.show', $draft))->assertNotFound();
    }

    public function test_members_cannot_manage_another_authors_article(): void
    {
        $owner = $this->member();
        $other = $this->member();
        $article = $this->seoReadyArticle($owner, ['status' => Article::STATUS_DRAFT]);

        $this->actingAs($other)->get(route('member.content.edit', $article))->assertForbidden();

        $this->actingAs($other)->patch(route('member.content.update', $article), [
            'title' => 'Hijacked',
            'topic' => 'engineering',
            'body' => str_repeat('Trying to overwrite someone else. ', 10),
        ])->assertForbidden();

        $this->actingAs($other)->delete(route('member.content.destroy', $article))->assertForbidden();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => $article->title,
        ]);
    }

    public function test_members_cannot_access_the_admin_content_queue(): void
    {
        $this->actingAs($this->member())
            ->get(route('admin.content.index'))
            ->assertForbidden();
    }

    public function test_authoring_and_review_screens_render(): void
    {
        $member = $this->member();
        $article = $this->seoReadyArticle($member);
        $admin = $this->staff('admin');

        $this->actingAs($member)->get(route('member.content.index'))->assertOk()->assertSee('My Content');
        $this->actingAs($member)->get(route('member.content.create'))->assertOk()->assertSee('SEO settings');
        $this->actingAs($member)->get(route('member.content.edit', $article))->assertOk();
        $this->actingAs($member)->get(route('dashboard'))->assertOk();

        $this->actingAs($admin)->get(route('admin.content.index'))->assertOk()->assertSee('Content review queue');
        $this->actingAs($admin)->get(route('admin.content.show', $article))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    private function member(): User
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $user->assignRole('member');

        return $user;
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function seoReadyArticle(User $author, array $overrides = []): Article
    {
        return Article::create(array_merge([
            'user_id' => $author->id,
            'title' => 'Building reliable Laravel APIs for growing teams',
            'topic' => 'engineering',
            'excerpt' => str_repeat('A practical guide to building reliable Laravel APIs. ', 3),
            'body' => str_repeat('Laravel APIs help teams ship reliable software. ', 25),
            'cover_image' => 'https://example.com/cover.jpg',
            'status' => Article::STATUS_DRAFT,
            'seo_title' => 'Building reliable Laravel APIs for growing teams',
            'seo_description' => str_repeat('Reliable Laravel APIs for growing teams. ', 3),
            'seo_keywords' => 'laravel apis, api design',
        ], $overrides));
    }
}
