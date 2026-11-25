<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function index(): View
    {
        return view('admin.operations.index', [
            'queuedJobs' => DB::table('jobs')->count(),
            'readyJobs' => DB::table('jobs')->whereNull('reserved_at')->where('available_at', '<=', now()->timestamp)->count(),
            'oldestQueuedAt' => DB::table('jobs')->whereNull('reserved_at')->min('created_at'),
            'failedJobs' => DB::table('failed_jobs')->latest('failed_at')->paginate(15),
            'payments' => PaymentTransaction::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'queueConnection' => config('queue.default'),
            'mailMailer' => config('mail.default'),
            'paystackConfigured' => filled(config('services.paystack.secret_key')),
            'paystackWebhookConfigured' => filled(config('services.paystack.webhook_secret')),
            'schedulerHeartbeat' => Cache::get('operations.scheduler_heartbeat'),
        ]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        Artisan::call('queue:retry', ['id' => [$uuid], '--no-interaction' => true]);
        AuditLogger::log('operations.queue_job_retried', null, ['job_uuid' => $uuid]);

        return back()->with('success', 'The failed job was queued for another attempt.');
    }
}
