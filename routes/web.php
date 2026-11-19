<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\EventCategoryController as AdminEventCategoryController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\Mail\AccountController as MailAccountController;
use App\Http\Controllers\Admin\Mail\BroadcastController as MailBroadcastController;
use App\Http\Controllers\Admin\Mail\MessageController as MailMessageController;
use App\Http\Controllers\Admin\Mail\TemplateController as MailTemplateController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OpportunityCategoryController as AdminOpportunityCategoryController;
use App\Http\Controllers\Admin\OpportunityController as AdminOpportunityController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Chapter\ChapterController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\HubDashboardController;
use App\Http\Controllers\InboundMailController;
use App\Http\Controllers\Member\ContentController as MemberContentController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\MyProfileController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Opportunity\OpportunityController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuperAdmin\AuditLogController as SuperAdminAuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminRoleController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hub;
use App\Models\HubApplication;
use App\Models\NewsletterSubscriber;
use App\Models\Opportunity;
use App\Models\OpportunityCategory;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\HubApplicationSubmitted;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

$profileDomains = collect([config('techies.profile_domain')])
    ->merge(config('techies.profile_extra_domains', []))
    ->filter()
    ->unique()
    ->values();

foreach ($profileDomains as $profileDomain) {
    Route::domain('{username}.'.$profileDomain)
        ->where(['username' => '(?!www\.)[a-z0-9](?:[a-z0-9-]{1,28}[a-z0-9])?'])
        ->group(function () use ($profileDomain) {
            $profileRoute = Route::get('/', [ProfileController::class, 'showBySubdomain']);

            if ($profileDomain === config('techies.profile_domain')) {
                $profileRoute->name('profile.subdomain');
            }

            // Any other path on a profile subdomain belongs to the main site.
            Route::get('/{path?}', function (string $username, ?string $path = null) {
                return redirect()->away(rtrim(config('app.url'), '/').'/'.ltrim((string) $path, '/'));
            })->where('path', '.*');
        });
}

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/coming-soon', 'pages.coming-soon')->name('coming.soon');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/feeds/articles.xml', [FeedController::class, 'articles'])->name('feed.articles');
Route::get('/feeds/events.xml', [FeedController::class, 'events'])->name('feed.events');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nDisallow: /admin\nDisallow: /super-admin\nDisallow: /dashboard\nDisallow: /hub/dashboard\n\nSitemap: ".route('sitemap')."\n", 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
    ]);
})->name('robots');
Route::post('/mail/inbound/{token}', [InboundMailController::class, 'store'])->name('mail.inbound');
Route::post('/newsletter', function (Request $r) {
    $data = $r->validate([
        'email' => 'required|email|max:255',
        'name' => 'nullable|string|max:255',
    ]);

    $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $data['email']], ['name' => $data['name'] ?? null]);

    return back()->with('success', $subscriber->wasRecentlyCreated
        ? 'Thanks for subscribing to the MWTT newsletter.'
        : 'You are already subscribed to the MWTT newsletter.');
})->name('newsletter.subscribe');
Route::post('/contact', function (Request $r) {
    $data = $r->validate([
        'name' => 'required|string|max:150',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:200',
        'message' => 'required|string|max:5000',
    ]);

    Mail::raw(
        "New contact message from {$data['name']} ({$data['email']})\n\nSubject: {$data['subject']}\n\n{$data['message']}",
        function ($mail) use ($data) {
            $mail->to(Setting::value('contact_email', config('techies.contact_email')))
                ->replyTo($data['email'], $data['name'])
                ->subject('MWTT contact: '.$data['subject']);
        }
    );

    return back()->with('success', 'Thanks for reaching out. We will get back to you shortly.');
})->middleware('throttle:5,1')->name('contact.send');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MemberDashboard::class, 'index'])->name('dashboard');
    Route::get('/profile', [MyProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [MyProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/username-availability', [MyProfileController::class, 'checkUsername'])->name('profile.username.check')->middleware('throttle:30,1');
    Route::get('/hub/dashboard', [HubDashboardController::class, 'index'])->name('hub.dashboard');

    Route::prefix('member/content')->name('member.content.')->group(function () {
        Route::get('/', [MemberContentController::class, 'index'])->name('index');
        Route::get('/create', [MemberContentController::class, 'create'])->name('create');
        Route::post('/', [MemberContentController::class, 'store'])->name('store');
        Route::get('/{article}/edit', [MemberContentController::class, 'edit'])->name('edit');
        Route::patch('/{article}', [MemberContentController::class, 'update'])->name('update');
        Route::post('/{article}/submit', [MemberContentController::class, 'submit'])->name('submit');
        Route::delete('/{article}', [MemberContentController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:admin|super_admin')->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.alt');
        Route::get('/admin/manage', function () {
            return view('admin.manage');
        })->name('admin.manage');

        Route::prefix('admin/mail')->name('admin.mail.')->group(function () {
            Route::get('/', [MailMessageController::class, 'index'])->name('index');
            Route::get('/inbox', [MailMessageController::class, 'inbox'])->name('inbox');
            Route::get('/inbox/{message}', [MailMessageController::class, 'showMessage'])->name('inbox.show');
            Route::get('/sent', [MailMessageController::class, 'sent'])->name('sent');
            Route::get('/sent/{message}', [MailMessageController::class, 'showMessage'])->name('sent.show');
            Route::get('/attachments/{attachment}', [MailMessageController::class, 'downloadAttachment'])->name('attachments.download');
            Route::get('/compose', [MailMessageController::class, 'compose'])->name('compose');
            Route::post('/compose', [MailMessageController::class, 'send'])->name('send');
            Route::post('/compose/draft', [MailMessageController::class, 'saveDraft'])->name('drafts.save');
            Route::delete('/compose/draft', [MailMessageController::class, 'discardDraft'])->name('drafts.discard');

            Route::get('/accounts', [MailAccountController::class, 'index'])->name('accounts.index');
            Route::get('/accounts/create', [MailAccountController::class, 'create'])->name('accounts.create');
            Route::post('/accounts', [MailAccountController::class, 'store'])->name('accounts.store');
            Route::get('/accounts/{account}/edit', [MailAccountController::class, 'edit'])->name('accounts.edit');
            Route::patch('/accounts/{account}', [MailAccountController::class, 'update'])->name('accounts.update');
            Route::delete('/accounts/{account}', [MailAccountController::class, 'destroy'])->name('accounts.destroy');
            Route::post('/accounts/{account}/default', [MailAccountController::class, 'makeDefault'])->name('accounts.default');
            Route::post('/accounts/{account}/test', [MailAccountController::class, 'test'])->name('accounts.test');
            Route::post('/accounts/{account}/test-imap', [MailAccountController::class, 'testImap'])->name('accounts.test-imap');
            Route::post('/accounts/{account}/sync', [MailAccountController::class, 'sync'])->name('accounts.sync');
            Route::post('/accounts/{account}/rotate-inbound', [MailAccountController::class, 'rotateInbound'])->name('accounts.rotate-inbound');

            Route::get('/templates', [MailTemplateController::class, 'index'])->name('templates.index');
            Route::get('/templates/create', [MailTemplateController::class, 'create'])->name('templates.create');
            Route::post('/templates', [MailTemplateController::class, 'store'])->name('templates.store');
            Route::get('/templates/{template}/edit', [MailTemplateController::class, 'edit'])->name('templates.edit');
            Route::patch('/templates/{template}', [MailTemplateController::class, 'update'])->name('templates.update');
            Route::delete('/templates/{template}', [MailTemplateController::class, 'destroy'])->name('templates.destroy');

            Route::get('/broadcasts', [MailBroadcastController::class, 'index'])->name('broadcasts.index');
            Route::get('/broadcasts/create', [MailBroadcastController::class, 'create'])->name('broadcasts.create');
            Route::post('/broadcasts', [MailBroadcastController::class, 'store'])->name('broadcasts.store');
            Route::get('/broadcasts/{broadcast}', [MailBroadcastController::class, 'show'])->name('broadcasts.show');
            Route::post('/broadcasts/{broadcast}/send', [MailBroadcastController::class, 'send'])->name('broadcasts.send');
            Route::delete('/broadcasts/{broadcast}', [MailBroadcastController::class, 'destroy'])->name('broadcasts.destroy');
        });
        Route::get('/admin/members', [MemberController::class, 'index'])->name('admin.members.index');
        Route::get('/admin/members/{user}', [MemberController::class, 'show'])->name('admin.members.show');
        Route::post('/admin/members/{user}/suspend', [MemberController::class, 'suspend'])->name('admin.members.suspend');
        Route::post('/admin/members/{user}/restore', [MemberController::class, 'restore'])->name('admin.members.restore');
        Route::get('/admin/hubs', [App\Http\Controllers\Admin\HubController::class, 'index'])->name('admin.hubs.index');
        Route::get('/admin/hubs/{hub}', [App\Http\Controllers\Admin\HubController::class, 'show'])->name('admin.hubs.show');
        Route::post('/admin/hubs/{hub}/approve', [App\Http\Controllers\Admin\HubController::class, 'approve'])->name('admin.hubs.approve');
        Route::post('/admin/hubs/{hub}/reject', [App\Http\Controllers\Admin\HubController::class, 'reject'])->name('admin.hubs.reject');
        Route::post('/admin/hubs/{hub}/suspend', [App\Http\Controllers\Admin\HubController::class, 'suspend'])->name('admin.hubs.suspend');
        Route::get('/admin/categories', function () {
            return view('admin.categories.index');
        })->name('admin.categories.index');

        Route::get('/admin/events', [AdminEventController::class, 'index'])->name('admin.events.index');
        Route::get('/admin/events/create', [AdminEventController::class, 'create'])->name('admin.events.create');
        Route::post('/admin/events', [AdminEventController::class, 'store'])->name('admin.events.store');
        Route::get('/admin/events/{event}/edit', [AdminEventController::class, 'edit'])->name('admin.events.edit');
        Route::get('/admin/events/{event}/registrations', [AdminEventController::class, 'registrations'])->name('admin.events.registrations');
        Route::get('/admin/events/{event}/registrations/export', [AdminEventController::class, 'exportRegistrations'])->name('admin.events.registrations.export');
        Route::post('/admin/events/{event}/registrations/{registration}/check-in', [AdminEventController::class, 'checkIn'])->name('admin.events.registrations.check-in');
        Route::post('/admin/events/{event}/registrations/{registration}/payment', [AdminEventController::class, 'updatePayment'])->name('admin.events.registrations.payment');
        Route::patch('/admin/events/{event}', [AdminEventController::class, 'update'])->name('admin.events.update');
        Route::delete('/admin/events/{event}', [AdminEventController::class, 'destroy'])->name('admin.events.destroy');

        Route::get('/admin/event-categories', [AdminEventCategoryController::class, 'index'])->name('admin.event-categories.index');
        Route::get('/admin/event-categories/create', [AdminEventCategoryController::class, 'create'])->name('admin.event-categories.create');
        Route::post('/admin/event-categories', [AdminEventCategoryController::class, 'store'])->name('admin.event-categories.store');
        Route::get('/admin/event-categories/{eventCategory}/edit', [AdminEventCategoryController::class, 'edit'])->name('admin.event-categories.edit');
        Route::patch('/admin/event-categories/{eventCategory}', [AdminEventCategoryController::class, 'update'])->name('admin.event-categories.update');
        Route::delete('/admin/event-categories/{eventCategory}', [AdminEventCategoryController::class, 'destroy'])->name('admin.event-categories.destroy');

        Route::get('/admin/opportunity-categories', [AdminOpportunityCategoryController::class, 'index'])->name('admin.opportunity-categories.index');
        Route::get('/admin/opportunity-categories/create', [AdminOpportunityCategoryController::class, 'create'])->name('admin.opportunity-categories.create');
        Route::post('/admin/opportunity-categories', [AdminOpportunityCategoryController::class, 'store'])->name('admin.opportunity-categories.store');
        Route::get('/admin/opportunity-categories/{opportunityCategory}/edit', [AdminOpportunityCategoryController::class, 'edit'])->name('admin.opportunity-categories.edit');
        Route::patch('/admin/opportunity-categories/{opportunityCategory}', [AdminOpportunityCategoryController::class, 'update'])->name('admin.opportunity-categories.update');
        Route::delete('/admin/opportunity-categories/{opportunityCategory}', [AdminOpportunityCategoryController::class, 'destroy'])->name('admin.opportunity-categories.destroy');

        Route::get('/admin/opportunities', [AdminOpportunityController::class, 'index'])->name('admin.opportunities.index');
        Route::get('/admin/opportunities/create', [AdminOpportunityController::class, 'create'])->name('admin.opportunities.create');
        Route::post('/admin/opportunities', [AdminOpportunityController::class, 'store'])->name('admin.opportunities.store');
        Route::get('/admin/opportunities/{opportunity}/edit', [AdminOpportunityController::class, 'edit'])->name('admin.opportunities.edit');
        Route::patch('/admin/opportunities/{opportunity}', [AdminOpportunityController::class, 'update'])->name('admin.opportunities.update');
        Route::post('/admin/opportunities/{opportunity}/applications/{application}', [AdminOpportunityController::class, 'updateApplication'])->name('admin.opportunities.applications.update');
        Route::get('/admin/opportunities/{opportunity}/applications/export', [AdminOpportunityController::class, 'exportApplications'])->name('admin.opportunities.applications.export');
        Route::delete('/admin/opportunities/{opportunity}', [AdminOpportunityController::class, 'destroy'])->name('admin.opportunities.destroy');

        Route::get('/admin/chapters', [AdminChapterController::class, 'index'])->name('admin.chapters.index');
        Route::get('/admin/chapters/create', [AdminChapterController::class, 'create'])->name('admin.chapters.create');
        Route::post('/admin/chapters', [AdminChapterController::class, 'store'])->name('admin.chapters.store');
        Route::get('/admin/chapters/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('admin.chapters.edit');
        Route::patch('/admin/chapters/{chapter}', [AdminChapterController::class, 'update'])->name('admin.chapters.update');
        Route::delete('/admin/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('admin.chapters.destroy');

        Route::get('/admin/announcements', [AdminAnnouncementController::class, 'index'])->name('admin.announcements.index');
        Route::get('/admin/announcements/create', [AdminAnnouncementController::class, 'create'])->name('admin.announcements.create');
        Route::post('/admin/announcements', [AdminAnnouncementController::class, 'store'])->name('admin.announcements.store');
        Route::get('/admin/announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('admin.announcements.edit');
        Route::patch('/admin/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('admin.announcements.update');
        Route::delete('/admin/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

        Route::get('/admin/content', [AdminContentController::class, 'index'])->name('admin.content.index');
        Route::get('/admin/content/{article}', [AdminContentController::class, 'show'])->name('admin.content.show');
        Route::post('/admin/content/{article}/approve', [AdminContentController::class, 'approve'])->name('admin.content.approve');
        Route::post('/admin/content/{article}/reject', [AdminContentController::class, 'reject'])->name('admin.content.reject');
        Route::post('/admin/content/{article}/unpublish', [AdminContentController::class, 'unpublish'])->name('admin.content.unpublish');
    });

    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', [SuperAdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/role', [SuperAdminUserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/suspend', [SuperAdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/restore', [SuperAdminUserController::class, 'restore'])->name('users.restore');
        Route::get('/roles', [SuperAdminRoleController::class, 'index'])->name('roles.index');
        Route::get('/settings', [SuperAdminSettingController::class, 'edit'])->name('settings.edit');
        Route::post('/settings', [SuperAdminSettingController::class, 'update'])->name('settings.update');
        Route::get('/audit-logs', [SuperAdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
        Route::post('/content/{article}/force-publish', [AdminContentController::class, 'forcePublish'])->name('content.force-publish');
    });
});

// public pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/mission-and-vision', 'pages.mission-and-vision')->name('mission_and_vision');
Route::view('/leadership', 'pages.leadership')->name('leadership');
Route::view('/community', 'pages.community')->name('community');
Route::get('/members', function (Request $r) {
    $q = User::where('status', 'active')
        ->whereHas('profile', fn ($p) => $p->where('is_public', true))
        ->with('profile');
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('name', 'like', "%$s%")
                ->orWhereHas('profile', function ($p) use ($s) {
                    $p->where('professional_title', 'like', "%$s%")
                        ->orWhere('technology_category', 'like', "%$s%")
                        ->orWhere('city', 'like', "%$s%")
                        ->orWhere('state', 'like', "%$s%");
                })
                ->orWhereHas('skills', fn ($sk) => $sk->where('name', 'like', "%$s%"))
                ->orWhereHas('interests', fn ($it) => $it->where('name', 'like', "%$s%"));
        });
    }
    if ($r->filled('technology')) {
        $q->whereHas('profile', fn ($p) => $p->where('technology_category', $r->technology));
    }
    if ($r->filled('state')) {
        $q->whereHas('profile', fn ($p) => $p->where('state', $r->state));
    }
    if ($r->filled('experience')) {
        $q->whereHas('profile', fn ($p) => $p->where('experience_level', $r->experience));
    }

    return view('pages.members', [
        'members' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'technology' => $r->technology,
        'state' => $r->state,
        'experience' => $r->experience,
    ]);
})->name('members');
Route::view('/hubs/register', 'pages.hub-register')->name('hubs.register');
Route::post('/hubs/register', function (Request $r) {
    $data = $r->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:30',
        'website' => 'nullable|url',
        'contact_person' => 'nullable|string|max:150',
        'contact_position' => 'nullable|string|max:150',
        'community_size' => 'nullable|string|max:50',
        'year_established' => 'nullable|integer',
        'organization_type' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:500',
        'linkedin' => 'nullable|url',
        'facebook' => 'nullable|url',
        'instagram' => 'nullable|url',
        'x' => 'nullable|url',
        'youtube' => 'nullable|url',
        'programs_offered' => 'nullable|string',
    ]);
    $owner = auth()->user();
    if (! $owner) {
        return redirect()->route('register')->with('status', 'Please create an account first.');
    }
    if ($owner->hub()->exists()) {
        return redirect()->route('hub.dashboard')->with('status', 'You already have a hub registered.');
    }
    $hub = Hub::create(array_merge($data, [
        'user_id' => $owner->id,
        'slug' => Str::slug($data['name']).'-'.uniqid(),
        'status' => 'pending',
        'is_verified' => false,
        'is_public' => false,
    ]));
    HubApplication::create(['hub_id' => $hub->id, 'status' => 'pending']);
    Role::findOrCreate('hub_owner', 'web');
    $owner->assignRole('hub_owner');

    User::reviewers()
        ->whereKeyNot($owner->id)
        ->get()
        ->each(fn (User $reviewer) => $reviewer->notify(new HubApplicationSubmitted($hub)));

    return redirect()->route('hub.dashboard')->with('success', 'Hub application submitted and pending review.');
})->middleware('auth')->name('hubs.register.store');
Route::get('/hubs/{slug}', [HubController::class, 'show'])->name('hubs.show');
Route::get('/hubs', function (Request $r) {
    $q = Hub::where('is_public', true)->where('status', 'approved');
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('name', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%")
                ->orWhere('city', 'like', "%$s%")
                ->orWhere('state', 'like', "%$s%");
        });
    }
    if ($r->filled('state')) {
        $q->where('state', $r->state);
    }

    return view('pages.hubs', [
        'hubs' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'state' => $r->state,
    ]);
})->name('hubs');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event:slug}/calendar', [EventController::class, 'calendar'])->name('events.calendar');
Route::post('/events/{event:slug}/register', [EventController::class, 'register'])->middleware(['auth', 'verified'])->name('events.register');
Route::post('/events/{event:slug}/cancel', [EventController::class, 'cancel'])->middleware(['auth', 'verified'])->name('events.cancel');
Route::get('/events', function (Request $r) {
    $q = Event::with('category')->where('status', 'published')->where('start_date', '>=', now()->toDateString());
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('title', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%")
                ->orWhere('city', 'like', "%$s%");
        });
    }
    if ($r->filled('category')) {
        $q->where('category_id', $r->category);
    }
    if ($r->filled('state')) {
        $q->where('state', $r->state);
    }
    if ($r->filled('type')) {
        $q->where('is_online', $r->type === 'online');
    }

    return view('pages.events', [
        'events' => $q->orderBy('start_date')->paginate(12)->withQueryString(),
        'search' => $r->search,
        'category' => $r->category,
        'state' => $r->state,
        'type' => $r->type,
        'categories' => EventCategory::all(),
    ]);
})->name('events');
Route::get('/opportunities/{slug}', [OpportunityController::class, 'show'])->name('opportunities.show');
Route::post('/opportunities/{opportunity:slug}/apply', [OpportunityController::class, 'apply'])->middleware(['auth', 'verified'])->name('opportunities.apply');
Route::get('/opportunities', function (Request $r) {
    $q = Opportunity::with('category')->where('status', 'published');
    if (! auth()->check()) {
        $q->where('is_member_only', false);
    }
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('title', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%")
                ->orWhere('organization', 'like', "%$s%")
                ->orWhere('location', 'like', "%$s%");
        });
    }
    if ($r->filled('category')) {
        $q->where('category_id', $r->category);
    }
    if ($r->filled('remote')) {
        $q->where('is_remote', $r->remote === '1');
    }

    return view('pages.opportunities', [
        'opportunities' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'category' => $r->category,
        'remote' => $r->remote,
        'categories' => OpportunityCategory::all(),
    ]);
})->name('opportunities');
Route::get('/members/{username}', [ProfileController::class, 'show'])->name('members.show');
Route::post('/members/{username}/contact', [ProfileController::class, 'contact'])
    ->middleware(['auth', 'verified', 'throttle:5,1'])
    ->name('members.contact');
Route::get('/chapters/{slug}', [ChapterController::class, 'show'])->name('chapters.show');
Route::post('/chapters/{chapter}/join', [ChapterController::class, 'join'])->middleware(['auth', 'verified'])->name('chapters.join');
Route::post('/chapters/{chapter}/leave', [ChapterController::class, 'leave'])->middleware(['auth', 'verified'])->name('chapters.leave');
Route::get('/chapters', function (Request $r) {
    $q = Chapter::where('status', 'active');
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('name', 'like', "%$s%")
                ->orWhere('location', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%");
        });
    }

    return view('pages.chapters', [
        'chapters' => $q->latest()->get(),
        'search' => $r->search,
    ]);
})->name('chapters');
Route::get('/news', function () {
    $announcements = Announcement::where('is_published', true)
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->take(9)
        ->get();

    $articles = Article::approved()
        ->with('author')
        ->orderByDesc('published_at')
        ->take(6)
        ->get();

    return view('pages.news', compact('announcements', 'articles'));
})->name('news');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy_policy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/community-guidelines', 'pages.community-guidelines')->name('community_guidelines');
