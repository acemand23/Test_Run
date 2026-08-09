# I'll Get This One 🍽️💸

A points-based "funny money" app for groups of friends. Someone picks up the
bill at a dinner or event; everyone estimates their own cost **in points**; the
person who paid collects those points. Balances float across the group, so over
time the app can tell you **whose turn it is to get the next one**.

- **Front end:** Godot 4 (one project → exports to **Android** and **iOS**)
- **Back end:** PHP + MySQL, designed to drop onto shared **cPanel** hosting
- **App name:** *I'll Get This One*

```
ill-get-this-one/
├── backend/          PHP + MySQL REST API (deploy to cPanel)
│   ├── api/          front controller + .htaccess
│   ├── controllers/  auth, groups, events
│   ├── lib/          Config, Database (PDO), Http, Auth, Groups, Settlement
│   ├── db/schema.sql import once via phpMyAdmin
│   ├── tests/        php tests/settlement_test.php
│   └── config.sample.php
└── godot/            Godot 4 client (open in the Godot editor)
    ├── autoload/     Api.gd (REST client), Session.gd (state + nav)
    ├── screens/      Auth, Groups, GroupDetail, AddEvent, Events
    ├── ui/UI.gd      styled Control factory
    └── Main.tscn/.gd root scene + screen switcher
```

---

## How the points work

When you tap **"I'll get this one!"**, you record a *gathering*: who paid and
each attendee's estimate in points.

- The payer is **owed** every *other* attendee's estimate.
- The payer's own estimate is their own cost and isn't owed to anyone.

Each member has a **net balance** across all gatherings in the group:

| balance | meaning | UI shows |
| ------- | ------- | -------- |
| `> 0`   | the group owes them (they've been generous) | "you're owed N" |
| `< 0`   | they owe the group | "you owe N" |
| `= 0`   | even | "even" |

The member with the **most negative** balance is flagged **"up next"** — they
owe the most, so they should grab the next bill. Paying collects points, pushing
their balance up (owe-balance down) and everyone else's the other way. It
self-balances over time — no cash ever changes hands.

The engine also produces a **minimal settlement** (fewest point transfers that
would zero everyone out), shown as "if you settled up right now".

> Example (from the original idea): A owes B 20, B owes C 10, C owes A 5.
> The engine nets this to A **−15**, B **+10**, C **+5** — so **A is up next**,
> and settling means A → B 10 and A → C 5. Verified in the test suite.

---

## Backend setup (cPanel)

1. **Database:** In cPanel → *MySQL Databases*, create a database and a user,
   add the user to the database with **All Privileges**.
2. **Schema:** Open cPanel → *phpMyAdmin*, select the database, go to *Import*,
   and import `backend/db/schema.sql`.
3. **Upload:** Copy the `backend/` folder into your site. A common layout is to
   put it under `public_html/` so the API lives at `https://your-domain.com/api`
   (the `api/` folder becomes that URL).
4. **Config:** Copy `config.sample.php` → `config.php` and fill in your DB
   credentials, a random `app_secret`, and `base_path` (`'/api'` for the layout
   above). `config.php` is git-ignored.
   Generate a secret: `php -r "echo bin2hex(random_bytes(32));"`
5. **mod_rewrite:** The included `api/.htaccess` routes all requests to the
   front controller and preserves the `Authorization` header. Most cPanel hosts
   enable `mod_rewrite` by default. If routes 404, confirm it's on.
6. **Smoke test:** Visit `https://your-domain.com/api/health` — you should see
   `{"ok":true,"data":{"service":"ill-get-this-one","status":"up"}}`.

### API reference

All responses are `{"ok":bool, "data":{...}}` or `{"ok":false,"error":"code","message":"..."}`.
Authenticated routes need the header `Authorization: Bearer <token>`.

| Method | Path | Body | Purpose |
| ------ | ---- | ---- | ------- |
| POST | `/auth/register` | `{name,email,password}` | create account → `{token,user}` |
| POST | `/auth/login` | `{email,password}` | log in → `{token,user}` |
| POST | `/auth/logout` | — | invalidate token |
| GET  | `/me` | — | current user |
| GET  | `/groups` | — | your groups + your standing in each |
| POST | `/groups` | `{name}` | create group (you become admin) |
| POST | `/groups/join` | `{invite_code}` | join a group |
| GET  | `/groups/{id}` | — | members, standings, up-next, settlement |
| GET  | `/groups/{id}/events` | — | gathering history |
| POST | `/groups/{id}/events` | `{payer_id?,description,occurred_on?,shares:[{user_id,points}]}` | log a gathering |
| DELETE | `/groups/{id}/events/{eid}` | — | delete (payer or admin) |

### Run the engine tests

```bash
cd backend
php tests/settlement_test.php
```

---

## Godot client setup

1. Install **Godot 4.3+** (standard build; no C#/Mono needed).
2. *Project → Import* and select the `godot/` folder (it contains `project.godot`).
3. Press **Run**. On the login screen, set the **Server** field to your API base
   URL (e.g. `https://your-domain.com/api`), then register an account.

The UI is built entirely in GDScript (`screens/*.gd` + `ui/UI.gd`), so there are
no fragile scene files to maintain beyond the tiny `Main.tscn` bootstrap.

### Exporting to phones

*Project → Export* and add a preset:

- **Android** — install the Android SDK + JDK, set the paths in
  *Editor → Editor Settings → Export → Android*, install the Android Build
  Template (*Project → Install Android Build Template*), then export an APK/AAB.
- **iOS** — export from macOS with Xcode installed; Godot generates an Xcode
  project you build/sign/submit through Xcode (Apple Developer account required).

Because the client talks to the backend over HTTPS, the same build works on both
platforms with no code changes — just make sure `Server` points at your API.

`export_presets.cfg` is git-ignored (it can contain signing paths/keys); create
it locally via the Export dialog.

---

## Status & next ideas

Done: accounts/membership, groups + invite codes, gatherings, the full
points/settlement engine (tested), and a working multi-screen Godot client.

Nice next steps: push notifications for "you're up next", editing a gathering,
per-group currencies/point names, avatars, and rate-limiting on the auth routes.
