# Forbedringsforslag / Improvement Ideas

A running list of potential improvements for the SkelbyForsamlingshus website, grouped by area. Not all items are equally urgent — priority notes are given where relevant.

Items marked ✅ have been implemented. Items marked ⏭️ were explicitly deferred/skipped per user decision.

## Content / UX

- ✅ **`index.php` contact info**: iframe now has a `title` attribute, the hero image has descriptive `alt` text, and `CivicStructure` JSON-LD structured data was added.
- ✅ **Page `<title>`**: [assets/view/header.php](../../assets/view/header.php) now supports per-page `$pageTitle`/`$pageDescription`/`$pageImage`.
- ✅ **`<html lang="da">`** fixed in [assets/view/header.php](../../assets/view/header.php).
- ⏭️ **Favicon** — explicitly excluded from this pass per user request.
- ✅ **Open Graph / Twitter Card tags** added to [assets/view/header.php](../../assets/view/header.php).
- ✅ **Structured data**: `CivicStructure` JSON-LD on `index.php`, `Event` JSON-LD on `arrangementer.php`.

## Accessibility

- Verify color contrast site-wide (e.g. the `subtle-link` phone number style) against WCAG AA. *(not yet done)*
- ✅ OpenStreetMap iframe on the homepage now has a `title` attribute.
- ✅ Hero image `alt` text on `index.php` fixed (was `alt=""`).
- Run an automated audit (axe, Lighthouse accessibility score) across all pages and fix flagged issues. *(not yet done)*

## Performance

- ✅ Google Fonts self-hosted (`assets/css/main.css` `@font-face` rules, vendored `.woff2` files in `assets/fonts/`); CSP `style-src`/`font-src` tightened to drop the now-unused `fonts.googleapis.com`/`fonts.gstatic.com` allowances.
- ✅ `loading="lazy"` added to gallery images (`gallery.php`).
- Consider adding cache headers / a CDN in front of static assets (`assets/css`, `assets/js`, `assets/img`) if not already handled by the web server config. *(not yet done)*
- ⏭️ Responsive `srcset` image variants — skipped; requires generating actual resized image assets, out of scope for a code-only pass.

## Security

- ✅ `confirmation_mail.php` / `rejected_mail.php` wired up into `BookableCell::routeActions()`'s `?book`/`?delete` approve/reject flow (user's choice over deletion) instead of being dead code with undefined variables.
- ✅ The backtick `SMTPSecure` bug was already fixed in a prior session (uses the bare `PHPMailer::ENCRYPTION_STARTTLS` constant).
- ✅ `.env.example` filled in with `MAIL_*`/`ADMIN_EMAIL`/`ENVIRONMENT`/`APP_DOMAIN` placeholders. **Also found and fixed a separate, more serious issue**: `.env.example` contained a real (if apparently no-longer-used) `CAP_SECRET_KEY` value committed since `c5472a7` — replaced with a placeholder in the working tree; the real value still exists in git history (user declined a history scrub for this one).
- ✅ CSP `report-uri`/`report-to` violation-reporting endpoint added (`csp-report.php`, logs to `storage/logs/csp-violations.log`).
- ✅ Stale local-only `dependabot/*` branches deleted (they were never pushed to `origin`; the one that had been on `origin` was already auto-deleted by GitHub before this pass).
- `kontakt.php` has no contact form at all (confirmed via code read) — the CAPTCHA/rate-limiting suggestion doesn't apply; nothing to do here.

## Code quality / maintainability

- ✅ SMTP setup consolidated into `assets/class/Mailer.php`, used by `phpmailer.php`, `phpmailer_2.php`, `confirmation_mail.php`, `rejected_mail.php`, `medlem.php`.
- ✅ Deleted `assets/config/mailer_config.php` — fully dead code that also hardcoded a real SMTP username/host as a fallback (same anti-pattern as the earlier password regression).
- ✅ `tests/browser/*` (Codeception) removed entirely per user decision (not wired to CI, considered redundant); `codeception/*` dev dependencies and `docs/testing_strategy.md` references removed too. A future CI-wired browser-test solution can be reconsidered later.
- ✅ `BookingIntegrationTest::testBookingForm` updated to match current `BookableCell::bookingForm()` markup.
- ✅ CI pipeline added: `.github/workflows/ci.yml` (MySQL service, PHP 8.3, `php -l` lint, `composer test`) on push/PR to `main`.

## Testing

- ✅ Added `tests/EnvExampleCompletenessTest.php` (fails if any `getenv()`/`$_ENV` var used in the app is missing from `.env.example`).
- ✅ Added `tests/MailerHardcodedCredentialFallbackTest.php` (fails if a `getenv('X_PASSWORD'|'X_SECRET'|...) ?: '<literal>'` pattern reappears in mailer-related files).
- Note: these two new tests were written and reasoned through manually but not run locally (no PHP CLI available to the agent in this environment) — run `composer test` to confirm they pass.

## Infrastructure

- ✅ Added a README note that `docker-compose.yml`'s dev credentials must not be reused in production.
- ✅ No further automated Dependabot review process was set up beyond the branch cleanup above — Dependabot will recreate branches as needed; up to the user to review/merge them going forward.
