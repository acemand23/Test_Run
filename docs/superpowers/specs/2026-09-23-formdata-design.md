# Form Data — shared form-intake service

**Date:** 2026-09-23
**Status:** Design approved, ready for implementation planning
**Target repo:** `~/code/formdata` (new — does not exist yet)
**Target URL:** `https://forms.anthonyduke.com`

---

## 1. Purpose

Today every form across our sites emails its data to somebody. That means the
information lives in inboxes, gets forwarded, gets lost, and cannot be searched,
counted, or exported. Spam goes straight to the same inboxes.

Form Data replaces that with one shared service:

- A visitor's submission is **stored in a central database**, not emailed.
- The form's manager gets a **notification email containing no data** — just the
  fact that something arrived, and a link.
- They **click through to a web interface** to read it.
- A **standard CAPTCHA** sits in front of every form to stop the spam.

Any site we run can use it, and registering a new form is an admin task, not a
development task.

### Success criteria

1. A new form can be added to any site by registering it in the admin and dropping
   one PHP file into the site — no changes to the central service.
2. No form data travels by email on the normal path.
3. A manager can log in and see only the forms they are responsible for.
4. Spam submissions do not reach anyone's inbox.
5. No submission from a real person is ever silently lost.

### Who uses it

- **Owner** (Coach Duke) — registers forms, manages people, sees everything.
- **Managers** (e.g. Dakota for ATX Juniors, LJ for ATX Events) — log in, see only
  their assigned forms.
- **Shared mailboxes** (e.g. `questions@tbdvolleyball.com`) — receive notification
  email but never log in.

---

## 2. Decisions made during design

These were chosen deliberately; each records the alternative rejected.

| Decision | Chosen | Rejected alternative |
|---|---|---|
| Reach | HTTP API any site can post to | cPanel-only direct MySQL — would have excluded ATX Beach on WP Engine |
| Field model | Accept anything, store as JSON | Declaring fields per form — too much setup, two places to edit |
| Access | Per-user logins scoped per form | Single shared admin; magic links |
| CAPTCHA | Cloudflare Turnstile + honeypot | reCAPTCHA (Google tracking); hCaptcha; honeypot alone |
| Intake path | Site's PHP relays with a secret key | Browser posts direct with a public key |
| Home | Own repo, `forms.anthonyduke.com` | Inside the `beta` repo; a digaball.com subdomain |
| API unreachable | Emergency email fallback | Local spool + cron; fail closed |

**Brand neutrality** is why `anthonyduke.com` beat `digaball.com`: this service
carries data for TBD Volleyball, ATX Beach, Boomtown and Digaball alike, and
should not wear any one brand's domain.

---

## 3. Architecture

New repo `~/code/formdata`, with its own `resume.md` and `resume.sh`, following the
structure already proven in `~/code/kevin60`.

```
formdata/
  api/
    submit.php            # the only public endpoint
  admin/
    _guard.php            # session auth + per-form scoping
    login.php  logout.php  account.php
    index.php             # dashboard
    form.php              # submissions list for one form
    submission.php        # one submission, full payload
    forms_edit.php        # register / edit a form
    users.php             # owner only
    export.php            # CSV
  src/
    bootstrap.php  config.php  config.sample.php
    db.php  helpers.php  layout.php
    spam.php              # honeypot, timing, Turnstile
    notify.php            # notification email + logging
  client/
    formdata-client.php   # the single file sites include
  sql/
    schema.mysql.sql      # production
    schema.sqlite.sql     # local dev — mirrors the above
  tools/
    create-user.php       # CLI account seeding
  tests/
```

### Component responsibilities

- **`api/submit.php`** — authenticate the form key, run spam checks, store the row,
  trigger notification, return JSON. Stateless.
- **`admin/`** — everything behind a login. Never writes submissions, only reads and
  changes status.
- **`src/`** — shared core. `db.php` exposes a PDO handle; `spam.php` and `notify.php`
  are independently testable units with no HTTP coupling.
- **`client/formdata-client.php`** — the only file that ships to other sites. Two
  public functions: `formdata_fields()` renders the hidden honeypot and signed
  timestamp into the form, and `formdata_submit()` sends the submission.

### Turnstile is verified server-side, in the API

The site embeds the Turnstile widget using the **public sitekey** and passes the
resulting token through to the API. The API holds the **secret** and performs the
verification.

Rationale: the secret exists in exactly one place; a site physically cannot forget
to verify; and turning CAPTCHA on or off for a form is a checkbox in the admin
rather than a code change deployed to six pages.

Turnstile does **not** require the domain to use Cloudflare DNS — allowed hostnames
are simply listed on the widget. `anthonyduke.com` can stay on Squarespace.

### PHP version constraint

The central app targets **PHP 8.x** (new subdomain, our choice).

`client/formdata-client.php` must run on **PHP 7.0+** — the TBD_v5 host is PHP 7.x.
(Commit `7c3770c` in the `beta` repo had to strip PHP 8-only syntax for exactly this
reason.) The client file therefore stays deliberately plain: cURL, no arrow
functions, no named arguments, no typed properties, no null-coalescing assignment.

---

## 4. Data model

MySQL in production, with a mirrored SQLite schema for local development. The two
schemas are kept deliberately identical in shape so tests run locally without
touching the InMotion box, which has a history of being slow.

### `users`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `name` | VARCHAR(190) | |
| `email` | VARCHAR(190) UNIQUE | login identity |
| `password_hash` | VARCHAR(255) | PHP `password_hash()` |
| `role` | VARCHAR(20) | `owner` or `manager` |
| `is_active` | TINYINT | deactivate without deleting history |
| `created_at` | DATETIME | |
| `last_login_at` | DATETIME NULL | |

### `forms`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `slug` | VARCHAR(190) UNIQUE | stable identifier, e.g. `tbd-volunteer` |
| `name` | VARCHAR(190) | human label, e.g. "TBD Volunteer Signup" |
| `site_label` | VARCHAR(190) | which site it lives on, for the admin list |
| `key_prefix` | VARCHAR(16) | first 8 chars, shown in UI to tell keys apart |
| `key_hash` | VARCHAR(255) | hash of the secret key |
| `notify_emails` | TEXT | comma-separated |
| `captcha_enabled` | TINYINT | default 1 |
| `is_active` | TINYINT | inactive forms reject submissions |
| `created_at` | DATETIME | |

### `form_users`
| Column | Type | Notes |
|---|---|---|
| `form_id` | INT | |
| `user_id` | INT | |
| | | `UNIQUE(form_id, user_id)` |

Controls **visibility in the admin**, not notification.

### `submissions`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `form_id` | INT | indexed |
| `payload` | TEXT | JSON |
| `submitted_at` | DATETIME | indexed |
| `ip` | VARCHAR(45) | IPv6-capable; `INDEX(form_id, ip, submitted_at)` for rate limiting |
| `user_agent` | TEXT | |
| `referer` | TEXT | |
| `status` | VARCHAR(20) | `new`, `read`, `spam`, `archived` |
| `spam_reason` | VARCHAR(40) NULL | `honeypot`, `timing`, `turnstile` |

### `notification_log`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `submission_id` | INT | |
| `to_email` | VARCHAR(190) | |
| `sent_at` | DATETIME | |
| `ok` | TINYINT | |
| `error` | TEXT NULL | |

### Three deliberate choices

1. **Secret keys are stored hashed.** The full key is displayed exactly once, at
   creation or regeneration. `key_prefix` stays in the clear so keys are
   distinguishable in the UI. A database leak cannot then be turned into the ability
   to post to our forms.
2. **`payload` is TEXT holding JSON, not a MySQL `JSON` column.** This keeps the
   MySQL and SQLite schemas honestly identical. We never query inside the payload,
   so the JSON column type buys nothing.
3. **`notify_emails` is separate from `form_users`.** They are different concerns:
   `questions@tbdvolleyball.com` is a shared mailbox that must receive mail but will
   never log in, while a manager needs an account. Conflating them would force
   fake user accounts for mailboxes.

`notification_log` would normally be cut as premature. It is kept because this host
has produced `mail()` failures and HTTP 508s, which makes "did the notification
actually go out?" a question we will genuinely need answered.

---

## 5. Intake flow

Worked example — the TBD_v5 volunteer form:

1. **Page renders** with the Turnstile widget (public sitekey), a hidden honeypot
   field, and a signed timestamp. The client library supplies these via a helper,
   `formdata_fields()`, which emits the honeypot input and the timestamp — sites do
   not hand-roll them, so every form gets the same protection.

   The timestamp is **HMAC-signed with the form's secret key** and submitted as
   `_ts` plus `_sig`. Signing matters: an unsigned timestamp is attacker-supplied,
   so a bot would simply send a plausible value and walk through the timing check.
   The API recomputes the HMAC and rejects any mismatch as `timing`.
2. **Visitor submits to `volunteer.php`** — unchanged from today, so the existing UX
   and success banner survive.
3. **`volunteer.php` validates its own required fields**, then calls the client:

```php
require_once 'formdata-client.php';
$res = formdata_submit(array(
  'endpoint' => 'https://forms.anthonyduke.com/api/submit.php',
  'key'      => FORMDATA_KEY,          // from gitignored config.php
  'fields'   => $_POST,                // accept-anything
  'captcha'  => $_POST['cf-turnstile-response'],
));
```

4. **API** authenticates the key, runs spam checks, stores the row, sends
   notifications, returns `{"ok":true,"id":123}`.
5. **Site shows its existing success banner.**

### Client return contract

`formdata_submit()` always returns an array and **never throws or emits output** —
a site's form handler must stay in control of its own page. Shape:

| Key | Type | Meaning |
|---|---|---|
| `ok` | bool | the submission is safely recorded |
| `id` | int\|null | submission id when stored centrally |
| `via` | string | `api` on the normal path, `fallback` if it was emailed |
| `error` | string\|null | operator-facing detail; never shown to the visitor |

`ok` is true when the data is safe **by either route** — so a site shows its success
banner on `ok`, without needing to know which path carried it. `ok` is false only
when the API rejected the submission (spam, bad key, over the caps) *and* no
fallback was possible; sites treat that as "show the error, keep the form filled in".

Spam rejections return `ok:false` with a generic `error`. Sites should show the
visitor a neutral failure message rather than "you look like a bot", which merely
teaches spammers what tripped the filter.

### Spam checks, cheapest first

| # | Check | Reject reason | Cost |
|---|---|---|---|
| 1 | Honeypot field non-empty | `honeypot` | free |
| 2 | Bad `_sig`, or timestamp age < 3s or > 24h | `timing` | free |
| 3 | Turnstile token verified with Cloudflare | `turnstile` | one HTTPS call |

Rejected submissions are **stored with `status='spam'`** and auto-purged after 30
days. This gives visibility into what is being caught — important for noticing if
the filter starts eating real people — without unbounded growth. Spam never
triggers a notification.

Checks 1 and 2 run before check 3 so that obvious bot traffic never costs us a
network round-trip to Cloudflare.

### When the API is unreachable

The client times out fast (5s connect, 10s total). On failure it falls back to
emailing the submission data to the form's notify address, subject-lined:

```
FORMDATA FALLBACK — API unreachable — <form name>
```

Email stops being the normal path, which is the goal, but no lead is ever lost. The
deliberately alarming subject line makes a fallback a visible alarm rather than a
silent habit, and the data can be pasted into the admin afterwards.

---

## 6. Admin interface

Owner sees every form. A manager sees only forms assigned through `form_users`.

| Page | Purpose |
|---|---|
| `login` / `logout` | Session auth, following kevin60's `_guard.php` pattern |
| `index` | Dashboard: your forms, each with a "new" count |
| `form?id=` | Submissions list: date, preview columns, status; filter + search |
| `submission?id=` | Full payload as a label/value table, plus IP, user-agent, referer, timestamp; mark read / spam / archived |
| `forms_edit` | Register or edit a form; regenerate key (shown once) |
| `users` | **Owner only**: create/deactivate people, assign forms |
| `export?form_id=` | CSV download |
| `account` | Change your own password |

**CSV columns are the union of every key ever seen** in that form's submissions.
This is the honest way to export accept-anything data: a form that gained a field
partway through its life still exports cleanly, with blanks where the field did not
yet exist.

There is **no public signup page**. Accounts are seeded by `tools/create-user.php`
from the CLI, and thereafter created by the owner in the admin.

---

## 7. Notifications

The email carries **no submission data**:

> **New submission — TBD Volunteer Signup**
> Received Tue, Sep 23 2026 at 4:12 PM CDT.
> → View it: `https://forms.anthonyduke.com/admin/submission.php?id=123`

Clicking through while logged out lands on the login page and then redirects to
that exact submission.

Delivery uses `mail()` on the forms subdomain by default, with optional
authenticated SMTP via a gitignored `config.php` — the same pattern as TBD_v5's
`config.sample.php`. Every send is written to `notification_log`.

**A notification failure never fails the submission.** The row is already committed;
a dead mail server is logged and the API still returns `ok`. Losing the lead because
the email bounced would defeat the purpose of the project.

---

## 8. Security requirements

These are requirements, not suggestions, and several follow directly from the
accept-anything design.

1. **Escape attacker-controlled field names *and* values.** Because the API stores
   whatever it is sent, both the keys and the values of the payload JSON are
   attacker-controlled, and the admin renders them. A field named `<script>` must be
   exactly as safe as one named `email`. This must be built in from the first line
   of the admin, not patched afterwards.
2. **Per-form scoping is enforced server-side on every admin page**, not merely
   hidden from the navigation. Requesting an unassigned form's submission by direct
   ID must be refused.
3. **Keys are verified against the stored hash**; revoked or wrong keys are rejected.
4. **All queries use PDO prepared statements.**
5. **Payload caps**: maximum total payload size and maximum field count, rejected
   with a clear error above the limit.
6. **Rate limiting** per form and per IP on the intake endpoint, implemented by
   counting rows in `submissions` for that `form_id` + `ip` inside a rolling window
   (default: 10 per 10 minutes). This needs no extra table, cache, or daemon —
   shared cPanel hosting gives us no Redis — and requires an index on
   `(form_id, ip, submitted_at)`. Rate-limited attempts are rejected without being
   stored, so a flood cannot grow the table.
7. **Secrets never enter git**: `config.php` is gitignored in both the service and
   every consuming site; `config.sample.php` is the committed template.
8. **The API returns generic errors.** An authentication failure must not reveal
   whether the form slug exists.

---

## 9. Deployment & setup

### Manual steps (owner only — these cannot be automated from here)

1. **DNS** — add A record `forms` → `192.249.113.88` in the Squarespace domain panel.
2. **cPanel** — add subdomain `forms.anthonyduke.com`, docroot
   `/home/texass17/forms.anthonyduke.com/`.
3. **cPanel** — create MySQL database and user (`texass17_formdata`), grant all.
4. **cPanel** — set that subdomain's PHP version to 8.x.
5. **AutoSSL** — issue the certificate once DNS resolves.
6. **Cloudflare** — create a Turnstile widget; copy sitekey and secret into
   `config.php`.

`anthonyduke.com` currently resolves to Squarespace (`198.185.159.x` /
`198.49.23.x`) with DNS at Google Domains, now administered through Squarespace.
Adding the `forms` A record leaves the apex and `www` untouched.

### Deferred, non-blocking

Moving `anthonyduke.com` off Squarespace entirely is **out of scope**. It is a
separate cleanup with its own risks — chiefly that a nameserver change can silently
break MX records and therefore email. If it happens later, re-creating one `forms`
A record at InMotion is trivial.

### Deploy method

Standard cPanel Git deployment via `.cpanel.yml`, with `rsync` over the
`vballmgr-staging` SSH host as the manual fallback.

---

## 10. Phased rollout

| Phase | Contents |
|---|---|
| **1 — Core** | Schema, `src/`, `api/submit.php`, client library, **one pilot form** (TBD_v5 volunteer) end to end |
| **2 — Admin** | Login, dashboard, submissions list + detail, form registration, CSV export |
| **3 — People** | User management, per-form assignment, owner/manager scoping |
| **4 — Rollout** | TBD_v5's other five forms, then ATX Beach's two (WordPress flavor of the client) |
| **5 — Later** | Spam tuning; optional full-domain move |

Phases 1–2 are the MVP. The pilot form proves the entire path before five more
pages are touched.

### Forms to migrate (phase 4)

- **TBD_v5** (`beta` repo, PHP 7.x host): `volunteer.php`, `contact.php`,
  `register.php`, `sponsor.php`, `become-a-sponsor.php`, `sponsor-in-kind.php`
- **ATX Beach** (WP Engine, WordPress): host inquiry (`lj@atxbeach.com`), juniors
  inquiry (`dakota@atxbeach.com`)

The ATX Beach forms need a WordPress-flavored client that uses `wp_remote_post()`
rather than raw cURL; the API contract is identical.

---

## 11. Testing

Local development runs on **SQLite + `php -S`**, so the suite is fast and never
touches the overloaded InMotion box. Test-driven throughout.

**Core coverage:**
- Key authentication: valid, wrong, revoked, inactive form
- Each spam check independently, plus the cheapest-first ordering
- JSON payload round-trip, including unicode and nested values
- Client library timeout and email-fallback behaviour
- CSV column-union across submissions with differing field sets
- Notification failure does not fail the submission

**Mandatory security tests:**
- A manager requesting an unassigned form's submission by direct ID is refused
- Field names and values containing HTML/JS render escaped in the admin
- Payload size and field-count caps reject oversized submissions
- A forged `_ts` with a missing or wrong `_sig` is rejected as `timing`
- Exceeding the per-form/per-IP rate limit is rejected and stores no row
- A failed authentication does not reveal whether the form slug exists

---

## 12. Open items

None blocking implementation. Deferred by choice:

- Full migration of `anthonyduke.com` off Squarespace (section 9)
- Spam-threshold tuning, which needs real traffic before it can be judged
