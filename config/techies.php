<?php

$appUrl = (string) env('APP_URL', 'http://localhost');
$appHost = parse_url(str_contains($appUrl, '://') ? $appUrl : 'http://'.$appUrl, PHP_URL_HOST) ?: 'localhost';

$profileDomain = (string) (env('PROFILE_DOMAIN') ?: $appHost);
$profileDomain = parse_url(str_contains($profileDomain, '://') ? $profileDomain : 'http://'.$profileDomain, PHP_URL_HOST) ?: $profileDomain;

return [
    /*
    |--------------------------------------------------------------------------
    | Coming soon mode
    |--------------------------------------------------------------------------
    |
    | When enabled, every route except the coming soon page, about page,
    | authentication screens and the health check will redirect to
    | /coming-soon. Disable via COMING_SOON=false in your .env file.
    |
    */
    'coming_soon' => env('COMING_SOON', false),

    'contact_email' => env('CONTACT_EMAIL', 'hello@meettechies.ng'),

    /*
    |--------------------------------------------------------------------------
    | Member profile subdomains
    |--------------------------------------------------------------------------
    |
    | Every member profile is published at {username}.{profile_domain}. By
    | default the domain is taken from APP_URL (with any "www." removed).
    | Override with PROFILE_DOMAIN when the app is served from another host.
    |
    */
    'profile_domain' => preg_replace('/^www\./i', '', $profileDomain),

    /*
    |--------------------------------------------------------------------------
    | Extra profile domains
    |--------------------------------------------------------------------------
    |
    | Additional hosts that should also resolve member profile subdomains.
    | Handy in local development, where username.localhost works in all
    | modern browsers. Comma-separate multiple hosts.
    |
    */
    'profile_extra_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('PROFILE_EXTRA_DOMAINS', 'localhost'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | Members cannot claim these as usernames because they are used, or may
    | be used, by the platform itself.
    |
    */
    'reserved_subdomains' => [
        'www', 'admin', 'administrator', 'api', 'app', 'auth', 'blog', 'cdn',
        'dashboard', 'dev', 'docs', 'help', 'mail', 'meet', 'members', 'news',
        'portal', 'secure', 'staging', 'static', 'status', 'support', 'test',
        'webmail', 'wiki',
    ],

    /*
    |--------------------------------------------------------------------------
    | Member content topics
    |--------------------------------------------------------------------------
    |
    | Members can submit articles for review. Each topic declares who may
    | approve it. Topics marked "super_admin" can only be approved by a
    | super administrator; everything else can be approved by any admin.
    |
    */
    'content_topics' => [
        'engineering' => [
            'label' => 'Software Engineering',
            'approval' => 'admin',
            'description' => 'Tutorials, architecture notes and engineering practice.',
        ],
        'career' => [
            'label' => 'Career & Growth',
            'approval' => 'admin',
            'description' => 'Career advice, mentorship stories and learning paths.',
        ],
        'product' => [
            'label' => 'Product & Design',
            'approval' => 'admin',
            'description' => 'Product thinking, UX research and design process.',
        ],
        'data-ai' => [
            'label' => 'Data & AI',
            'approval' => 'admin',
            'description' => 'Data science, machine learning and responsible AI.',
        ],
        'community' => [
            'label' => 'Community Stories',
            'approval' => 'admin',
            'description' => 'Member journeys, chapter stories and community impact.',
        ],
        'entrepreneurship' => [
            'label' => 'Entrepreneurship',
            'approval' => 'admin',
            'description' => 'Startups, freelancing and building technology businesses.',
        ],
        'security' => [
            'label' => 'Security & Privacy',
            'approval' => 'admin',
            'description' => 'Application security, privacy and safe engineering.',
        ],
        'policy-governance' => [
            'label' => 'Policy & Governance',
            'approval' => 'super_admin',
            'description' => 'Technology policy, regulation and governance topics.',
        ],
        'partnerships' => [
            'label' => 'Partnerships & Funding',
            'approval' => 'super_admin',
            'description' => 'Partnership announcements, grants and funding programmes.',
        ],
        'official-statements' => [
            'label' => 'Official MWTT Statements',
            'approval' => 'super_admin',
            'description' => 'Statements made on behalf of the organisation.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social profiles
    |--------------------------------------------------------------------------
    |
    | Set the organisation's profile URLs to override the discovery links.
    |
    */
    'social' => [
        'linkedin' => env('SOCIAL_LINKEDIN', 'https://www.linkedin.com/search/results/all/?keywords=Meet%20with%20the%20Techies'),
        'x' => env('SOCIAL_X', 'https://x.com/search?q=Meet%20with%20the%20Techies'),
        'instagram' => env('SOCIAL_INSTAGRAM', 'https://www.instagram.com/explore/tags/meetwiththetechies/'),
        'facebook' => env('SOCIAL_FACEBOOK', 'https://www.facebook.com/search/top?q=Meet%20with%20the%20Techies'),
    ],
];
