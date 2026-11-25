# Meet with the Techies — System Guide for Administrators

**Version:** current build (November 2026)
**Audience:** administrators, super administrators and anyone taking over the platform
**Purpose:** explain what the platform does, how each part behaves, and what an administrator is expected to do — without needing to log in first.

---

## How to use this guide

- Sections 1–4 explain the platform, its public site, accounts and permissions.
- Sections 5–11 explain each feature area in the order a community manager meets it.
- Sections 12–14 explain the admin centre, the super admin console and the mail centre.
- Sections 15–18 are reference material: every automated email, every scheduled job, SEO and security.
- Sections 19–20 are the operational runbook and the honest list of limitations.

If you only read one section before taking over, read **Section 4 (roles and access)** and **Section 12 (admin centre)**.

---

## 1. What the platform is

Meet with the Techies (MWTT) is a non-profit technology community platform. It does five things:

1. **Publishes a public community site** — members directory, hubs directory, chapters, events, opportunities, articles and news.
2. **Gives every account a member profile** — optionally public, published at its own subdomain.
3. **Runs a content workflow** — members write articles, staff review and publish them, with topic-dependent approval authority.
4. **Runs community operations** — event registration and waitlists, opportunity applications, hub applications, chapter membership and moderation.
5. **Provides a staff mail centre** — connected mailboxes with per-mailbox access, a shared inbox and sent log, attachments, reusable templates, bulk broadcasts and scheduled delivery.

There is no in-app social feed, no direct messaging inbox, no saved-items list and no in-app notification centre. **Email is the communication channel.** Every event that a member needs to know about produces an email, and staff send community-wide messages through the mail centre.

---

## 2. How the system is built and run

| Item | Detail |
|---|---|
| Framework | Laravel 13.x |
| Language | PHP 8.5 |
| Database | MySQL |
| Views | Blade templates |
| Styling | Tailwind CSS (loaded via CDN) |
| Interactivity | Alpine.js (loaded via CDN) |
| Front-end build | Not required for styling; no Vite dependency for the UI |
| Brand colours | Primary `#FF5C3D` (orange), secondary `#101E33` (navy) |
| File storage | Laravel `public` disk for avatars and event covers; private `local` disk for mail attachments |
| Mail | Laravel mailer; plus the separate mail-centre accounts described in Section 14 |
| Scheduling | Laravel scheduler (requires a cron entry on the server) |

### Two separate mail paths

This distinction matters:

1. **System email** — verification links, password resets, review alerts, registration confirmations, digest emails. These use the mail settings in the application's `.env` file (`MAIL_MAILER`, `MAIL_HOST`, etc.).
2. **Mail-centre email** — the shared mailboxes staff connect inside the admin centre, used for composing, receiving and broadcasting. These use their own SMTP/IMAP credentials stored in the database.

Configuring one does **not** configure the other. See Section 14.9.

### Background work

Laravel's scheduler must be running on the server:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled broadcasts also rely on a queue worker. Keep one running with `php artisan queue:work --tries=3`, supervised by your process manager. The per-minute `mail:broadcasts:send-due` command acts as a fallback if the worker is down.

The scheduled tasks are listed in Section 16.

### Local development defaults

The shipped development environment uses:

- `APP_URL=http://localhost:8000`
- `MAIL_MAILER=log` (email is written to the log, not delivered)
- `QUEUE_CONNECTION=database` (scheduled broadcasts are queued; other notifications send synchronously)
- a separate test database named `techies_test`

These are development settings, not production settings.

---

## 3. The public site

Anyone can browse the public site without an account, subject to the visibility rules below.

### 3.1 Page map

| Page | Path | What it shows |
|---|---|---|
| Home | `/` | Live platform counts, recent members, hubs, upcoming events, opportunities and chapters |
| About | `/about` | Organisation background |
| Mission and vision | `/mission-and-vision` | Mission and vision statements |
| Leadership | `/leadership` | Leadership information |
| Community | `/community` | Community overview |
| Members | `/members` | Directory of public, active member profiles |
| Member profile | `/members/{username}` | An individual profile (private profiles return 403 to other people) |
| Hubs | `/hubs` | Directory of approved, public hubs |
| Hub profile | `/hubs/{slug}` | Hub detail and its upcoming events |
| Register a hub | `/hubs/register` | Hub application form (submitting requires login) |
| Chapters | `/chapters` | Active chapters |
| Chapter detail | `/chapters/{slug}` | Chapter description, coordinators, upcoming events, join/leave |
| Events | `/events` | Upcoming published events with filters |
| Event detail | `/events/{slug}` | Full event detail, registration, waitlist, calendar download |
| Opportunities | `/opportunities` | Published opportunities with filters |
| Opportunity detail | `/opportunities/{slug}` | Full detail and application form |
| Articles | `/articles` | Approved articles with topic filter and search |
| Article detail | `/articles/{slug}` | Full article |
| News | `/news` | Published announcements and recent articles |
| Contact | `/contact` | Contact form |
| Privacy policy | `/privacy-policy` | Policy page |
| Terms | `/terms` | Terms page |
| Community guidelines | `/community-guidelines` | Guidelines page |
| Coming soon | `/coming-soon` | Holding page when coming-soon mode is on |
| Sitemap | `/sitemap.xml` | XML sitemap |
| Robots | `/robots.txt` | Crawler rules and sitemap reference |
| Article feed | `/feeds/articles.xml` | RSS feed of approved articles |
| Event feed | `/feeds/events.xml` | RSS feed of upcoming published events |

### 3.2 Visibility rules that apply everywhere

- **Members directory** shows only accounts that are `active` **and** have a profile with `is_public = true`.
- **Member profile** returns 404 when the account is not active. A private profile returns 403 to other visitors but remains visible to its owner.
- **Hub directory** shows only hubs with `status = approved` and `is_public = true`.
- **Hub detail** is visible to the public only when approved and public. Admins, super admins and the hub owner can preview any hub in any state.
- **Events list** shows only `published` events whose start date is today or later.
- **Event detail** requires `published` status; a past event remains viewable by direct link until it is changed.
- **Opportunities list** shows only `published` opportunities. Opportunities marked member-only are hidden from logged-out visitors and return 403 on direct access.
- **Articles list, article detail, sitemap and RSS** include only approved articles whose author is still active.

### 3.3 Coming-soon mode

Controlled by `COMING_SOON` in `.env`.

When enabled, every path redirects to `/coming-soon` except:

- the coming-soon page itself,
- the about page,
- login, register and forgot/reset password,
- email verification,
- the sitemap and health check,
- stored files.

Logged-in admins and super admins bypass the restriction entirely, so staff can keep working while the public site is closed.

### 3.4 Public forms

| Form | Behaviour |
|---|---|
| Contact | Sends an email to the configured contact address; the visitor's address is set as reply-to. Rate-limited to 5 submissions per minute. |
| Newsletter | Stores or reuses a newsletter subscriber, then confirms the subscription. |
| Hub application | Creates a pending hub, a pending hub application, a `hub_owner` role for the applicant and emails staff reviewers. Requires an account. |
| Member contact | Sends a message to a member through the platform using the sender as reply-to. Works for guests on public profiles (name and email required) and for signed-in members (no re-login prompt). Rate-limited to 5 per minute; a hidden honeypot field silently drops bot submissions. |

---

## 4. Accounts, roles and access

### 4.1 One account, many possible roles

Every account is also a community member and can own a public profile — including staff accounts. There is no separate "admin profile" type.

The **`users.role` column is the single authority** for the three primary roles:

| Primary role | Meaning | Where login lands |
|---|---|---|
| `member` | Standard community member | `/dashboard` |
| `admin` | Staff member with access to the admin centre | `/admin` |
| `super_admin` | Highest staff level, with the super admin console | `/super-admin` |

A hub owner with no staff role lands on `/hub/dashboard`.

### 4.2 Capability roles

The system also catalogs Spatie roles for finer-grained capability. These are useful as a description of responsibilities, but **they do not grant access to the admin centre on their own**. Admin-centre routes require the primary role `admin` or `super_admin`.

| Capability role | Seeded permissions (catalogue) |
|---|---|
| `super_admin` | All permissions |
| `admin` | All permissions except `roles.manage` and `settings.manage` |
| `content_manager` | `content.manage` |
| `membership_manager` | `members.view`, `members.update`, `members.suspend` |
| `events_manager` | `events.create`, `events.publish`, `events.manage` |
| `hub_manager` | `hubs.view`, `hubs.approve`, `hubs.reject`, `hubs.update` |
| `chapter_coordinator` | `chapters.manage` |
| `hub_owner` | No seeded permissions; assigned automatically when a hub is registered |
| `hub_administrator` | No seeded permissions |
| `hub_editor` | No seeded permissions |
| `member` | No permissions |

The practical rule for staff: **set the primary role to `admin` or `super_admin`.** Capability roles are bookkeeping on top.

### 4.3 How role checks work

- `isAdmin()` and `isSuperAdmin()` read `users.role`.
- Route protection (`role:admin|super_admin`, `role:super_admin`) uses the same rule.
- Spatie roles are still consulted for non-primary capabilities such as `hub_owner`.
- A stale Spatie pivot row cannot promote someone above their `users.role`.

This was a deliberate fix: previously a stale `super_admin` pivot row could keep granting super admin screens after the account had been demoted. The role column is now authoritative.

### 4.4 Sign-in rules

- Accounts with an unverified email are redirected to the verification screen when they use member-only features.
- Suspended accounts cannot sign in.
- A suspended account is also signed out of any existing session on its next page load.
- Login redirects by role as shown in the table above.

### 4.5 Automatic logout when access changes

Every login records a fingerprint of:

- the user's ID,
- their primary role,
- their account status,
- their assigned capability roles.

On every subsequent request the fingerprint is recomputed. If anything changed — a promotion, a demotion, a suspension, a role being added or removed, even a direct database edit — the session is destroyed and the person is sent to the login page with:

> Your access changed, so you were signed out. Please sign in again.

This happens on the next page refresh, not instantly. It is intentional: permissions must never outlive the session that was granted them.

### 4.6 Registration and account lifecycle

1. A visitor registers with name, email and password.
2. The account is created with primary role `member`, status `active`, membership status `unpaid`, an unverified email and a generated username.
3. The member verifies their email.
4. The member completes their profile and chooses whether it is public.
5. Staff can suspend or restore an account from the members screen or the super admin users screen.

Staff accounts are created by changing an existing account's role, not through a separate registration form.

### 4.7 Membership fee and onboarding

New members pay a one-time membership fee before using member-only features. The fee and copy are configured under **Super admin → Settings**:

- `membership_fee` — default `15000`.
- `membership_currency` — default `NGN`.
- `membership_payment_url` — an external payment link.
- `membership_payment_instructions` — bank transfer or other instructions.
- `membership_benefits` — one benefit per line, shown on `/membership`.
- `membership_guarantee` — the member promise shown on the page and in the FAQ.

When `PAYSTACK_SECRET_KEY` is configured, `/membership` shows a **Pay securely** button that starts Paystack Checkout. After Paystack confirms the transaction, the callback and webhook verify it and activate the membership automatically. The member receives the welcome email. Manual reference submission is hidden in this mode.

If Paystack is not configured, the manual flow is used:

1. The member registers and verifies their email. Membership status is `unpaid`.
2. They open `/membership`, pay using the link or instructions, and submit their payment reference. Status becomes `pending`.
3. Admins and super admins are emailed that a payment reference was submitted.
4. Staff open the member from **Members management** and choose **Confirm payment**. Status becomes `active` and the member is emailed a welcome guide with an onboarding checklist.
5. **Waive fee** sets the status to `exempt` for comped or staff-approved accounts.

Paystack configuration lives in `.env`:

```env
PAYSTACK_SECRET_KEY=sk_live_...
PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_WEBHOOK_SECRET=...
```

Set the Paystack webhook URL to `https://your-domain.com/payments/paystack/webhook`. The webhook verifies the signature and the transaction amount before activating anything.

Until the membership is `active` or `exempt`, the member can browse and edit their profile but cannot register for events, apply to opportunities, submit speaker proposals, join chapters, register a hub or publish member content. Those actions redirect to `/membership`.

The dashboard shows a membership banner so an unpaid member always knows the next step. Existing accounts and staff accounts were migrated as `active`.

### 4.8 Recovery: restoring a super administrator

If the last super admin is lost (for example because of a direct database edit), nobody can open `/super-admin`. Recovery requires server access:

1. Set `users.role = 'super_admin'` for the recovery account.
2. Ensure the Spatie pivot also has the role. The safest route is to run:

```bash
php artisan users:sync-roles --dry-run   # preview
php artisan users:sync-roles             # apply
```

3. Sign in again. The session fingerprint will be rebuilt at login.

`users:sync-roles` aligns the Spatie pivot with `users.role` and preserves extra capability roles such as `hub_owner`. It is safe to run repeatedly.

### 4.9 Member passport and retention tools

Every account receives a **member number** (`MWTT-YYYY-00000`), a **referral code** and, for existing members, a **founding member** badge.

**Member passport** — `/passport` in the member workspace. It shows:
- a digital membership card with a QR code,
- member number, status and member-since date,
- events, talks, articles, chapters, certificates and referrals,
- an onboarding checklist,
- mentorship status,
- a referral link and certificate list.

**Benefits** — `/benefits` lists active partner offers and resources. Manage them at **Admin → Member benefits**. Add a title, provider, description, offer label, category and link.

**Mentorship** — members request a mentor or volunteer as one at `/mentorship`. Admins match a mentee to an available mentor at **Admin → Mentorship**. Both members are emailed. The admin screen also closes completed cycles.

**Certificates** — issue verified certificates at **Admin → Certificates**. Types include attendance, speaking, volunteering, mentorship and chapter participation. Members are emailed, and anyone can verify a certificate at `/certificates/{number}`.

**Programmes** — `/programmes` lists upcoming events in the **Career Clinics** and **Office Hours** categories. Create these events through the normal event form.

**Referrals** — a member can share `/register?ref=CODE`. The new account records the referrer. Referral counts appear on the passport and the admin member screen.

**Opportunity matches** — `opportunities:send-matches` emails up to three opportunities matched by state, skills and technology category. It runs weekly on Monday at 09:00. Members opt in through the weekly digest preference on their profile.

**Thirty-day feedback** — `members:request-feedback` emails members 30 days after membership activation. Responses are stored and reviewed at **Admin → Member feedback**.

**Onboarding** — the member dashboard and passport show a checklist covering profile completion, a chapter, an event, an opportunity or talk, and mentorship.

### 4.10 Community and career features

**Availability signals** — members can mark themselves as open to work, open to mentor, open to collaborate, available for speaking or hiring. The members directory filters by these signals.

**Warm introductions** — members request an introduction at `/introductions`. Admins review requests at **Admin → Introductions** and mark them completed or declined. Both members are emailed.

**Cohorts** — admins create cohorts at **Admin → Cohorts**. Members browse `/cohorts`, join open cohorts and leave at any time. Capacity is enforced.

**Project showcase** — members submit projects at `/projects`. Admins approve, reject or feature them at **Admin → Showcase**. Approved projects appear publicly.

**Job referrals** — members can offer a referral on an opportunity and request a referral from another member. Members manage responses at `/referrals`; admins can review activity at **Admin → Referrals**.

**Member lounge** — a members-only discussion board at `/lounge`. Members create posts and replies. Admins can pin or delete posts at **Admin → Lounge**.

**Micro-grants** — admins create grant rounds at **Admin → Micro-grants**. Members apply from `/grants`. Admins review applications and members are emailed the decision.

**Benefit redemption** — add a redemption code and instructions to a benefit. Members reveal the code and record use from `/benefits`; redemptions are stored for reporting.

**QR event check-in** — each event has a check-in token. The attendee screen shows a QR code and link. Signed-in, registered members scan it to check in. The passport shows a monthly attendance streak.

**Member spotlights** — admins publish a spotlight at **Admin → Spotlights**. The latest published spotlight appears on the homepage.

**Monthly report** — `community:send-monthly-report` emails active members a short monthly summary and the current spotlight. It runs on the first day of each month at 08:00.

**Birthday wishes** — members can add a private date of birth on their profile. `members:send-birthday-wishes` emails active members on their birthday at 07:30, skips anyone who opted out of community email, and sends at most once per year. People born on 29 February are greeted on 28 February in non-leap years. The date is never shown on the public profile.

---

## 5. Member profiles and profile subdomains

### 5.1 What a member controls

From `/profile`, a member can edit:

- **About you:** photo, first and last name, public username, headline, biography.
- **Location and contact:** country, state, city, phone.
- **Professional details:** professional title, technology category, experience level, current organisation, chapter, availability.
- **Education and credentials:** education, certifications, languages.
- **Skills and interests:** multi-select skills and interests, likes and hobbies.
- **Work experience:** repeated entries with title, company, location, dates, current-role flag and description.
- **Projects:** repeated entries with title, description, URL and technologies.
- **Social links:** LinkedIn, GitHub, Facebook, X/Twitter, TikTok.
- **Visibility:** a public/private toggle.
- **Weekly digest:** an opt-in toggle.
- **Chapter:** a single-chapter selector.

Saving the profile also updates the account's display name from first and last name, syncs skills and interests, replaces work experience and project entries, and recalculates profile completion.

### 5.2 Profile completion

Completion is a percentage across 21 fields: first name, last name, phone, headline, professional title, technology category, experience level, current organisation, education, bio, country, state, city, LinkedIn, GitHub, Facebook, X/Twitter, TikTok, certifications, languages and availability.

The member dashboard shows the percentage and the missing fields.

### 5.3 Public profiles and subdomains

A public profile is published at:

```
https://{username}.{profile-domain}
```

- The default profile domain comes from `APP_URL`.
- `PROFILE_DOMAIN` can override it.
- `PROFILE_EXTRA_DOMAINS` lists additional hosts that should also resolve profile subdomains. It defaults to `localhost`, which makes `username.localhost` work in development.
- URLs use the host and port the visitor is currently using, so local development does not break.

Usernames:

- 3–30 characters.
- Lowercase letters, numbers and hyphens only.
- Must start and end with a letter or number.
- Must be unique.
- Cannot be on the reserved list (`www`, `admin`, `api`, `mail`, `dashboard`, `support`, and similar platform names).
- Are claimed **only** from profile settings.
- Are generated automatically at registration and can be changed later.
- Live availability can be checked from the profile screen.

**Important:** a private profile is not published on its subdomain (it returns 404). The owner can still preview it; other people cannot. Private profiles remain reachable by their owner at `/members/{username}`.

### 5.4 What the public profile shows

Avatar, name, headline, professional summary, current role and organisation, location, availability, work experience, projects, education, certifications, languages, skills, interests, likes, social links, chapter memberships, and a contact form for signed-in members.

The contact form does not reveal the recipient's email address. The email is delivered by the platform with the sender set as reply-to, so the recipient can reply directly.

### 5.5 Deliberate omissions

There is no personal website field and no portfolio section. Those were removed from both the profile and the public display.

---

## 6. Articles and the approval workflow

### 6.1 The member side

A member manages their articles at `/member/content`:

- Create a draft.
- Save and keep editing.
- Submit for review.
- Edit or delete a draft, or an article that was sent back for changes.
- See status counters: total, drafts, pending, approved, changes requested.

Once an article is pending or approved, the author can no longer edit it. Approved articles can only be changed by staff unpublishing them, which returns the article to the author as a draft.

Admins and super admins write articles through the same screen (`/member/content`), linked from the management centre as **Write an article**. The article is created under the staff account and follows the same topic-based review rules, so an admin can approve their own article for topics they may review, while a super-admin topic still needs a super administrator.

The body uses a rich-text editor (CKEditor) with headings, lists, links, quotes and image upload. Cover images and images inserted into the body are uploaded to the platform — no external URL is required. Content is sanitised on save before it is stored.

### 6.2 Article fields

| Field | Rules |
|---|---|
| Title | Required, up to 160 characters |
| Topic | Required, one of the configured topics |
| Excerpt | Optional, up to 300 characters |
| Body | Required, at least 100 characters, written in the rich-text editor |
| Cover image | Optional upload (JPG, PNG, GIF or WebP) |
| SEO title | Optional, up to 70 characters |
| SEO description | Optional, up to 200 characters |
| SEO keywords | Optional, up to 255 characters; the first keyword is the focus keyword |
| Canonical URL | Optional URL |
| Slug | Optional; generated from the title when blank |

Slugs are always unique, including against soft-deleted articles.

### 6.3 Statuses

| Status | Meaning |
|---|---|
| `draft` | Author is still working on it |
| `pending` | Submitted and waiting for review |
| `approved` | Published on the public site |
| `rejected` | Changes requested; author can revise and resubmit |

The interface labels `rejected` as "Changes requested" because that is what it means operationally.

### 6.4 The SEO gate

A member cannot submit an article until the SEO score is **at least 50%**. The score is the percentage of seven checks that pass:

1. SEO title is 30–60 characters.
2. Meta description is 120–160 characters.
3. The focus keyword appears in the title and in the body.
4. The slug is 80 characters or fewer and contains no spaces.
5. The excerpt is 80–300 characters.
6. The body is at least 600 characters of text.
7. A cover image is provided.

At 50%, four of the seven checks must pass. If submission is blocked, the screen lists exactly which checks are failing.

This gate applies to members submitting content. It does **not** prevent staff from approving an article that is already in the queue.

### 6.5 Topics and who may approve them

Topics and approval levels are configured in `config/techies.php`.

| Topic key | Label | Approver |
|---|---|---|
| `engineering` | Software Engineering | Admin |
| `career` | Career & Growth | Admin |
| `product` | Product & Design | Admin |
| `data-ai` | Data & AI | Admin |
| `community` | Community Stories | Admin |
| `entrepreneurship` | Entrepreneurship | Admin |
| `security` | Security & Privacy | Admin |
| `policy-governance` | Policy & Governance | **Super admin only** |
| `partnerships` | Partnerships & Funding | **Super admin only** |
| `official-statements` | Official MWTT Statements | **Super admin only** |

The rule is simple: **topics that speak for the organisation or touch policy require a super administrator.** Everything else can be handled by any admin.

### 6.6 What happens on submission

1. The article moves to `pending` with a submission timestamp.
2. Reviewers are emailed:
   - super admins only for restricted topics,
   - admins and super admins for ordinary topics.
3. The author is not emailed if they are themselves a reviewer.
4. The email links straight to the review screen and states whether it needs admin or super admin review.

### 6.7 The review screen

Admins open `/admin/content`. The list can be filtered by status, topic, approval level and search term, and shows counters including how many pending items need super admin review.

On an individual article, a reviewer can:

- **Approve** — publishes immediately. Sets the reviewer, review time and publication date, records an audit entry and emails the author.
- **Request changes** — requires review notes, sets the article back to `rejected`, records an audit entry and emails the author with the notes.
- **Unpublish** — only for published articles. Returns the article to `draft`, clears the publication date and records an audit entry.

A super admin also has **force publish**, which publishes an article regardless of the normal approval path. It is audited as an override.

Approval permission is enforced per article: an admin cannot approve a restricted topic even if they can see it in the queue.

### 6.8 Where articles appear

Approved articles appear on `/articles`, `/news`, the home page, the sitemap and the RSS feed. Each article records a view count and displays a reading time based on 220 words per minute.

---

## 7. Events

### 7.1 Event record

Staff manage every event at `/manage/events`. Hub owners (accounts carrying the `hub_owner` capability) and chapter coordinators also reach the same screens for their own records:

- Staff see and manage all events.
- A hub owner sees events they organise or that belong to their hub.
- A chapter coordinator sees events they organise.
- Members never see the address as "admin" in the browser; the routes live under `/manage`.

An event has:

- Title, slug, description and cover image upload. Description, agenda and requirements use the rich-text editor and are sanitised on save.
- Category.
- Host (free text, with member and approved-hub suggestions).
- Key speakers plus an optional **speaker photo gallery**: upload several images at once without holding Ctrl/Cmd, with previews and per-image removal. The public page shows them as a grid.
- Agenda and requirements (rich text).
- Contact email and phone.
- Start date (required), end date, start time, end time.
- Venue, city, state, country.
- Online flag and meeting link.
- Registration deadline, maximum attendees.
- Registration type: free or paid, plus an optional fee.
- Payment link and payment instructions (shown to registered attendees of paid events).
- Status: `draft`, `published` or `cancelled`.
- Organiser: the account that created it.

**Publishing is a staff decision.** When a hub owner or coordinator creates or edits an event, the record is forced to `draft` and the status control, publishing section and draft filters are hidden from them. Their list shows such records as "Awaiting review". Only an admin or super admin sets `published`; on first publication, active members in the event's state (or all active members when no state is recorded) are emailed. See Section 9.4.

**The platform tracks payments but does not process cards.** For a paid event, add an external payment link (for example a Paystack or Flutterwave page) and any bank-transfer instructions. Attendees pay outside the platform; an admin then confirms or refunds each registration from the attendee screen. See Section 7.7.

### 7.2 The public event page

- Only published events can be opened.
- The page shows the schedule, venue or online details, capacity and remaining spots, speakers, the speaker photo gallery, agenda, requirements and contact details.
- If a meeting link is set, a **Join Online** section is shown. The link itself is only revealed to confirmed attendees, the organiser and staff. Everyone else sees an explanation asking them to register.
- The page offers an `.ics` calendar download for published events.

The meeting link is private by default. It is included in the `.ics` file only for people who can already see it on the page — confirmed attendees, the organiser and staff. Waitlisted, cancelled and unregistered visitors get the event details without the link.

### 7.3 Registration, capacity and waitlist

Registration requires a verified signed-in account.

1. Registration closes automatically after the registration deadline.
2. If capacity is set and the event is full, the member is added to the **waitlist** and emailed.
3. If a seat is available, the member is registered and emailed a confirmation.
4. A member who already has a registration can re-register after cancelling.
5. A member can cancel from the event page.

When a confirmed attendee cancels, the **oldest waitlisted person is promoted automatically** and emailed that a spot opened. Cancelling a waitlist entry simply removes it.

### 7.4 Attendance and exports

From an event's attendee screen, admins can:

- filter by status (registered, waitlisted, cancelled) and search by name or email,
- see counts for registered, waitlisted, cancelled and checked in,
- **check in** or undo check-in for a confirmed attendee (only registered attendees can be checked in),
- **update payment status** for paid events (see Section 7.7),
- **export the attendee list as CSV**, including name, email, phone, status, payment status, payment reference, paid date, registration date, waitlist date and check-in time.

Check-ins, payment updates and exports are written to the audit log.

### 7.5 Reminders

A scheduled command emails confirmed attendees one day before the event. It only emails people who have not already been reminded, and it records the reminder time so nobody is emailed twice for the same event.

For online events, the reminder includes the meeting link. For in-person events, it includes the venue and city.

### 7.6 Calendar downloads

The `.ics` file contains the title, start and end, location (online link or venue), description and a link back to the event. If no end time is supplied, the calendar entry defaults to two hours.

For an online event, the meeting link is written into the calendar entry only for viewers who are allowed to see it on the event page. Guests and unregistered visitors receive `Online` as the location, without the join URL.

### 7.7 Paid events and payment tracking

When an event's registration type is `paid`:

1. Add the **fee**, an optional **payment link** and optional **payment instructions** when creating or editing the event.
2. A registration starts with payment status **unpaid**. The confirmation email tells the attendee the fee and, if set, links to the payment page.
3. The attendee's event page shows the fee, the payment status and the payment link or instructions. It never shows payment details to people who are not registered.
4. An admin opens the attendee screen and sets the status to **paid**, optionally recording a payment reference. The platform stamps the paid date.
5. Refunding sets the status to **refunded** and records the refund date.

Payment statuses are `not_required`, `unpaid`, `paid` and `refunded`. Free events use `not_required`.

The payment-link and instructions fields are disabled in the event form while the registration type is free, so they cannot be set accidentally.

When Paystack is configured, registered attendees can pay through Paystack Checkout from the event page. The callback and webhook verify the transaction and mark the registration **paid** automatically. Admins can still confirm or refund manually from the attendee screen, and refunds can be initiated from **Admin → Payments**.

Without Paystack, confirming a payment is a human decision based on the reference you record.

### 7.8 Call for speakers

Admins, hub owners and chapter coordinators can open a call for speakers at `/manage/speaker-calls`.

**Creating a call**
- Give it a title and a rich-text description.
- Add the topics speakers may choose from, one per line.
- Optionally link an event. The call then appears on that event page and inherits the event's hub and chapter.
- Set an opening date, a closing date and a status: `draft`, `open` or `closed`.

Without a linked event, a hub owner's call belongs to their hub and a chapter coordinator's call belongs to their chapter. Admins can create platform-wide calls. Drafts are hidden from the public, and submissions stop automatically once the closing date passes.

**What members see**

Open calls appear at `/speaker-calls`, in the main navigation under **Speak**, and on the linked event page. A signed-in, verified member submits:
- one of the published topics,
- a talk title,
- an abstract,
- an optional short bio.

A member can submit only once per call.

**Reviewing proposals**

The creator opens **Review** from the call list. Every submission shows the member, chosen topic, talk title, abstract and bio. The reviewer chooses **Approve** or **Reject**, optionally adds notes, and saves.

- Approving emails the speaker with the talk details and any notes.
- Rejecting emails the speaker with the decision and any notes.
- Saving the same decision again does not send a second email.
- Decisions are audited.

Approved speakers are listed publicly on the call page. Only staff, the creator, the owning hub and the relevant chapter coordinators can see or review a call.

---

## 8. Opportunities

### 8.1 Opportunity record

Staff manage opportunities at `/manage/opportunities`; hub owners and chapter coordinators reach the same screens for their own records, scoped exactly like events (Section 7.1). Fields:

- Title, slug, description (rich text), cover image URL.
- Category.
- Organisation and location.
- Remote flag.
- **Member-only** flag.
- Application link (external).
- Deadline.
- Eligibility (rich text).
- Status: `draft` or `published`.

Description and eligibility are written in the rich-text editor and sanitised on save. When a hub owner or coordinator creates or edits an opportunity, the record is forced to `draft`, the status control is hidden, and their list labels it "Awaiting review". Only staff publish; on first publication, active members in the hub's state (or all active members when there is no hub state) are emailed. Hub owners and coordinators can still review applications on their own opportunities, but publishing status is never shown to them.

### 8.2 Internal-first applications

The platform prefers on-platform applications:

- Signed-in, verified members can apply from the opportunity page with an optional cover letter (up to 3,000 characters).
- An application is created once per member per opportunity; applying again does not duplicate it.
- The applicant is emailed a receipt.
- The external application link remains available for organisations that require their own form.

Member-only opportunities are hidden from logged-out visitors, excluded from the public list and return 403 on direct access.

### 8.3 Application pipeline

Application statuses:

`applied` → `reviewing` → `shortlisted` → `accepted` or `rejected`

Admins update the status from the opportunity edit screen. **Every status change emails the applicant** with the new status. The change is audited.

### 8.4 Publishing and exports

- An opportunity becomes visible when its status changes from draft to published.
- On first publication, a "New opportunity" message goes to active members in the owning hub's state. If there is no hub state, it goes to every active account, staff included.
- Applicants can be exported as CSV with name, email, phone, status, application date and cover letter. Exports are audited.

### 8.5 Member follow-up

The mail centre can broadcast to "Applicants for an opportunity", which makes it possible to email a specific applicant pool without exporting addresses. See Section 14.7.

---

## 9. Hubs

### 9.1 What a hub is

A hub is a technology community organisation — a co-working space, community group, campus club or similar — with its own public profile and an owner account.

### 9.2 Registration and review

1. A signed-in member submits `/hubs/register` with organisation details, contact details, location, community size, programmes offered and social links.
2. The hub is created as `pending`, private and unverified.
3. A pending hub application is created.
4. The owner receives the `hub_owner` capability role.
5. Staff reviewers (admins and super admins) are emailed.
6. An account can own only one hub.

### 9.3 Admin decisions

From `/admin/hubs`, admins can:

- **Approve** — sets the hub to `approved`, verified and public, approves the application, and emails the owner.
- **Reject** — sets the hub to `rejected`, private and unverified, rejects the application and emails the owner.
- **Suspend** — sets the hub to `suspended` and private, removing it from the public directory. No email is sent and no audit entry is written.

Admins can preview any hub, in any state, from the admin detail screen. The hub owner can also preview their own hub while it is pending, rejected or suspended.

### 9.4 Hub owner dashboard

`/hub/dashboard` shows the owner's hub, counts for events, team members and applications, the five most recent events and the latest application status. If the account has no hub, it redirects to the registration form.

The member dashboard also shows a **Your hub** card for anyone who owns a hub, with shortcuts to manage the hub and to create an event or an opportunity. Creations from a hub account are always saved as drafts and need staff approval before they appear publicly. On approval, active members in the hub's state are emailed.

There is currently no separate hub-owner editing screen for the hub profile or team beyond the registration form.

### 9.5 Public hub page

Approved and public hubs appear in `/hubs` and on their own `/hubs/{slug}` page, which includes the hub description, contact details, location, social links and its next six published events.

---

## 10. Chapters

### 10.1 What a chapter is

A chapter is a regional or local community group with a name, slug, country, state, city/LGA, description, up to two coordinators and an `active` or `inactive` status.

Admins create and manage chapters at `/admin/chapters`. A chapter can name a **primary coordinator** and an optional **second coordinator** (the same person cannot hold both). Assigning or removing a coordinator emails that member, and both coordinators are treated as chapter leads:

- They see a coordination card on their member dashboard.
- They can create events and opportunities from `/manage`; everything they create is saved as a draft and needs staff approval before publishing.
- New members who join the chapter are emailed; the primary coordinator is notified of each join.

Descriptions use the rich-text editor and are sanitised on save.

### 10.2 Member membership

Signed-in, verified members can:

- open `/chapters/{slug}` and see the chapter and its next six events,
- **join** the chapter,
- **leave** the chapter.

On joining, the member is emailed a welcome message that includes the primary coordinator's name when one is assigned. That coordinator is also emailed that a new member joined. Leaving sends no email.

### 10.3 The chapter selector on the profile

The profile screen's "Add me to a chapter" selector **adds** a membership. It never removes other chapters. The join/leave buttons on a chapter page add and remove memberships directly.

Operational consequence: a member who belongs to several chapters can save their profile without losing any of them. To leave a chapter, use the leave button on that chapter's page.

### 10.4 Chapter visibility

Only `active` chapters appear publicly. Inactive chapters and their join/leave actions return 404.

---

## 11. Categories and taxonomy

### 11.1 Event categories

Managed at `/admin/event-categories`. Each has a name and a unique slug used for public filtering. Default categories seeded on install include Meetups, Conferences, Workshops, Hackathons, Networking, Webinars, Training, Career Events and Community Events.

### 11.2 Opportunity categories

Managed at `/admin/opportunity-categories`. Defaults include Jobs, Internships, Scholarships, Grants, Fellowships, Training, Mentorship, Hackathons and Competitions.

### 11.3 Categories landing page

`/admin/categories` is a landing page that links to both category managers.

### 11.4 Skills and interests

Skills are a seeded reference list used by member profiles and the member search (Software Development, Frontend, Backend, Full-Stack, Mobile, DevOps, Cybersecurity, Data Science, AI/ML, UI/UX, Product, Marketing, Networking, QA, Blockchain, Embedded, Technical Writing, Other). There is no admin screen for editing skills; changes require a developer or database update.

Event and opportunity categories can be created, edited and deleted from the admin centre. Deletions are audited.

---

## 12. The admin centre

### 12.1 Entry points

- `/admin` — dashboard with counts and the oldest pending articles.
- `/admin/manage` — the management centre, a card-based map of every admin area.

The navigation bar shows an **Admin** badge for admins and a **Super Admin** badge for super admins. The mobile menu includes the same links and the logout button.

### 12.2 Dashboard

The admin dashboard shows:

- member count,
- hub count and pending hubs,
- event count,
- opportunity count,
- chapter count,
- pending and published content counts,
- the six oldest pending articles with links to review.

### 12.3 Management centre

The management centre groups screens into four areas:

**People and places**
- Members — view, suspend and restore member accounts.
- Hubs — review, approve, reject, suspend and preview.
- Chapters — create and manage chapters.

**Community content**
- Content review — the article queue.
- Write an article — opens the member editor for staff to draft and submit their own articles.
- Announcements — create, edit, publish and delete announcements for the news page and member dashboard.
- Events — at `/manage/events`: create, edit, delete, manage attendees, export.
- Opportunities — at `/manage/opportunities`: create, edit, delete, manage applications, export.
- Call for speakers — at `/manage/speaker-calls`: open calls, collect topic proposals and email decisions.
- Categories — event and opportunity categories.

The event and opportunity screens live under `/manage`, not `/admin`, because hub owners and chapter coordinators use them too. The browser address never exposes a staff-only path to those users.

**Communication**
- Mail centre — accounts, inbox, sent, compose, templates, broadcasts.

**Super admin (visible only to super admins)**
- Users and roles.
- Settings and audit logs.

### 12.4 Announcements

`/admin/announcements` manages community announcements. The same announcements appear on `/news` and in the member dashboard.

- Create an announcement with a title, message, optional chapter and a publish toggle. The message uses the rich-text editor and is sanitised on save.
- Published announcements get a publication date and appear immediately.
- Unpublishing returns the item to draft and clears the date.
- Announcements can be edited or deleted. All three actions are audited.

Chapter-labelled announcements are still public; the chapter is a label, not an access restriction.

### 12.5 Members screen

`/admin/members` lists accounts whose primary role is `member`, with search by name or email and a status filter. Each member has a detail screen showing their profile, skills and interests.

Admins can suspend or restore members. An admin cannot suspend themselves or a super admin. Suspension takes effect on the member's next request and ends their session.

Note: the members screen only lists primary-role members. Staff accounts are managed in the super admin console.

### 12.6 Navigation and badges

The navbar adapts to the signed-in user:

- Super admins see a dark **Super Admin** badge and are linked to the super admin dashboard.
- Admins see an orange **Admin** badge and are linked to the admin dashboard.
- Everyone sees a logout button, on desktop and in the mobile menu.

---

## 13. The super admin console

Only accounts with primary role `super_admin` can open `/super-admin`.

### 13.1 Dashboard

Shows platform totals: all users, active members, staff, suspended accounts, pending content, pending content that requires super admin review, published content, pending hubs, events and opportunities.

It also shows:

- the oldest super-admin-only articles waiting for review,
- the six most recent accounts,
- all roles with user counts,
- the eight most recent audit entries.

### 13.2 Users and roles

`/super-admin/users` lists every account with search by name or email, role filter and status filter.

A super admin can:

- **Change a user's primary role.** This updates `users.role` and aligns the Spatie `member`/`admin`/`super_admin` assignment with the new role. It is audited. A super admin cannot change their own role.
- **Suspend or restore an account.** A super admin cannot suspend themselves or another super admin. Both actions are audited.

Safety rule: **the last remaining super admin cannot be demoted.**

Extra capability roles are preserved. If a hub owner also holds `hub_owner` and you change their primary role to `admin`, they keep `hub_owner`; only the primary-role tag is replaced. This means promotion or demotion does not silently strip responsibilities. Use `users:sync-roles` if the pivot ever drifts from `users.role`.

### 13.3 Roles screen

`/super-admin/roles` is a read-only catalogue of roles, their permissions and how many users hold each. There is no role editor in the UI; roles and permissions are seeded and changed by a developer.

### 13.4 Settings

`/super-admin/settings` edits organisation-wide values:

| Setting | Used by |
|---|---|
| Site tagline | The footer line on every public page |
| Contact email | Contact form recipient (overrides the config default) |
| Support phone | The public contact page |
| Default meta description | The meta description on pages that do not provide their own |
| Community guidelines URL | Stored, but not currently linked by any template |

Settings are cached and the cache is cleared on save. The change is audited.

### 13.5 Audit logs

`/super-admin/audit-logs` lists administrative actions with the acting user, action name, related record, change details and IP address. It can be filtered by action text and by user name or email, and paginates 30 entries at a time.

Actions currently recorded include:

- content approvals, change requests, unpublishing and force publishing,
- event creation, updates, deletion, check-ins and attendee exports,
- opportunity creation, updates, deletion, application status changes and applicant exports,
- chapter creation, updates and deletion,
- event and opportunity category changes,
- mail account changes, test sends, template changes, broadcasts and composed messages,
- user role changes, suspensions and restorations,
- settings changes.

**Gaps to be aware of:** hub approvals, rejections and suspensions are **not** audited. Member suspensions performed from the members screen are **not** audited (the super admin users screen does audit them). If you need a complete moderation trail, record those decisions separately for now.

### 13.6 Super admin content override

The super admin console includes the content queue and a **force publish** action. Force publishing bypasses the normal review requirement, sets the reviewer to the acting super admin and is audited as `content.force_published`.

---

## 14. The mail centre

The mail centre is a staff email workspace inside the admin area. It is separate from the system notification emails described in Section 15.

### 14.1 What it can do

- Connect multiple sending and receiving mail accounts.
- Choose a default account and share a mailbox with other admins as a viewer or manager.
- Send a test email per account and test the IMAP login.
- Compose and send HTML email with CC, BCC and file attachments.
- Receive mail into a shared inbox through IMAP polling or a provider webhook.
- Read messages, mark them read, see simple threads and download attachments.
- Keep a sent log with delivery status and errors.
- Save reusable templates.
- Prepare and send bulk broadcasts to defined audiences, with a per-recipient delivery record.
- Schedule a broadcast for a future time.
- Autosave a compose draft and restore it on return.

### 14.2 Accounts

Each account stores:

- display name, email address, from name, reply-to and signature,
- SMTP host, port, username, password and encryption (`tls`, `ssl` or `none`),
- IMAP host, port, username, password and encryption,
- active flag and default flag,
- an inbound webhook token and signing secret,
- a **Require a signed inbound webhook** switch,
- last sync time and last sync error.

Passwords and the signing secret are **encrypted at rest** and hidden from normal model output.

Only one account can be the default; setting a new default clears the old one. Deleting the default promotes the oldest active account.

**Mailbox access**

- Super admins can open every mailbox.
- The admin who created a mailbox always manages it.
- Other admins can be invited as a **viewer** (read the inbox, sent log and attachments) or a **manager** (also edit settings, sync, test and rotate credentials).
- Viewers and managers only see the mailboxes they were invited to. The account list, inbox, sent log, compose picker and broadcast history are all scoped.
- Sending from or broadcasting with a mailbox you cannot access is rejected.
- Sharing is edited on the mailbox screen. Ticking **Manage** is enough; it includes view.

### 14.3 Sending

- Each account is registered as a dynamic Laravel mailer at send time, so every account can use its own SMTP credentials without restarting the application.
- The body is wrapped in the branded MWTT email layout, with the account signature appended.
- Each send creates a record in the sent log. A failure stores the error message and the message remains visible as failed.
- Replies set `In-Reply-To` and `References` headers so they thread correctly in normal mail clients.
- The `From` address is always the connected account's address.
- Compose accepts up to five attachments of 10 MB each. Files are stored on the private local disk and attached to the outgoing message.
- The compose screen autosaves a single draft per user while typing. Reopening compose restores it. Sending or pressing **Discard draft** clears it.

### 14.4 Receiving

Two methods are supported:

**IMAP polling**

- `mail:sync` runs every ten minutes, or you can press **Sync** on an account.
- It reads the last 30 messages in `INBOX` and stores new ones.
- Duplicates are skipped by `Message-ID`.
- Text and HTML bodies are parsed, including base64 and quoted-printable encodings and common multipart layouts.
- File attachments are extracted and stored on the private disk; they appear on the message and can be downloaded by anyone with access to that mailbox.
- Failures are stored on the account as `last_sync_error` and written to the application log.
- **Test IMAP connection** opens and authenticates a session without fetching mail, so you can prove the credentials before relying on the sync.

**Provider webhook**

```
POST /mail/inbound/{inbound-token}
```

- The token belongs to one account and only active accounts are accepted.
- The endpoint is public and exempt from CSRF protection.
- It accepts JSON or form payloads from Mailgun, Postmark, SendGrid and generic senders.
- It returns JSON: `{ "status": "ok", "stored": true|false, "account": "..." }`.
- The token is generated automatically and acts as a secret URL.
- Base64 attachments in the payload are stored with the message.
- Duplicate `Message-ID` values are skipped, so a provider retry does not create a second copy.

**Webhook signatures**

Each account has a signing secret. The account setting **Require a signed inbound webhook** controls whether signatures are mandatory:

- **Off (default):** unsigned webhooks are accepted, so a simple forwarding rule keeps working. A signature that is present but wrong is still rejected.
- **On:** every call must include a valid signature. Unsigned calls receive `403`.

Send the signature as `X-Techies-Signature: sha256=<hmac>`, where the HMAC is SHA-256 of the raw request body keyed with the account's secret. `X-Webhook-Signature` and `X-Hub-Signature-256` are also accepted.

**Rotating credentials**

The mailbox screen has **Rotate inbound credentials**. It replaces both the URL token and the signing secret immediately. Update the mail provider at the same time, or inbound mail for that account stops. Rotation is audited.

### 14.5 Inbox and sent log

The inbox can be filtered by account, unread status and search across subject, sender address and sender name. Opening an inbound message marks it read. A simple thread view groups messages by `Message-ID` and `In-Reply-To`. A message with attachments lists them with size and a download link.

The sent log can be filtered by status and searched by subject or recipient. It records sent and failed messages, including broadcast deliveries.

Both lists are scoped to the mailboxes you can access. Opening a message or downloading an attachment from a mailbox you cannot access returns `403`.

### 14.6 Templates

Templates have a name, subject and body. They are available when composing, when replying and when preparing a broadcast. Templates are plain reusable content; they are not versioned and changes apply immediately.

Placeholders are supported in broadcasts (see below). They are not automatically substituted when composing a single message.

### 14.7 Broadcasts

A broadcast is a prepared bulk email.

**Preparation**

- Name it for internal reference.
- Choose the sending account.
- Optionally start from a template.
- Write the subject and body.
- Choose an audience.

**Audiences**

| Audience | Recipients |
|---|---|
| All active members | Every active account |
| Members of a chapter | Active members of the selected chapter |
| Attendees of an event | Registrants with confirmed `registered` status |
| Newsletter subscribers | Everyone on the newsletter list, including non-members |
| Hub owners | Active accounts that own a hub |
| Applicants for an opportunity | Everyone who applied to the selected opportunity |

Recipients are deduplicated by lower-cased email address and stored as a **snapshot** when the broadcast is prepared. Later changes to membership do not alter an already-prepared broadcast.

**Placeholders**

- `{{name}}` — full recipient name
- `{{first_name}}` — text before the first space
- `{{email}}` — recipient address
- `{{site_name}}` — application name

Both `{{ name }}` and `{{name}}` forms work.

**Sending**

- The broadcast is created as `draft`.
- Pressing send moves it to `sending`, then `sent` (or `failed` if every recipient failed).
- Recipients are processed in batches of 50.
- Each delivery creates a sent-log record and updates the recipient row to `sent` or `failed`, with the error.
- Failed recipients can be retried by sending again; already-sent recipients are not re-sent.
- A broadcast cannot be deleted while it is sending.
- Sending runs synchronously. For large lists this can take a long time; the controller removes the PHP time limit, but the browser request still has to stay open.

**Scheduling**

- On the create screen, set **Schedule for later** to a future date and time.
- The broadcast is saved with status `scheduled` and a queue job delayed until that moment.
- A per-minute command, `mail:broadcasts:send-due`, also sends anything due. This is the safety net if the queue worker is not running.
- A scheduled broadcast can still be opened and sent immediately with **Send now instead**.
- A past time is rejected by validation.
- Both the queue worker and the scheduler must be running for reliable delivery. See Section 2.

**Command-line fallback**

```bash
php artisan mail:broadcast {id}
```

This is the reliable way to send a large broadcast, because it is not tied to a browser request. It sends pending recipients and reports delivered and failed counts.

### 14.8 What the mail centre does not do

- No card processing. Payment links are external.
- No IMAP folder management, labels or archiving.
- No rich-text or WYSIWYG editor; compose accepts HTML.
- No open/click tracking or bounce processing.
- No per-message scheduled sending; scheduling applies to broadcasts only.
- No inbound forwarding rules; the webhook and IMAP sync only store messages.
- No automatic retention or deletion policy for stored mail.

### 14.9 Configuring both mail paths

To make the platform work end to end, configure:

1. **System mail** in `.env` — verification, password resets, review alerts, event confirmations, reminders, digests and the contact form.
2. **At least one mail-centre account** in `/admin/mail/accounts` — human correspondence and broadcasts.

If only system mail is configured, members still receive platform notifications, but staff cannot use the shared inbox or send broadcasts. If only a mail-centre account is configured, staff can send mail, but platform notifications may fail.

After connecting an account, use **Send test** to confirm SMTP before relying on it.

---

## 15. Every automated email

All of these are sent through the system mailer (Section 14.9), not the mail centre.

| Email | Trigger | Recipient |
|---|---|---|
| Email verification | Registration or resend request | The account owner |
| Password reset | Forgot-password request | The account owner |
| Contact form | Public contact form | Configured contact address |
| Member contact message | Profile contact form | The profile owner, with the sender as reply-to |
| Content submitted | Article submitted for review | Reviewers for that topic (super admins only for restricted topics), excluding the author |
| Content approved | Admin approves an article | Author |
| Changes requested | Admin rejects an article with notes | Author |
| Event registered | Successful registration; paid events get a "registration received" email with payment details | Attendee |
| Event waitlisted | Event is full | Attendee |
| Spot opened | A confirmed attendee cancels and a waitlisted person is promoted | Promoted attendee |
| Event reminder | One day before the event | Confirmed attendees not yet reminded |
| Application received | Member applies to an opportunity | Applicant |
| Application update | Admin changes an application status | Applicant |
| Speaker proposal approved | Organiser approves a call for speakers submission | The speaker |
| Speaker proposal rejected | Organiser rejects a call for speakers submission | The speaker |
| Membership payment submitted | Member submits a payment reference | Admins and super admins |
| Membership activated | Staff confirms or waives the fee | The member |
| Mentorship matched | Admin matches a mentor and mentee | Both members |
| Certificate issued | Admin issues a certificate | The member |
| Membership feedback request | 30 days after activation | The member |
| Opportunity matches | Weekly matching job | Opted-in active members |
| Introduction update | Admin completes or declines an introduction | Requester and target |
| Grant application update | Admin approves or rejects a grant application | Applicant |
| Monthly community report | First day of each month | Active members |
| Birthday greeting | Member's birthday, once per year | The member |
| New event | An event is first published | Active members in the event's state (or all active members when no state is recorded) |
| New opportunity | An opportunity is first published | Active members in the hub's state (or all active members when there is no hub state) |
| Hub application submitted | A hub is registered | Admins and super admins, excluding the owner |
| Hub application reviewed | Hub approved or rejected | Hub owner |
| Chapter joined | Member joins a chapter | The member |
| New chapter member | Member joins a chapter | The primary coordinator, if different |
| Chapter coordinator assigned | A coordinator is set on a chapter | The newly assigned coordinator |
| Chapter coordinator removed | A coordinator is replaced or cleared | The removed coordinator |
| Weekly digest | Monday 08:00, if opted in and there is content | Members with the digest enabled |

Operational note: publishing mail is broad by design. An event or opportunity without a recognisable state goes to every active account, including staff. There is no exclusion list.

---

## 16. Scheduled work and command-line tools

### 16.1 Schedule

| Time | Command | Purpose |
|---|---|---|
| Daily 08:00 | `events:send-reminders` | Email confirmed attendees about events starting the next day |
| Mondays 08:00 | `community:send-digest` | Weekly digest to opted-in members |
| Every 10 minutes | `mail:sync` | Poll connected IMAP inboxes |
| Every minute | `mail:broadcasts:send-due` | Send scheduled broadcasts whose time has arrived |
| Daily 09:00 | `members:request-feedback` | Ask members for feedback after 30 days |
| Mondays 09:00 | `opportunities:send-matches` | Email matched opportunities to opted-in members |
| 1st of month 08:00 | `community:send-monthly-report` | Email a monthly community report to active members |
| Daily 07:30 | `members:send-birthday-wishes` | Email members on their birthday |

The scheduler requires the server cron entry shown in Section 2.

### 16.2 Commands

| Command | What it does |
|---|---|
| `php artisan events:send-reminders --days=1` | Sends reminders for events starting that many days ahead |
| `php artisan community:send-digest --dry-run` | Counts digest recipients without sending |
| `php artisan community:send-digest` | Sends the weekly digest |
| `php artisan mail:sync` | Syncs every active IMAP account |
| `php artisan mail:sync --account={id}` | Syncs one account |
| `php artisan mail:broadcast {id}` | Sends a prepared broadcast from the command line |
| `php artisan mail:broadcasts:send-due` | Sends scheduled broadcasts that are now due |
| `php artisan users:sync-roles --dry-run` | Reports role drift without changing anything |
| `php artisan users:sync-roles` | Aligns Spatie roles with `users.role` |

### 16.3 Digest behaviour

The digest is only sent when there is something to include: at least one upcoming event in the next seven days, a recently published article, or a published opportunity. If there is nothing, the command exits without sending.

Recipients are active members whose profile has `weekly_digest` enabled.

---

## 17. Search engine visibility and feeds

### 17.1 Sitemap

`/sitemap.xml` includes:

- the main public pages,
- up to 500 approved articles with active authors,
- up to 500 upcoming published events,
- up to 500 approved public hubs,
- all active chapters,
- up to 1,000 public member profiles.

### 17.2 RSS

- `/feeds/articles.xml` — the 30 most recent approved articles.
- `/feeds/events.xml` — the 30 next upcoming published events.

Both are linked from the site's `<head>`.

### 17.3 Robots

`/robots.txt` disallows `/admin`, `/super-admin`, `/dashboard` and `/hub/dashboard`, and points crawlers at the sitemap.

### 17.4 Page metadata

Pages use their own titles and descriptions where set. Articles use the SEO title, description and canonical URL when provided. Site-wide defaults come from settings.

---

## 18. Security, privacy and data handling

### 18.1 Access control

- Admin routes require primary role `admin` or `super_admin`.
- Super admin routes require `super_admin`.
- Member actions require a verified account where they create public or emailed content: event registration, opportunity applications, chapter membership, member contact and content submission.
- Ownership checks prevent members from editing another member's article.
- Article approval is checked per topic, not just per role.
- Admins cannot suspend themselves or a super admin.
- Super admins cannot change their own role, and the last super admin cannot be demoted.

### 18.2 Session safety

- Sessions are invalidated when role, status or assigned roles change.
- Suspended accounts are logged out on their next request.
- Password resets and logins regenerate the session.
- Logout invalidates the session and regenerates the CSRF token.

### 18.3 Private data

- Private profiles are not published on subdomains.
- Member contact forms do not reveal email addresses.
- The members directory only exposes profiles that opted in.
- SMTP and IMAP passwords are encrypted at rest.
- The inbound mail token should be treated as a secret.

### 18.4 Content and moderation

- Members cannot publish directly; all articles pass through review.
- Restricted topics require a super admin.
- Review notes are emailed to the author and stored on the article.
- Administrative actions are audited, with the gaps noted in Section 13.5.

### 18.5 Rate limiting

- Contact form: 5 per minute.
- Member contact: 5 per minute.
- Username availability check: 30 per minute.
- Verification email resend: 6 per minute.

### 18.6 Soft deletes

Articles, events, opportunities, chapters and hubs use soft deletes. Deleting from the admin interface hides the record but retains it in the database. Slug generation checks soft-deleted records too, so URLs are not reused accidentally.

---

## 19. Operating the platform

### 19.1 Daily

- Open `/admin` and clear the pending content queue, oldest first.
- Check pending hubs and approve or reject them.
- Check the mail centre inbox and sent log for failures.
- Review new opportunity applications and event registrations as they arrive.

### 19.2 Weekly

- Confirm the weekly digest ran (Mondays 08:00) and check the recipient count with `--dry-run` if unsure.
- Review the audit log for unexpected role or settings changes.
- Review upcoming events for missing deadlines, capacity or meeting links.
- Check the mail centre's last sync status and errors on each account.

### 19.3 Monthly

- Review suspended accounts and restore anyone who should return.
- Review staff accounts and remove admin access that is no longer needed.
- Confirm that at least one super admin exists and can sign in.
- Check the sitemap and feeds still return content.
- Verify backups of the database and the `storage/app/public` directory.

### 19.4 Before go-live

1. Set production `APP_URL`, database and system mail credentials.
2. Set `COMING_SOON=false` only when the public site is ready.
3. Run migrations and seed the required reference data.
4. Create the first super admin and change any seeded default password.
5. Connect at least one mail-centre account and send a test.
6. Run `php artisan storage:link` so avatars and event covers are served.
7. Add the scheduler cron entry.
8. Confirm `APP_DEBUG=false` and a production `APP_KEY`.
9. Submit the sitemap to search consoles.
10. Test registration, verification, event registration, application and article submission end to end.

### 19.5 Troubleshooting

| Symptom | Likely cause | Action |
|---|---|---|
| A role change did not take effect | The user has not refreshed | It applies on the next request; the session will end |
| A demoted user still sees admin screens | Old session or stale pivot | Refresh to trigger logout; run `users:sync-roles` |
| No platform emails arrive | System mail is misconfigured | Check `.env` mail settings and the application log |
| Mail-centre test fails | Wrong SMTP host, port or encryption | Recheck credentials; try TLS on 587 or SSL on 465 |
| Inbox does not update | IMAP not configured or sync failing | Check the account's last sync error; run `mail:sync --account={id}` |
| A broadcast is slow or times out | Sending is synchronous | Use `php artisan mail:broadcast {id}` |
| A scheduled broadcast did not send | Queue worker or scheduler stopped | Run `php artisan mail:broadcasts:send-due`; check the queue worker |
| A member cannot submit an article | SEO score below 50% | The screen lists the failing checks |
| A public profile shows 404 | Profile is private or the account is suspended | Check visibility and status |
| The meeting link is hidden | By design | Only confirmed attendees, the organiser and staff see it |
| An admin cannot see a mailbox | Not the creator and not invited | The mailbox owner adds them as a viewer or manager |
| Inbound webhook returns 403 | Mailbox requires a signature | Add the HMAC header or turn the requirement off |
| Styling looks broken | CDN assets blocked | Allow the Tailwind/Alpine CDNs or self-host them |

### 19.6 Recovering from role drift

Symptom: an account shows one role in the interface and behaves like another. This is the old dual-source problem.

```bash
php artisan users:sync-roles --dry-run
php artisan users:sync-roles
```

Then ask the person to refresh; their session will end and they will sign in with the corrected access.

---

## 20. Known limitations and cautions

These are deliberate or current-state limitations, not hidden bugs. They should be understood before promising a capability.

1. **Card processing requires Paystack keys.** With `PAYSTACK_SECRET_KEY` set, membership and paid-event checkout use Paystack and activate automatically after verification. Without keys, the platform falls back to external payment links/instructions and manual confirmation.
2. **Most platform email is synchronous.** Verification, review alerts, event mail, chapter mail, digests and individual compose send during the request. Only scheduled broadcasts are queued. A slow SMTP host slows the request that triggers it.
3. **IMAP is a custom socket client.** PHP's IMAP extension is not required. It supports the common path (SSL/TLS, login, inbox, search, fetch) but has not yet been proven against every live provider. Use **Test IMAP connection** with the target provider before relying on it.
4. **Webhook signature verification is optional per mailbox.** Turn it on and configure the provider to sign; otherwise the URL token is the only protection.
5. **Hub decisions and member-screen suspensions are audited, but not every read is.** Audit entries cover decisions, role changes, payments, mail and announcements. Listing and viewing are not logged.
6. **Capability roles do not grant admin access.** Only the primary role does. The one exception is the `/manage` event and opportunity area, which also accepts hub owners and chapter coordinators and still restricts them to their own records and to drafts.
7. **Changing a chapter on the profile only adds.** Memberships are never removed from the profile screen; leave a chapter from the chapter page.
8. **Role changes in the console preserve capability roles but replace the primary-role tag.** `users.role` remains the authority.
9. **Email remains the primary channel.** In-app notifications, saved items and search are available as convenience tools, but decisions and reminders are still delivered by email.
10. **No personal website or portfolio fields.** Removed on purpose.
11. **Software-deleted records remain in the database.** "Delete" in the admin interface is a soft delete.
12. **Mail-centre credentials are only as safe as the admin accounts.** Protect staff accounts, use mailbox sharing instead of a shared login, and rotate credentials when someone leaves.
13. **Announcements are public labels, not permissions.** A chapter announcement is visible to everyone; the chapter is not an audience restriction.
14. **Scheduled broadcasts need a working queue and scheduler.** The per-minute command is a safety net, not a replacement for a running worker.
15. **No billing, invoicing or refund processing.** A refund is a status and a note, not money movement.
16. **Rich text is cleaned by an in-house allow-list.** Scripts, styles, forms and embedded frames are stripped. There is no embedded video or map block; use a plain link instead.
17. **Speaker gallery images are not captioned or linked to individual speakers.** The gallery sits alongside the speaker list on the public page. Use clear file names and order the list in the Key speakers field.
18. **Uploaded images are stored as provided.** The platform does not crop, resize or convert them. Ask contributors to keep files reasonably small.

---

## Appendix A — Role and capability matrix

| Capability | Member | Admin | Super admin |
|---|---|---|---|
| Public profile and subdomain | Yes | Yes | Yes |
| Submit articles | Yes | Yes | Yes |
| Approve ordinary-topic articles | No | Yes | Yes |
| Approve policy/partnership/statement articles | No | No | Yes |
| Force publish | No | No | Yes |
| Register for events / join chapters | Yes | Yes | Yes |
| Apply to opportunities | Yes | Yes | Yes |
| Create and manage events | No | Yes | Yes |
| Create and manage announcements | No | Yes | Yes |
| Create and manage opportunities | No | Yes | Yes |
| Manage chapters | No | Yes | Yes |
| Review and decide hubs | No | Yes | Yes |
| Suspend members | No | Yes (not self, not super admin) | Yes |
| Use the mail centre | No | Yes | Yes |
| Manage users and roles | No | No | Yes |
| Edit organisation settings | No | No | Yes |
| View audit logs | No | No | Yes |

Members whose account carries the `hub_owner` capability, or who coordinate a chapter, can create events and opportunities from `/manage`. Everything they create stays in draft until staff publish it; they cannot see or set publishing status.

---

## Appendix B — Screen map

### Public

```
/                                   home
/about                              about
/mission-and-vision                 mission and vision
/leadership                         leadership
/community                          community
/members                            member directory
/members/{username}                 member profile
/hubs                               hub directory
/hubs/register                      hub application
/hubs/{slug}                        hub profile
/chapters                           chapter directory
/chapters/{slug}                    chapter detail
/events                             event directory
/events/{slug}                      event detail
/events/{slug}/calendar             .ics download
/opportunities                      opportunity directory
/opportunities/{slug}               opportunity detail
/membership                         membership benefits and payment
/speaker-calls                      open calls for speakers
/speaker-calls/{slug}               call detail and submission form
/certificates/{number}              public certificate verification
/articles                           article directory
/articles/{slug}                    article detail
/news                               announcements and recent articles
/contact                            contact form
/privacy-policy                     privacy policy
/terms                              terms
/community-guidelines               guidelines
/sitemap.xml                        sitemap
/robots.txt                         robots
/feeds/articles.xml                 article RSS
/feeds/events.xml                   event RSS
/{username}.{profile-domain}        public profile subdomain
```

### Authentication

```
/login
/register
/forgot-password
/reset-password/{token}
/email/verify
/logout
```

### Member

```
/dashboard                          member dashboard
/profile                            profile settings
/member/content                     my articles
/member/content/create              new article
/member/content/{article}/edit      edit draft or returned article
/hub/dashboard                      hub owner dashboard
/passport                           member passport and card
/mentorship                         mentorship request
/benefits                           member benefits
/programmes                         career clinics and office hours
/feedback                           thirty-day member feedback
/projects                           project showcase
/grants                             micro-grants
/cohorts                            cohorts and build sprints
/introductions                      warm introduction requests
/referrals                          job referral offers and requests
/lounge                             member discussion board
```

### Admin

```
/admin                              admin dashboard
/admin/manage                       management centre
/admin/members                      members
/admin/members/{user}               member detail
/admin/hubs                         hubs
/admin/hubs/{hub}                   hub detail and decisions
/admin/chapters                     chapters
/admin/mentorship                    mentorship matching
/admin/certificates                  certificates
/admin/benefits                      member benefits
/admin/feedback                      member feedback
/admin/introductions                 introduction requests
/admin/cohorts                       cohorts
/admin/showcase                      project showcase
/admin/referrals                     referrals
/admin/lounge                        lounge moderation
/admin/grants                        micro-grants
/admin/spotlights                    member spotlights
/admin/content                      content queue
/admin/content/{article}            review screen
/admin/announcements                announcements
/admin/announcements/create         new announcement
/manage/events                      events (staff, hub owners, coordinators)
/manage/events/create               new event
/manage/events/{event}/edit         edit event
/manage/events/{event}/registrations attendee list, check-in, export
/manage/opportunities               opportunities (staff, hub owners, coordinators)
/manage/opportunities/create        new opportunity
/manage/opportunities/{opportunity}/edit  edit and manage applications
/manage/speaker-calls               calls for speakers (staff, hub owners, coordinators)
/manage/speaker-calls/create        new call
/manage/speaker-calls/{call}/edit   edit call
/manage/speaker-calls/{call}/submissions  review speaker proposals
/admin/categories                   category landing page
/admin/event-categories             event categories
/admin/opportunity-categories       opportunity categories
/admin/mail                         mail centre home
/admin/mail/inbox                   shared inbox
/admin/mail/sent                    sent log
/admin/mail/compose                 compose
/admin/mail/accounts                connected accounts
/admin/mail/templates               templates
/admin/mail/broadcasts              broadcasts
```

### Super admin

```
/super-admin                        console dashboard
/super-admin/users                  users and roles
/super-admin/roles                  role catalogue
/super-admin/settings               organisation settings
/super-admin/audit-logs             audit trail
/super-admin/content                content queue with force publish
```

---

## Appendix C — Glossary

| Term | Meaning |
|---|---|
| **Primary role** | The value in `users.role`: `member`, `admin` or `super_admin`. It decides which dashboard and area a person can use. |
| **Capability role** | A Spatie role such as `hub_owner` or `content_manager`. It describes extra responsibility but does not grant admin access by itself. |
| **Access signature** | The fingerprint stored at login and rechecked on every request. A mismatch signs the person out. |
| **SEO gate** | The 50% minimum score required before a member can submit an article. |
| **Waitlist** | The queue for a full event. The oldest waitlisted person is promoted when a confirmed attendee cancels. |
| **Snapshot** | The fixed recipient list captured when a broadcast is prepared. |
| **Soft delete** | A record hidden from the interface but retained in the database. |
| **Coming-soon mode** | A switch that closes the public site while leaving staff access open. |
| **System mail** | Platform-generated email, configured in `.env`. |
| **Mail-centre account** | A mailbox connected in the admin centre for human correspondence and broadcasts. |
