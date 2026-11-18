<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const MINIMUM_SEO_SCORE = 50;

    protected $fillable = [
        'user_id', 'title', 'slug', 'topic', 'excerpt', 'body', 'cover_image',
        'status', 'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
        'reviewed_by', 'reviewed_at', 'review_notes', 'submitted_at', 'published_at', 'views',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if (blank($article->slug)) {
                $article->slug = static::uniqueSlug($article->title, $article->getKey());
            } else {
                $article->slug = Str::slug($article->slug);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Changes requested',
        ];
    }

    /**
     * @return array<string, array{label: string, approval: string, description: string}>
     */
    public static function topics(): array
    {
        return config('techies.content_topics', []);
    }

    public function topicLabel(): string
    {
        return static::topics()[$this->topic]['label'] ?? Str::headline($this->topic);
    }

    public function topicApprovalLevel(): string
    {
        return static::topics()[$this->topic]['approval'] ?? 'admin';
    }

    public function requiresSuperAdminApproval(): bool
    {
        return $this->topicApprovalLevel() === 'super_admin';
    }

    public function approvalLabel(): string
    {
        return $this->requiresSuperAdminApproval() ? 'Super admin review' : 'Admin review';
    }

    public function statusLabel(): string
    {
        return static::statuses()[$this->status] ?? Str::headline($this->status);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isEditableBy(User $user): bool
    {
        return $this->user_id === $user->id
            && in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function canBeApprovedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && ! $this->requiresSuperAdminApproval();
    }

    public function readingTime(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 220));
    }

    public function primaryKeyword(): string
    {
        $keyword = trim(Str::before((string) $this->seo_keywords, ','));

        return $keyword;
    }

    /**
     * A transparent, human-readable checklist used on the authoring screens.
     *
     * @return array<string, array{label: string, passed: bool, hint: string}>
     */
    public function seoChecklist(): array
    {
        $title = $this->seo_title ?: $this->title;
        $description = $this->seo_description ?: (string) $this->excerpt;
        $keyword = $this->primaryKeyword();
        $plainBody = Str::lower(strip_tags((string) $this->body));

        return [
            'title_length' => [
                'label' => 'SEO title is 30-60 characters',
                'passed' => mb_strlen((string) $title) >= 30 && mb_strlen((string) $title) <= 60,
                'hint' => 'Aim for a concise title that includes your focus keyword.',
            ],
            'description_length' => [
                'label' => 'Meta description is 120-160 characters',
                'passed' => mb_strlen($description) >= 120 && mb_strlen($description) <= 160,
                'hint' => 'Write a compelling summary that appears in search results.',
            ],
            'focus_keyword' => [
                'label' => 'Focus keyword appears in the title and body',
                'passed' => filled($keyword)
                    && Str::contains(Str::lower((string) $title), Str::lower($keyword))
                    && Str::contains($plainBody, Str::lower($keyword)),
                'hint' => 'Choose one clear keyword or phrase and use it naturally.',
            ],
            'slug' => [
                'label' => 'URL slug is short and readable',
                'passed' => filled($this->slug) && mb_strlen($this->slug) <= 80 && ! Str::contains($this->slug, ' '),
                'hint' => 'Use a short slug with the main keyword.',
            ],
            'excerpt' => [
                'label' => 'Excerpt is between 80 and 300 characters',
                'passed' => mb_strlen((string) $this->excerpt) >= 80 && mb_strlen((string) $this->excerpt) <= 300,
                'hint' => 'The excerpt is used on listing pages and social previews.',
            ],
            'body_length' => [
                'label' => 'Article body is at least 600 characters',
                'passed' => mb_strlen(strip_tags((string) $this->body)) >= 600,
                'hint' => 'Longer, useful articles rank better and help readers.',
            ],
            'cover_image' => [
                'label' => 'Cover image is provided',
                'passed' => filled($this->cover_image),
                'hint' => 'A cover image improves click-through and social sharing.',
            ],
        ];
    }

    public function seoScore(): int
    {
        $checks = $this->seoChecklist();

        if ($checks === []) {
            return 0;
        }

        $passed = count(array_filter($checks, fn (array $check) => $check['passed']));

        return (int) round(($passed / count($checks)) * 100);
    }

    public function isSeoReady(): bool
    {
        return $this->seoScore() >= self::MINIMUM_SEO_SCORE;
    }

    /**
     * @return array<int, string>
     */
    public function failingSeoChecks(): array
    {
        return collect($this->seoChecklist())
            ->reject(fn (array $check) => $check['passed'])
            ->pluck('label')
            ->values()
            ->all();
    }
}
