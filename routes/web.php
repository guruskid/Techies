<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Chapter\ChapterController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\HubDashboardController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\MyProfileController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Opportunity\OpportunityController;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hub;
use App\Models\HubApplication;
use App\Models\NewsletterSubscriber;
use App\Models\Opportunity;
use App\Models\OpportunityCategory;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/coming-soon', 'pages.coming-soon')->name('coming.soon');
Route::get('/sitemap.xml', function () {
    $urls = [
        url('/'), url('/about'), url('/members'), url('/hubs'), url('/events'),
        url('/opportunities'), url('/chapters'), url('/contact'),
    ];
    foreach (Hub::where('is_public', true)->get() as $h) {
        $urls[] = url('/hubs/'.$h->slug);
    }
    foreach (Event::where('status', 'published')->get() as $e) {
        $urls[] = url('/events/'.$e->slug);
    }
    foreach (Opportunity::where('status', 'published')->get() as $o) {
        $urls[] = url('/opportunities/'.$o->slug);
    }
    foreach (Chapter::all() as $c) {
        $urls[] = url('/chapters/'.$c->slug);
    }
    foreach (User::where('role', 'member')->get() as $u) {
        if ($u->profile?->is_public && $u->profile?->username) {
            $urls[] = url('/members/'.$u->profile->username);
        }
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach (array_unique($urls) as $u) {
        $xml .= '  <url><loc>'.htmlspecialchars($u).'</loc></url>
';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::post('/newsletter', function (Request $r) {
    NewsletterSubscriber::firstOrCreate(['email' => $r->email], ['name' => $r->name]);

    return back();
})->name('newsletter.subscribe');
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
    Route::get('/hub/dashboard', [HubDashboardController::class, 'index'])->name('hub.dashboard');
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.alt');
    Route::get('/admin/members', [MemberController::class, 'index'])->name('admin.members.index');
    Route::get('/admin/members/{user}', [MemberController::class, 'show'])->name('admin.members.show');
    Route::post('/admin/members/{user}/suspend', [MemberController::class, 'suspend'])->name('admin.members.suspend');
    Route::post('/admin/members/{user}/restore', [MemberController::class, 'restore'])->name('admin.members.restore');
    Route::get('/admin/hubs', [App\Http\Controllers\Admin\HubController::class, 'index'])->name('admin.hubs.index');
    Route::get('/admin/hubs/{hub}', [App\Http\Controllers\Admin\HubController::class, 'show'])->name('admin.hubs.show');
    Route::post('/admin/hubs/{hub}/approve', [App\Http\Controllers\Admin\HubController::class, 'approve'])->name('admin.hubs.approve');
    Route::post('/admin/hubs/{hub}/reject', [App\Http\Controllers\Admin\HubController::class, 'reject'])->name('admin.hubs.reject');
    Route::post('/admin/hubs/{hub}/suspend', [App\Http\Controllers\Admin\HubController::class, 'suspend'])->name('admin.hubs.suspend');
});

// public pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/mission-and-vision', 'pages.mission-and-vision')->name('mission_and_vision');
Route::view('/leadership', 'pages.leadership')->name('leadership');
Route::view('/community', 'pages.community')->name('community');
Route::get('/members', function (Request $r) {
    $q = User::where('role', 'member')->with('profile');
    if ($r->filled('search')) {
        $s = $r->search;
        $q->where(function ($w) use ($s) {
            $w->where('name', 'like', "%$s%")
                ->orWhereHas('profile', function ($p) use ($s) {
                    $p->where('professional_title', 'like', "%$s%")
                        ->orWhere('technology_category', 'like', "%$s%")
                        ->orWhere('city', 'like', "%$s%")
                        ->orWhere('state', 'like', "%$s%");
                });
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
    $hub = Hub::create(array_merge($data, [
        'user_id' => $owner->id,
        'slug' => Str::slug($data['name']).'-'.uniqid(),
        'status' => 'pending',
        'is_verified' => false,
        'is_public' => true,
    ]));
    HubApplication::create(['hub_id' => $hub->id, 'status' => 'pending']);

    return redirect('/hubs')->with('success', 'Hub application submitted and pending review.');
})->name('hubs.register.store');
Route::get('/hubs/{slug}', [HubController::class, 'show'])->name('hubs.show');
Route::get('/hubs', function (Request $r) {
    $q = Hub::where('is_public', true);
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
Route::get('/events', function (Request $r) {
    $q = Event::with('category');
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
        'events' => $q->latest()->paginate(12)->withQueryString(),
        'search' => $r->search,
        'category' => $r->category,
        'state' => $r->state,
        'type' => $r->type,
        'categories' => EventCategory::all(),
    ]);
})->name('events');
Route::get('/opportunities/{slug}', [OpportunityController::class, 'show'])->name('opportunities.show');
Route::get('/opportunities', function (Request $r) {
    $q = Opportunity::with('category');
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
Route::get('/chapters/{slug}', [ChapterController::class, 'show'])->name('chapters.show');
Route::get('/chapters', function (Request $r) {
    $q = Chapter::query();
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
Route::view('/news', 'pages.news')->name('news');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy_policy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/community-guidelines', 'pages.community-guidelines')->name('community_guidelines');
