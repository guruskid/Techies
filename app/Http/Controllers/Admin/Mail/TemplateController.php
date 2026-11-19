<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.mail.templates.index', [
            'templates' => EmailTemplate::with('creator')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.mail.templates.form', ['template' => new EmailTemplate]);
    }

    public function store(Request $request): RedirectResponse
    {
        $template = EmailTemplate::create($this->validated($request) + ['created_by' => auth()->id()]);

        AuditLogger::log('mail.template.created', $template, ['name' => $template->name]);

        return redirect()->route('admin.mail.templates.index')->with('success', 'Template saved.');
    }

    public function edit(EmailTemplate $template): View
    {
        return view('admin.mail.templates.form', ['template' => $template]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $template->update($this->validated($request));

        AuditLogger::log('mail.template.updated', $template, ['name' => $template->name]);

        return redirect()->route('admin.mail.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $template->delete();

        AuditLogger::log('mail.template.deleted', null, ['name' => $template->name]);

        return back()->with('success', 'Template deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
        ]);
    }
}
