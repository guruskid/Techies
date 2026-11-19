<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\Hub;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        return view('admin.events.index', [
            'events' => $query->withCount([
                'registrations',
                'registrations as registered_count' => fn ($builder) => $builder->where('status', EventRegistration::STATUS_REGISTERED),
                'registrations as waitlisted_count' => fn ($builder) => $builder->where('status', EventRegistration::STATUS_WAITLISTED),
            ])->latest()->paginate(20)->withQueryString(),
            'categories' => EventCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Attendee list for a single event with waitlist and check-in management.
     */
    public function registrations(Request $request, Event $event): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $registrations = $event->registrations()
            ->with('user')
            ->when(in_array($status, [
                EventRegistration::STATUS_REGISTERED,
                EventRegistration::STATUS_WAITLISTED,
                EventRegistration::STATUS_CANCELLED,
            ], true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('user', function ($builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.events.registrations', [
            'event' => $event,
            'registrations' => $registrations,
            'status' => $status,
            'search' => $search,
            'counts' => [
                'registered' => $event->registrations()->where('status', EventRegistration::STATUS_REGISTERED)->count(),
                'waitlisted' => $event->registrations()->where('status', EventRegistration::STATUS_WAITLISTED)->count(),
                'cancelled' => $event->registrations()->where('status', EventRegistration::STATUS_CANCELLED)->count(),
                'checked_in' => $event->registrations()->whereNotNull('checked_in_at')->count(),
                'unpaid' => $event->registrations()->where('payment_status', EventRegistration::PAYMENT_UNPAID)->count(),
                'paid' => $event->registrations()->where('payment_status', EventRegistration::PAYMENT_PAID)->count(),
            ],
        ]);
    }

    /**
     * Record an offline payment or refund for an attendee.
     */
    public function updatePayment(Request $request, Event $event, EventRegistration $registration): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);

        $data = $request->validate([
            'payment_status' => ['required', Rule::in(array_keys(EventRegistration::paymentStatuses()))],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $paymentStatus = $data['payment_status'];

        $registration->update([
            'payment_status' => $paymentStatus,
            'payment_reference' => $data['payment_reference'] ?? null,
            'paid_at' => $paymentStatus === EventRegistration::PAYMENT_PAID
                ? ($registration->paid_at ?? now())
                : null,
            'refunded_at' => $paymentStatus === EventRegistration::PAYMENT_REFUNDED ? now() : null,
        ]);

        AuditLogger::log('event.registration.payment_updated', $registration, [
            'event' => $event->title,
            'payment_status' => $paymentStatus,
            'payment_reference' => $data['payment_reference'] ?? null,
        ]);

        return back()->with('success', $registration->user?->name.' is now marked '.str_replace('_', ' ', $paymentStatus).'.');
    }

    /**
     * Toggle attendance check-in for a registration.
     */
    public function checkIn(Event $event, EventRegistration $registration): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);
        abort_unless($registration->status === EventRegistration::STATUS_REGISTERED, 422, 'Only confirmed attendees can be checked in.');

        $registration->update([
            'checked_in_at' => $registration->checked_in_at ? null : now(),
        ]);

        AuditLogger::log('event.registration.checked_in', $registration, [
            'event' => $event->title,
            'checked_in' => (bool) $registration->checked_in_at,
        ]);

        return back()->with('success', $registration->checked_in_at
            ? $registration->user?->name.' is checked in.'
            : $registration->user?->name.' is no longer checked in.');
    }

    /**
     * Download the attendee list as a CSV file.
     */
    public function exportRegistrations(Event $event): StreamedResponse
    {
        $registrations = $event->registrations()->with('user.profile')->orderBy('created_at')->get();

        AuditLogger::log('event.registrations.exported', $event, ['event' => $event->title]);

        return response()->streamDownload(function () use ($registrations): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Status', 'Payment status', 'Payment reference', 'Paid at', 'Registered at', 'Waitlisted at', 'Checked in at'], ',', '"', '');

            foreach ($registrations as $registration) {
                fputcsv($handle, [
                    $registration->user?->name,
                    $registration->user?->email,
                    $registration->user?->profile?->phone,
                    $registration->status,
                    $registration->payment_status,
                    $registration->payment_reference,
                    $registration->paid_at?->toDateTimeString(),
                    $registration->created_at?->toDateTimeString(),
                    $registration->waitlisted_at?->toDateTimeString(),
                    $registration->checked_in_at?->toDateTimeString(),
                ], ',', '"', '');
            }

            fclose($handle);
        }, 'event-'.$event->slug.'-attendees.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', [
            'categories' => EventCategory::orderBy('name')->get(),
            'hosts' => $this->hosts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $coverImage = null;
        if ($request->hasFile('cover_image')) {
            $coverImage = '/storage/'.$request->file('cover_image')->store('events', 'public');
        }

        $event = Event::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['title']),
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'host' => $data['host'] ?? null,
            'key_speakers' => $data['key_speakers'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'cover_image' => $coverImage,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'venue' => $data['venue'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'is_online' => (bool) ($data['is_online'] ?? false),
            'meeting_url' => $data['meeting_url'] ?? null,
            'registration_deadline' => $data['registration_deadline'] ?? null,
            'max_attendees' => $data['max_attendees'] ?? null,
            'registration_type' => $data['registration_type'],
            'fee' => $data['fee'] ?? null,
            'payment_url' => $data['payment_url'] ?? null,
            'payment_instructions' => $data['payment_instructions'] ?? null,
            'status' => $data['status'],
            'organizer_id' => auth()->id(),
        ]);

        AuditLogger::log('event.created', $event, ['title' => $event->title]);

        return redirect()->route('admin.events.index')->with('success', 'Event created.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', [
            'event' => $event,
            'categories' => EventCategory::orderBy('name')->get(),
            'hosts' => $this->hosts(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validated($request);

        $coverImage = $event->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverImage = '/storage/'.$request->file('cover_image')->store('events', 'public');
        }

        $event->update([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['title'], $event->id),
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'host' => $data['host'] ?? null,
            'key_speakers' => $data['key_speakers'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'cover_image' => $coverImage,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'venue' => $data['venue'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'is_online' => (bool) ($data['is_online'] ?? false),
            'meeting_url' => $data['meeting_url'] ?? null,
            'registration_deadline' => $data['registration_deadline'] ?? null,
            'max_attendees' => $data['max_attendees'] ?? null,
            'registration_type' => $data['registration_type'],
            'fee' => $data['fee'] ?? null,
            'payment_url' => $data['payment_url'] ?? null,
            'payment_instructions' => $data['payment_instructions'] ?? null,
            'status' => $data['status'],
        ]);

        AuditLogger::log('event.updated', $event, ['title' => $event->title]);

        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        AuditLogger::log('event.deleted', $event, ['title' => $event->title]);

        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:event_categories,id'],
            'host' => ['nullable', 'string', 'max:200'],
            'key_speakers' => ['nullable', 'string', 'max:5000'],
            'agenda' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'is_online' => ['nullable', 'boolean'],
            'meeting_url' => ['nullable', 'url', 'max:2048'],
            'registration_deadline' => ['nullable', 'date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'registration_type' => ['required', Rule::in(['free', 'paid'])],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'payment_url' => ['nullable', 'url', 'max:2048'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled'])],
        ]);
    }

    private function hosts(): Collection
    {
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'type' => 'member',
                'id' => $user->id,
                'label' => $user->name.' ('.$user->email.')',
                'value' => $user->name,
            ]);

        $hubs = Hub::where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Hub $hub) => [
                'type' => 'hub',
                'id' => $hub->id,
                'label' => $hub->name.' (Hub)',
                'value' => $hub->name,
            ]);

        return $users->concat($hubs)->sortBy('label')->values();
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'event';
        $candidate = $base;
        $suffix = 2;

        while (Event::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
