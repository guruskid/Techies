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
            'membership_fee' => [
                'label' => 'Membership fee (one time)',
                'default' => '15000',
                'rules' => 'nullable|numeric|min:0',
                'type' => 'text',
            ],
            'membership_currency' => [
                'label' => 'Membership currency',
                'default' => 'NGN',
                'rules' => 'nullable|string|max:10',
                'type' => 'text',
            ],
            'membership_payment_url' => [
                'label' => 'Membership payment link',
                'default' => '',
                'rules' => 'nullable|url|max:2048',
                'type' => 'text',
            ],
            'membership_payment_instructions' => [
                'label' => 'Membership payment instructions',
                'default' => '',
                'rules' => 'nullable|string|max:2000',
                'type' => 'textarea',
            ],
            'membership_benefits' => [
                'label' => 'Membership benefits (one per line)',
                'default' => "Member events, workshops and meetups\nA public profile that showcases your work\nMentorship and peer learning circles\nOpportunity applications and career support\nSpeaker calls, volunteering and chapter membership",
                'rules' => 'nullable|string|max:3000',
                'type' => 'textarea',
            ],
            'membership_guarantee' => [
                'label' => 'Membership guarantee',
                'default' => 'If the community is not a fit, contact us within 14 days of joining and we will review your refund.',
                'rules' => 'nullable|string|max:1000',
                'type' => 'textarea',
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
