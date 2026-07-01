# Deploying the Digaball V4 flagship to production (digaball.com)

## The model (confirmed working)

`digaball.com` serves from `~/public_html`, which also holds the `beta/` checkout
of this repo and your other subsites. Deploying V4 is just **copying
`beta/digaball_v4/*` up to the web root** — index.html + assets/ — which you've
already verified works:

```
~/public_html/beta/digaball_v4/index.html  ->  ~/public_html/index.html
~/public_html/beta/digaball_v4/assets/*    ->  ~/public_html/assets/*
```

`deploy/deploy-to-prod.sh` does exactly that with a plain overlay copy (no
`--delete`), so `beta/` and every other folder under `public_html` are left
untouched. It's idempotent — safe to run on every push.

## Automate it (so pushes update prod)

The source is the `beta/` checkout, which already updates when you push. Add a
cron that pulls (if needed) and copies:

```cron
*/10 * * * * cd $HOME/public_html/beta && /usr/bin/git pull --ff-only >> $HOME/digaball-deploy.log 2>&1 && /bin/bash deploy/deploy-to-prod.sh >> $HOME/digaball-deploy.log 2>&1
```

- If `/beta` already auto-pulls on its own, drop `git pull --ff-only &&` and just
  run the script on the schedule.
- cPanel Git users can instead use the repo-root `/.cpanel.yml`, which runs the
  same script on "Deploy".

After this, the flow is: **edit V4 → `git push` → beta pulls → cron copies →
`digaball.com` reflects it** (within the cron interval). That's the "update prod
by pushing" loop.

## First run (manual, once)

```bash
cd ~/public_html/beta && bash deploy/deploy-to-prod.sh
```
Then check `https://digaball.com/` serves V4 and `https://digaball.com/beta/`
still works. (If the homepage doesn't switch from the old site, ensure
`~/public_html/.htaccess` has `DirectoryIndex index.html index.php` and that the
old `index.php` isn't being force-rewritten.)

## Rollback

The deploy only writes `index.html` + `assets/`. To revert, restore the previous
`index.html` (and remove the added assets), or point `.htaccess` back.

## Files

- `deploy/deploy-to-prod.sh` — the copy (run on the server).
- `/.cpanel.yml` — optional cPanel Git deploy trigger (runs the same script).
