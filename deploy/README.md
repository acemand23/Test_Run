# Deploying the Digaball V4 flagship to production (digaball.com)

## The model

`digaball.com` is served from `~/public_html`, which is a **shared directory** —
it holds the `beta/` preview (a full checkout of this repo), your other subsites,
and (currently) the Joomla site. So we do **not** clone/deploy the whole repo to
the root. V4 is only an `index.html` + three images, so we surgically copy just
those into the root:

```
~/public_html/beta/          <- this repo, already checked out (serves /beta/)
        └── digaball_v4/      <- the flagship (source of truth)

   deploy-to-prod.sh copies:
        digaball_v4/index.html  ->  ~/public_html/index.html   (asset paths -> dgassets/)
        digaball_v4/assets/*    ->  ~/public_html/dgassets/*

   Everything else in ~/public_html (beta/, other sites) is LEFT ALONE.
   (No --delete is ever run against public_html.)
```

`deploy/deploy-to-prod.sh` resolves its own repo root, so run from the beta
checkout it copies `digaball_v4/` up into `public_html/`. It's idempotent — safe
to run on every push. It deliberately does **not** touch `.htaccess` (that's a
one-time setup, below), so repeated auto-deploys can't clobber the domain rules.

## One-time setup (you, once, via cPanel / SSH)

1. **Back up + clear Joomla from the root — but KEEP `beta/` and your other
   folders.** Move Joomla's core files into a backup dir instead of deleting:
   ```
   ~/public_html/_joomla_backup_<date>/   <- move Joomla core here
   ```
   Joomla core (move these): `administrator api cache cli components includes
   language layouts libraries media modules plugins templates tmp` and files
   `index.php configuration.php LICENSE.txt README.txt htaccess.txt
   web.config.txt`. Keep anything you recognize as your own (`beta/`, other
   subsites). Leave the MySQL DB in place until you're sure.

2. **Confirm the asset folder name is free.** The deploy uses `~/public_html/dgassets/`.
   If that name already exists in `public_html`, set a different one — export
   `NS=<name>` in the cron/`.cpanel.yml` command (the script reads `$NS`).

3. **Set the homepage `.htaccess`.** Back up the current `~/public_html/.htaccess`
   first, then make sure it selects the static homepage (no Joomla rewrites):
   ```apache
   DirectoryIndex index.html index.php
   ```
   (A ready-made minimal `.htaccess` is produced by `deploy/build-root-bundle.sh`
   if you want to copy it.)

4. **First deploy — run once to verify:**
   ```bash
   cd ~/public_html/beta && bash deploy/deploy-to-prod.sh
   ```
   Then check `https://digaball.com/` serves V4 and `https://digaball.com/beta/`
   still works.

5. **Automate it (pick one):**

   **A) Cron (works on any host — recommended).** Re-pull the checkout and copy:
   ```cron
   */10 * * * * cd $HOME/public_html/beta && /usr/bin/git pull --ff-only >> $HOME/digaball-deploy.log 2>&1 && /bin/bash deploy/deploy-to-prod.sh >> $HOME/digaball-deploy.log 2>&1
   ```
   If `/beta` already auto-pulls on its own, drop the `git pull &&` and just run
   the script on the schedule.

   **B) cPanel Git Version Control.** If the server checkout is a cPanel-managed
   git repo, the included `/.cpanel.yml` runs the copy on "Deploy HEAD Commit"
   (and on push if your host has push-to-deploy enabled).

## After setup — how future updates work

Editing the flagship and **pushing to `master` is all it takes**: `/beta` updates
(as it already does), then the cron/deploy copies V4 up to the root — so
`digaball.com` reflects the change automatically (within the cron interval).
That's the "Claude can update prod by pushing" flow.

## Verify / rollback

- Verify: `curl -sI https://digaball.com/ | head` (200), spot-check the page and
  `/beta/`.
- Rollback: restore from `_joomla_backup_<date>/`, or just re-point `.htaccess`.
  The deploy only writes `index.html` + `dgassets/`, so removing those two reverts
  the root.

## Files

- `deploy/deploy-to-prod.sh` — the surgical copy (run on the server).
- `/.cpanel.yml` — cPanel Git deploy trigger (optional).
- `deploy/build-root-bundle.sh` — optional: builds `dist/digaball-root.zip`
  (namespaced + optimized + `.htaccess`) for a **manual** first upload via
  cPanel File Manager, if you'd rather not use SSH for step 4.
