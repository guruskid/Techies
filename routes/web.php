<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\BenefitController as AdminBenefitController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\CohortController as AdminCohortController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\EventCategoryController as AdminEventCategoryController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\GrantController as AdminGrantController;
use App\Http\Controllers\Admin\IntroductionController as AdminIntroductionController;
use App\Http\Controllers\Admin\LoungeController as AdminLoungeController;
use App\Http\Controllers\Admin\Mail\AccountController as MailAccountController;
use App\Http\Controllers\Admin\Mail\BroadcastController as MailBroadcastController;
use App\Http\Controllers\Admin\Mail\MessageController as MailMessageController;
use App\Http\Controllers\Admin\Mail\TemplateController as MailTemplateController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MentorshipController as AdminMentorshipController;
use App\Http\Controllers\Admin\OpportunityCategoryController as AdminOpportunityCategoryController;
use App\Http\Controllers\Admin\OpportunityController as AdminOpportunityController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\ShowcaseController as AdminShowcaseController;
use App\Http\Controllers\Admin\SpeakerCallController as AdminSpeakerCallController;
use App\Http\Controllers\Admin\SpotlightController as AdminSpotlightController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\Chapter\ChapterController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\HubDashboardController;
use App\Http\Controllers\InboundMailController;
use App\Http\Controllers\Member\CohortController;
use App\Http\Controllers\Member\ContentController as MemberContentController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\FeedbackController;
use App\Http\Controllers\Member\GrantController;
use App\Http\Controllers\Member\IntroductionController;
use App\Http\Controllers\Member\LoungeController;
use App\Http\Controllers\Member\MentorshipController;
use App\Http\Controllers\Member\MyProfileController;
use App\Http\Controllers\Member\PassportController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\ReferralController;
use App\Http\Controllers\Member\ShowcaseController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\Opportunity\OpportunityController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SpeakerCallController;
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

Route::get('/benefits', [BenefitController::class, 'index'])->name('benefits.index');
Route::get('/projects', [ShowcaseController::class, 'index'])->name('projects.index');
Route::post('/benefits/{benefit}/redeem', [BenefitController::class, 'redeem'])->middleware(['auth', 'verified', 'membership'])->name('benefits.redeem');
Route::get('/programmes', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/certificates/{certificate:certificate_number}', [CertificateController::class, 'show'])->name('certificates.show');
Route::get('/membership', [MembershipController::class, 'show'])->name('membership.show');
Route::post('/membership/payment', [MembershipController::class, 'submitPayment'])
    ->middleware(['auth', 'verified'])
    ->name('membership.payment.submit');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MemberDashboard::class, 'index'])->name('dashboard');
    Route::get('/passport', [PassportController::class, 'show'])->name('passport.show');
    Route::get('/feedback', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::middleware('membership')->group(function () {
        Route::get('/lounge', [LoungeController::class, 'index'])->name('lounge.index');
        Route::get('/lounge/create', [LoungeController::class, 'create'])->name('lounge.create');
        Route::post('/lounge', [LoungeController::class, 'store'])->name('lounge.store');
        Route::get('/lounge/{post}', [LoungeController::class, 'show'])->name('lounge.show');
        Route::post('/lounge/{post}/reply', [LoungeController::class, 'reply'])->name('lounge.reply');
    });
    Route::get('/grants', [GrantController::class, 'index'])->name('grants.index');
    Route::get('/grants/{grant:slug}', [GrantController::class, 'show'])->name('grants.show');
    Route::post('/grants/{grant:slug}/apply', [GrantController::class, 'apply'])->middleware('membership')->name('grants.apply');
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/opportunities/{opportunity:slug}/referrals/offer', [ReferralController::class, 'storeOffer'])->middleware('membership')->name('referrals.offer');
    Route::post('/referral-offers/{offer}/request', [ReferralController::class, 'requestReferral'])->middleware('membership')->name('referrals.request');
    Route::post('/referrals/{referralRequest}/respond', [ReferralController::class, 'respond'])->name('referrals.respond');
    Route::get('/projects/create', [ShowcaseController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ShowcaseController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project:slug}/edit', [ShowcaseController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project:slug}', [ShowcaseController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project:slug}', [ShowcaseController::class, 'destroy'])->name('projects.destroy');
    Route::get('/cohorts', [CohortController::class, 'index'])->name('cohorts.index');
    Route::get('/cohorts/{cohort:slug}', [CohortController::class, 'show'])->name('cohorts.show');
    Route::post('/cohorts/{cohort:slug}/join', [CohortController::class, 'join'])->name('cohorts.join');
    Route::post('/cohorts/{cohort:slug}/leave', [CohortController::class, 'leave'])->name('cohorts.leave');
    Route::get('/introductions', [IntroductionController::class, 'index'])->name('introductions.index');
    Route::post('/introductions', [IntroductionController::class, 'store'])->name('introductions.store');
    Route::get('/mentorship', [MentorshipController::class, 'index'])->name('mentorship.index');
    Route::post('/mentorship', [MentorshipController::class, 'store'])->name('mentorship.store');
    Route::get('/profile', [MyProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [MyProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/username-availability', [MyProfileController::class, 'checkUsername'])->name('profile.username.check')->middleware('throttle:30,1');
    Route::get('/hub/dashboard', [HubDashboardController::class, 'index'])->name('hub.dashboard');
    Route::get('/hub/team', [HubDashboardController::class, 'team'])->name('hub.team');

    Route::prefix('member/content')->name('member.content.')->group(function () {
        Route::get('/', [MemberContentController::class, 'index'])->name('index');
        Route::get('/create', [MemberContentController::class, 'create'])->middleware('membership')->name('create');
        Route::post('/upload-image', [MemberContentController::class, 'uploadImage'])->middleware('membership')->name('upload-image');
        Route::post('/', [MemberContentController::class, 'store'])->middleware('membership')->name('store');
        Route::get('/{article}/edit', [MemberContentController::class, 'edit'])->middleware('membership')->name('edit');
        Route::patch('/{article}', [MemberContentController::class, 'update'])->middleware('membership')->name('update');
        Route::post('/{article}/submit', [MemberContentController::class, 'submit'])->middleware('membership')->name('submit');
        Route::delete('/{article}', [MemberContentController::class, 'destroy'])->middleware('membership')->name('destroy');
    });

    Route::middleware('event-opportunity')->group(function () {
        Route::get('/manage/events', [AdminEventController::class, 'index'])->name('admin.events.index');
        Route::get('/manage/events/create', [AdminEventController::class, 'create'])->name('admin.events.create');
        Route::post('/manage/events', [AdminEventController::class, 'store'])->name('admin.events.store');
        Route::get('/manage/events/{event}/edit', [AdminEventController::class, 'edit'])->name('admin.events.edit');
        Route::get('/manage/events/{event}/registrations', [AdminEventController::class, 'registrations'])->name('admin.events.registrations');
        Route::get('/manage/events/{event}/registrations/export', [AdminEventController::class, 'exportRegistrations'])->name('admin.events.registrations.export');
        Route::post('/manage/events/{event}/registrations/{registration}/check-in', [AdminEventController::class, 'checkIn'])->name('admin.events.registrations.check-in');
        Route::post('/manage/events/{event}/registrations/{registration}/payment', [AdminEventController::class, 'updatePayment'])->name('admin.events.registrations.payment');
        Route::patch('/manage/events/{event}', [AdminEventController::class, 'update'])->name('admin.events.update');
        Route::delete('/manage/events/{event}', [AdminEventController::class, 'destroy'])->name('admin.events.destroy');

        Route::get('/manage/opportunities', [AdminOpportunityController::class, 'index'])->name('admin.opportunities.index');
        Route::get('/manage/opportunities/create', [AdminOpportunityController::class, 'create'])->name('admin.opportunities.create');
        Route::post('/manage/opportunities', [AdminOpportunityController::class, 'store'])->name('admin.opportunities.store');
        Route::get('/manage/opportunities/{opportunity}/edit', [AdminOpportunityController::class, 'edit'])->name('admin.opportunities.edit');
        Route::patch('/manage/opportunities/{opportunity}', [AdminOpportunityController::class, 'update'])->name('admin.opportunities.update');
        Route::post('/manage/opportunities/{opportunity}/applications/{application}', [AdminOpportunityController::class, 'updateApplication'])->name('admin.opportunities.applications.update');
        Route::get('/manage/opportunities/{opportunity}/applications/export', [AdminOpportunityController::class, 'exportApplications'])->name('admin.opportunities.applications.export');
        Route::delete('/manage/opportunities/{opportunity}', [AdminOpportunityController::class, 'destroy'])->name('admin.opportunities.destroy');

        Route::get('/manage/speaker-calls', [AdminSpeakerCallController::class, 'index'])->name('admin.speaker-calls.index');
        Route::get('/manage/speaker-calls/create', [AdminSpeakerCallController::class, 'create'])->name('admin.speaker-calls.create');
        Route::post('/manage/speaker-calls', [AdminSpeakerCallController::class, 'store'])->name('admin.speaker-calls.store');
        Route::get('/manage/speaker-calls/{speakerCall}/edit', [AdminSpeakerCallController::class, 'edit'])->name('admin.speaker-calls.edit');
        Route::patch('/manage/speaker-calls/{speakerCall}', [AdminSpeakerCallController::class, 'update'])->name('admin.speaker-calls.update');
        Route::get('/manage/speaker-calls/{speakerCall}/submissions', [AdminSpeakerCallController::class, 'submissions'])->name('admin.speaker-calls.submissions');
        Route::post('/manage/speaker-calls/{speakerCall}/submissions/{submission}', [AdminSpeakerCallController::class, 'review'])->name('admin.speaker-calls.submissions.review');
        Route::delete('/manage/speaker-calls/{speakerCall}', [AdminSpeakerCallController::class, 'destroy'])->name('admin.speaker-calls.destroy');
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
        Route::post('/admin/members/{user}/membership/confirm', [MemberController::class, 'confirmMembership'])->name('admin.members.membership.confirm');
        Route::post('/admin/members/{user}/membership/waive', [MemberController::class, 'waiveMembership'])->name('admin.members.membership.waive');
        Route::get('/admin/hubs', [App\Http\Controllers\Admin\HubController::class, 'index'])->name('admin.hubs.index');
        Route::get('/admin/hubs/{hub}', [App\Http\Controllers\Admin\HubController::class, 'show'])->name('admin.hubs.show');
        Route::post('/admin/hubs/{hub}/approve', [App\Http\Controllers\Admin\HubController::class, 'approve'])->name('admin.hubs.approve');
        Route::post('/admin/hubs/{hub}/reject', [App\Http\Controllers\Admin\HubController::class, 'reject'])->name('admin.hubs.reject');
        Route::post('/admin/hubs/{hub}/suspend', [App\Http\Controllers\Admin\HubController::class, 'suspend'])->name('admin.hubs.suspend');
        Route::get('/admin/categories', function () {
            return view('admin.categories.index');
        })->name('admin.categories.index');

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

        Route::get('/admin/chapters', [AdminChapterController::class, 'index'])->name('admin.chapters.index');
        Route::get('/admin/chapters/create', [AdminChapterController::class, 'create'])->name('admin.chapters.create');
        Route::post('/admin/chapters', [AdminChapterController::class, 'store'])->name('admin.chapters.store');
        Route::get('/admin/chapters/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('admin.chapters.edit');
        Route::patch('/admin/chapters/{chapter}', [AdminChapterController::class, 'update'])->name('admin.chapters.update');
        Route::delete('/admin/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('admin.chapters.destroy');

        Route::get('/admin/grants', [AdminGrantController::class, 'index'])->name('admin.grants.index');
        Route::get('/admin/grants/create', [AdminGrantController::class, 'create'])->name('admin.grants.create');
        Route::post('/admin/grants', [AdminGrantController::class, 'store'])->name('admin.grants.store');
        Route::get('/admin/grants/{grant}/edit', [AdminGrantController::class, 'edit'])->name('admin.grants.edit');
        Route::patch('/admin/grants/{grant}', [AdminGrantController::class, 'update'])->name('admin.grants.update');
        Route::get('/admin/grants/{grant}/applications', [AdminGrantController::class, 'applications'])->name('admin.grants.applications');
        Route::post('/admin/grants/{grant}/applications/{application}', [AdminGrantController::class, 'review'])->name('admin.grants.review');
        Route::delete('/admin/grants/{grant}', [AdminGrantController::class, 'destroy'])->name('admin.grants.destroy');

        Route::get('/admin/lounge', [AdminLoungeController::class, 'index'])->name('admin.lounge.index');
        Route::post('/admin/lounge/{post}/pin', [AdminLoungeController::class, 'togglePin'])->name('admin.lounge.pin');
        Route::delete('/admin/lounge/{post}', [AdminLoungeController::class, 'destroy'])->name('admin.lounge.destroy');

        Route::get('/admin/spotlights', [AdminSpotlightController::class, 'index'])->name('admin.spotlights.index');
        Route::get('/admin/spotlights/create', [AdminSpotlightController::class, 'create'])->name('admin.spotlights.create');
        Route::post('/admin/spotlights', [AdminSpotlightController::class, 'store'])->name('admin.spotlights.store');
        Route::get('/admin/spotlights/{spotlight}/edit', [AdminSpotlightController::class, 'edit'])->name('admin.spotlights.edit');
        Route::patch('/admin/spotlights/{spotlight}', [AdminSpotlightController::class, 'update'])->name('admin.spotlights.update');
        Route::delete('/admin/spotlights/{spotlight}', [AdminSpotlightController::class, 'destroy'])->name('admin.spotlights.destroy');

        Route::get('/admin/referrals', [AdminReferralController::class, 'index'])->name('admin.referrals.index');

        Route::get('/admin/showcase', [AdminShowcaseController::class, 'index'])->name('admin.showcase.index');
        Route::post('/admin/showcase/{project}', [AdminShowcaseController::class, 'moderate'])->name('admin.showcase.moderate');
        Route::delete('/admin/showcase/{project}', [AdminShowcaseController::class, 'destroy'])->name('admin.showcase.destroy');

        Route::get('/admin/cohorts', [AdminCohortController::class, 'index'])->name('admin.cohorts.index');
        Route::get('/admin/cohorts/create', [AdminCohortController::class, 'create'])->name('admin.cohorts.create');
        Route::post('/admin/cohorts', [AdminCohortController::class, 'store'])->name('admin.cohorts.store');
        Route::get('/admin/cohorts/{cohort}/edit', [AdminCohortController::class, 'edit'])->name('admin.cohorts.edit');
        Route::patch('/admin/cohorts/{cohort}', [AdminCohortController::class, 'update'])->name('admin.cohorts.update');
        Route::delete('/admin/cohorts/{cohort}', [AdminCohortController::class, 'destroy'])->name('admin.cohorts.destroy');

        Route::get('/admin/introductions', [AdminIntroductionController::class, 'index'])->name('admin.introductions.index');
        Route::post('/admin/introductions/{introRequest}', [AdminIntroductionController::class, 'update'])->name('admin.introductions.update');

        Route::get('/admin/feedback', [AdminFeedbackController::class, 'index'])->name('admin.feedback.index');

        Route::get('/admin/certificates', [AdminCertificateController::class, 'index'])->name('admin.certificates.index');
        Route::get('/admin/certificates/create', [AdminCertificateController::class, 'create'])->name('admin.certificates.create');
        Route::post('/admin/certificates', [AdminCertificateController::class, 'store'])->name('admin.certificates.store');
        Route::delete('/admin/certificates/{certificate}', [AdminCertificateController::class, 'destroy'])->name('admin.certificates.destroy');

        Route::get('/admin/mentorship', [AdminMentorshipController::class, 'index'])->name('admin.mentorship.index');
        Route::post('/admin/mentorship/{mentorshipRequest}/match', [AdminMentorshipController::class, 'match'])->name('admin.mentorship.match');
        Route::post('/admin/mentorship/{mentorshipRequest}/close', [AdminMentorshipController::class, 'close'])->name('admin.mentorship.close');

        Route::get('/admin/benefits', [AdminBenefitController::class, 'index'])->name('admin.benefits.index');
        Route::get('/admin/benefits/create', [AdminBenefitController::class, 'create'])->name('admin.benefits.create');
        Route::post('/admin/benefits', [AdminBenefitController::class, 'store'])->name('admin.benefits.store');
        Route::get('/admin/benefits/{benefit}/edit', [AdminBenefitController::class, 'edit'])->name('admin.benefits.edit');
        Route::patch('/admin/benefits/{benefit}', [AdminBenefitController::class, 'update'])->name('admin.benefits.update');
        Route::delete('/admin/benefits/{benefit}', [AdminBenefitController::class, 'destroy'])->name('admin.benefits.destroy');

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
    if ($r->filled('open_to') && in_array($r->open_to, ['open_to_work', 'open_to_mentor', 'open_to_collaborate', 'available_for_speaking', 'hiring'], true)) {
        $q->whereHas('profile', fn ($p) => $p->where($r->open_to, true));
    }

    return view('pages.members', [
        'members' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'technology' => $r->technology,
        'state' => $r->state,
        'experience' => $r->experience,
        'openTo' => $r->open_to,
    ]);
})->name('members');
Route::get('/hubs/register', [HubController::class, 'register'])->name('hubs.register');
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
})->middleware(['auth', 'membership'])->name('hubs.register.store');
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
    $organizationType = $r->query('organization_type');
    $organizationType = is_string($organizationType) ? $organizationType : '';
    if ($organizationType !== '') {
        $q->where('organization_type', $organizationType);
    }
    $organizationTypes = Hub::query()
        ->where('is_public', true)
        ->where('status', 'approved')
        ->whereNotNull('organization_type')
        ->where('organization_type', '!=', '')
        ->distinct()
        ->orderBy('organization_type')
        ->pluck('organization_type');

    return view('pages.hubs', [
        'hubs' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'state' => $r->state,
        'organizationType' => $organizationType,
        'organizationTypes' => $organizationTypes,
    ]);
})->name('hubs');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event:slug}/calendar', [EventController::class, 'calendar'])->name('events.calendar');
Route::post('/events/{event:slug}/register', [EventController::class, 'register'])->middleware(['auth', 'verified', 'membership'])->name('events.register');
Route::get('/events/{event:slug}/check-in/{token}', [EventController::class, 'checkIn'])->middleware(['auth', 'verified', 'membership'])->name('events.check-in');
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
Route::get('/speaker-calls', [SpeakerCallController::class, 'index'])->name('speaker-calls.index');
Route::get('/speaker-calls/{speakerCall:slug}', [SpeakerCallController::class, 'show'])->name('speaker-calls.show');
Route::post('/speaker-calls/{speakerCall:slug}', [SpeakerCallController::class, 'submit'])
    ->middleware(['auth', 'verified', 'membership'])
    ->name('speaker-calls.submit');
Route::get('/opportunities/{slug}', [OpportunityController::class, 'show'])->name('opportunities.show');
Route::post('/opportunities/{opportunity:slug}/apply', [OpportunityController::class, 'apply'])->middleware(['auth', 'verified', 'membership'])->name('opportunities.apply');
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
    ->middleware('throttle:5,1')
    ->name('members.contact');
Route::get('/chapters/{slug}', [ChapterController::class, 'show'])->name('chapters.show');
Route::post('/chapters/{chapter}/join', [ChapterController::class, 'join'])->middleware(['auth', 'verified', 'membership'])->name('chapters.join');
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
