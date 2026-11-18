<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Editable organisation settings, their labels and validation rules.
     *
     * @return array<string, array{label: string, default: string, rules: string, type: string}>
     */
    public static function fields(): array
    {
        return [
            'site_tagline' => [
                'label' => 'Site tagline',
                'default' => 'A non-profit technology community connecting people, hubs and opportunities.',
                'rules' => 'nullable|string|max:160',
                'type' => 'text',
            ],
            'contact_email' => [
                'label' => 'Contact email',
                'default' => (string) config('techies.contact_email'),
                'rules' => 'nullable|email|max:255',
                'type' => 'email',
            ],
            'support_phone' => [
                'label' => 'Support phone',
                'default' => '',
                'rules' => 'nullable|string|max:40',
                'type' => 'text',
            ],
            'default_meta_description' => [
                'label' => 'Default meta description',
                'default' => 'Meet with the Techies is a non-profit technology community connecting professionals, developers, innovators, founders, students and technology hubs.',
                'rules' => 'nullable|string|max:200',
                'type' => 'textarea',
            ],
            'community_guidelines_url' => [
                'label' => 'Community guidelines URL',
                'default' => '/community-guidelines',
                'rules' => 'nullable|string|max:255',
                'type' => 'text',
            ],
        ];
    }

    public function edit(): View
    {
        $fields = static::fields();
        $values = Setting::whereIn('key', array_keys($fields))->pluck('value', 'key');

        return view('super-admin.settings.edit', compact('fields', 'values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = static::fields();

        $data = $request->validate(
            collect($fields)->mapWithKeys(fn (array $field, string $key) => [$key => $field['rules']])->all()
        );

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Setting::forget($key);
        }

        AuditLogger::log('settings.updated', null, ['keys' => array_keys($data)]);

        return back()->with('success', 'Organisation settings updated.');
    }
}
