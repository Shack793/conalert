# ConAlert — case intake & public-pressure tracker (PHP/MySQL, shared-hosting edition)

A small site for collecting reports from people whose withdrawals or funds
were frozen by online casinos, crypto exchanges, or similar platforms — so
you can (a) point them at the formal channels that actually move money, and
(b) selectively apply public pressure on verified, consented cases.

This version is plain **PHP + MySQL**, chosen specifically because it runs
on virtually any Namecheap hosting plan — shared/cPanel hosting or a VPS —
with no special runtime setup, no Node.js, no external services. If you
later move from shared hosting to a VPS, this exact codebase moves with you
unchanged.

## What's new in this version

- **Rebranded to ConAlert** (conalert.org) throughout.
- **Multi-user admin logins**, not one shared password:
  - `admin` role: manages cases *and* can create, revoke, reactivate, and
    reset the password of other logins (for your assistant/dev team).
  - `staff` role: can review and update cases, but can't touch other logins.
  - Revoking an account takes effect **immediately** — it's checked against
    the database on every request, not just at login, so a revoked person is
    locked out even mid-session.
  - Every status/summary/notes change on a case now records *who* made it.

## What's in the box

```
index.html, submit.html, cases.html, resources.html   → public site (unchanged design)
css/style.css, js/*.js                                  → shared frontend assets
api/cases.php, api/stats.php                             → public endpoints
admin/login.php, admin/logout.php                        → session-based login
admin/dashboard.php                                       → case queue (admin + staff)
admin/users.php                                            → team login management (admin only)
admin/api/*.php                                            → the admin JSON endpoints behind those pages
includes/auth.php, includes/csrf.php, includes/helpers.php → shared PHP logic
db.php, config.example.php                                → database connection + settings
db/schema.sql                                              → MySQL schema
db/seed.php                                                → one-time setup script (see below)
.htaccess                                                  → blocks direct access to config.php, includes/, *.sql
```

No build step, no `npm install`, no Node — just upload the files.

## 1. Get a MySQL database (cPanel)

In cPanel: **MySQL Databases**
1. Create a database (e.g. `conalert`) — cPanel will prefix it with your
   account username, giving you something like `myuser_conalert`.
2. Create a database user and a strong password.
3. Add that user to the database with **All Privileges**.
4. Note down: database host (almost always `localhost` on shared hosting),
   database name, username, password.

## 2. Import the schema

In cPanel: **phpMyAdmin** → select your new database → **Import** →
choose `db/schema.sql` → Go. This creates the `admins`, `cases`, and
`case_events` tables.

## 3. Upload the files

Via cPanel **File Manager** (or FTP/SFTP) upload everything in this project
into your domain's document root — usually `public_html/` if this is your
primary domain, or `public_html/conalert.org/` if it's an addon domain.

## 4. Configure the app

Rename `config.example.php` to `config.php` (don't leave the example one
sitting alongside it) and fill in real values:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'myuser_conalert');
define('DB_USER', 'myuser_dbuser');
define('DB_PASS', 'your-real-password');
define('APP_SECRET', '...long random string...');
define('SITE_NAME', 'ConAlert');
define('CONTACT_EMAIL', 'cases@conalert.org');
define('SEED_SECRET', '...another long random string...');
```

Generate random strings for `APP_SECRET` and `SEED_SECRET` — if you have
terminal access: `php -r "echo bin2hex(random_bytes(32));"`. If not, any
long, unguessable string works (a password manager's "generate password"
feature is fine for this).

## 5. Create your first admin login

**If your hosting plan includes SSH/terminal access** (check cPanel for a
"Terminal" icon, or ask Namecheap support):
```bash
cd public_html   # or wherever you uploaded the files
php db/seed.php "Your Name" you@conalert.org "a-strong-password"
```

**If you don't have terminal access**, run the same script over the web
once:
```
https://conalert.org/db/seed.php?secret=YOUR_SEED_SECRET&name=Your+Name&email=you@conalert.org&password=a-strong-password
```
Then **immediately** either delete `db/seed.php` or change `SEED_SECRET` in
`config.php` to something else — leaving that URL guessable is a real risk,
since anyone who finds it could create themselves an admin account.

Either way, this also seeds one clearly-labeled **illustrative** example
case (the $14,000 bets.io scenario) so you can see the site working before
any real submissions exist.

## 6. Log in and add your team

Visit `https://conalert.org/admin/login.php` and sign in with the account
you just created. From **Team logins** (visible to admin accounts, linked
from the dashboard), you can add logins for assistants or dev team members:

- Pick a role — **staff** for people who should only work cases, **admin**
  for people who should also be able to manage logins.
- Creating an account generates a temporary password shown once — share it
  with them securely (not over an unencrypted channel) and have them change
  it via **Change my password** on the dashboard after they log in.
- **Revoke** cuts off access immediately. **Reset password** issues a new
  temporary one, same as account creation.
- ConAlert always keeps at least one active admin account — you can't
  revoke or demote the last one, so you can't accidentally lock yourself out
  entirely.

## 7. Enable HTTPS

Most Namecheap shared hosting includes free **AutoSSL** — in cPanel, look
for **SSL/TLS Status** and make sure it's issued and active for your domain.
Session cookies here are marked `secure` automatically once HTTPS is
detected, so admin login won't work meaningfully over plain HTTP — get SSL
running before you rely on the admin area.

## Verifying it all works

- `https://conalert.org/` — homepage with the ledger stat
- `https://conalert.org/submit.html` — submission form
- `https://conalert.org/cases.html` — should show the seeded bets.io example
- `https://conalert.org/admin/login.php` — sign in with the account you created
- `https://conalert.org/admin/users.php` — (admin accounts only) team login management

## If you move to a VPS later

Nothing about this codebase is shared-hosting-specific — it's a standard
PHP app talking to MySQL. On a VPS you'd install PHP + MySQL/MariaDB +
nginx or Apache yourself, point the web server's document root at this
folder, and everything above still applies (skip the cPanel-specific steps
and use `mysql` CLI / a `.my.cnf` instead of phpMyAdmin if you prefer). Ask
if you want a VPS-specific nginx/PHP-FPM config for this exact codebase —
it's a smaller change than the earlier Node.js rewrite was, since it's the
same language and database either way.

## Backing up your data

Everything lives in MySQL. Back up via cPanel's **Backup Wizard** (full
account backup, includes the database), or on a schedule:
```bash
mysqldump -u myuser_dbuser -p myuser_conalert > conalert-backup-$(date +%F).sql
```

## Important: read this before you publish anything about a real company

This kind of site sits close to defamation risk, because the whole point is
to publish specific, damaging claims about named businesses. A few things
worth building into your process (the seed case and admin dashboard are
already worded this way as a starting point, but the judgment calls are
yours):

- **Publish allegations as allegations.** "A reporter says X withheld a
  $14,000 withdrawal" is very different, legally, from "X stole $14,000."
  The first is a report you can back up with evidence; the second asserts a
  crime as fact.
- **Keep the evidence.** A public summary should be defensible against the
  underlying screenshots/transaction hashes/ticket threads on file — if a
  claim in the summary can't be pointed back to something concrete in the
  submission, cut it or soften it.
- **Give the platform a chance to respond**, and publish that response if
  you get one. It's fairer, and it also blunts a lot of legal risk.
- **Public availability of a complaint isn't the same as it being safe to
  publish.** Even true statements can be defamatory if presented
  misleadingly.
- **Get a lawyer in your jurisdiction to review your standard case-summary
  template before your first real publication.** Defamation, consumer-
  protection, and platform-liability rules vary a lot by country, and a
  30-minute consult is cheap compared to a legal notice later.

None of this means don't do the project — consumer-advocacy and scam-
tracking sites like this exist and do real good. It just means the writing
in `public_summary` is the highest-stakes text on the whole site, and it's
worth being deliberate about it every time.

## What's not automated (on purpose)

Nothing here auto-posts to Reddit, X, or TikTok — those platforms' anti-spam
systems make that risky to automate, and it also removes the human
fact-check step right before something goes out under your name. The
intended flow is: verify a case in the dashboard → copy its
`public_summary` → post it yourself, by hand, from real accounts, with a
link back to the case-wall entry.
