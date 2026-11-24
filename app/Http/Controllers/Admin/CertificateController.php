<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\User;
use App\Notifications\CertificateIssued;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        return view('admin.certificates.index', [
            'certificates' => Certificate::with('user', 'event')->latest('issued_at')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.certificates.create', [
            'certificate' => new Certificate(['issued_at' => now(), 'type' => 'attendance']),
            'members' => User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']),
            'events' => Event::orderByDesc('start_date')->limit(200)->get(['id', 'title', 'start_date']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $certificate = Certificate::create(array_merge($data, [
            'issued_by' => $request->user()->id,
            'certificate_number' => $this->uniqueNumber(),
        ]));

        AuditLogger::log('certificate.issued', $certificate, ['title' => $certificate->title]);

        $certificate->user?->notify(new CertificateIssued($certificate));

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate issued and emailed.');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        $certificate->delete();

        AuditLogger::log('certificate.deleted', $certificate, ['title' => $certificate->title]);

        return back()->with('success', 'Certificate deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'type' => ['required', 'in:'.implode(',', array_keys(Certificate::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'issued_at' => ['nullable', 'date'],
        ]);
    }

    private function uniqueNumber(): string
    {
        do {
            $number = 'MWTT-CERT-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}
