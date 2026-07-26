# Forbedringsforslag / Improvement Ideas

A running list of potential improvements for the SkelbyForsamlingshus website, grouped by area. Not all items are equally urgent — priority notes are given where relevant.

## Content / UX

- **`index.php` contact info**: the email link (`mailto:info@skelbyforsamlingshus.dk`) is commented out, so visitors can only contact via phone. Either re-enable it (ideally hex/entity-obfuscated like the phone number already is, to reduce scraping) or remove the dead comment entirely.
- **Page `<title>`** is hardcoded to "Skelby Forsamlingshus" in [assets/view/header.php](assets/view/header.php) for every page (index, gallery, arrangementer, udlejning, etc.). Per-page titles would improve SEO, browser tab identification, and bookmarking.
- **`<html lang="en">`** in [assets/view/header.php](assets/view/header.php) but all page content is Danish — should be `lang="da"`. Affects screen readers and browser translation prompts.
- No visible `favicon` link tag in the header — check whether one is served by default path convention or missing entirely.
- No `<meta property="og:*">` / Open Graph or Twitter Card tags — links shared on Facebook/Messenger (likely a common channel for a local forsamlingshus) will show no preview image/description.
- Consider adding structured data (schema.org `LocalBusiness`/`CivicStructure` or `Event` for `arrangementer.php`) to improve Google search result rendering (address, opening info, event dates).

## Accessibility

- Verify color contrast site-wide (e.g. the `subtle-link` phone number style) against WCAG AA.
- Embedded OpenStreetMap iframe on the homepage has no `title` attribute — should have one for screen reader users (e.g. `title="Kort over Skelby Forsamlingshus"`).
- Confirm all `<img>` tags have meaningful `alt` text (the hero image in `index.php` currently has `alt=""`, which is only appropriate if purely decorative — should be described if not).
- Run an automated audit (axe, Lighthouse accessibility score) across all pages and fix flagged issues.

## Performance

- Google Fonts are loaded from `fonts.googleapis.com` (render-blocking, third-party request, GDPR/privacy consideration for EU visitors since it contacts Google servers). Consider self-hosting the fonts (same pattern already used for the Cap CAPTCHA widget, which was vendored locally to avoid third-party requests under the strict CSP).
- Add `loading="lazy"` to below-the-fold images (gallery, hero images) to reduce initial page weight.
- Consider adding cache headers / a CDN in front of static assets (`assets/css`, `assets/js`, `assets/img`) if not already handled by the web server config.
- Audit image sizes in `assets/img` — large source photos served without responsive `srcset` variants can slow down mobile loads.

## Security

- `confirmation_mail.php` / `rejected_mail.php` are dead code (not included/required anywhere, confirmed via repo-wide search) and reference undefined variables (`$mailer`, `$name`, `$dato`) — either wire them up properly or remove them to reduce attack surface and maintenance confusion.
- `confirmation_mail.php` / `rejected_mail.php` also have a pre-existing bug: `$mail3->SMTPSecure = \`PHPMailer::ENCRYPTION_STARTTLS\`;` uses backticks (PHP shell-exec operator) instead of a bare constant reference, so STARTTLS is silently never applied — likely a no-op.
- `.env.example` is missing several variables the code actually depends on (`MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM`, `MAIL_FROM_NAME`, `ADMIN_EMAIL`) — new deployments/onboarding would silently break on missing mail config. Worth filling in the template (with placeholder, non-real values).
- Consider adding a Content-Security-Policy report-uri/report-to endpoint to catch CSP violations in production instead of only relying on manual testing.
- Review whether git history scrubbing (already done for the leaked SMTP password) should also be extended to the still-untouched `dependabot/*` branches, or whether those should just be deleted and let Dependabot recreate them.
- Consider rate-limiting / additional bot protection on `kontakt.php` if it has a contact form without CAPTCHA (currently only the booking and membership forms have Cap CAPTCHA).

## Code quality / maintainability

- Several scripts (`phpmailer.php`, `phpmailer_2.php`) duplicate SMTP setup logic — could be consolidated into a shared mailer helper/class to avoid config drift (as happened with the hardcoded-password regression documented in repo history).
- `tests/browser/*` (Codeception) are not wired into `composer test` / `phpunit.xml` — CI likely never runs browser/functional-UI regression tests. Worth deciding whether to integrate them into CI or explicitly document them as a manual-only suite.
- `BookingIntegrationTest::testBookingForm` already asserts against stale markup that doesn't match the current `BookableCell::bookingForm()` output — should be updated or the test will keep silently mismatching intent.
- No CI pipeline visible in the repo listing (no `.github/workflows`) — adding one to run `composer test`/`phpunit` and basic lint (`php -l`) on push/PR would catch regressions earlier (e.g. the hardcoded-password regression, or the `phpmailer` `\Throwable` bug) automatically instead of relying on manual review.

## Testing

- Add a regression test asserting `.env.example` contains all environment variables actually referenced via `getenv()`/`$_ENV` in the codebase, to prevent future onboarding gaps.
- Add an automated test (or CI lint step) that fails if hardcoded credential fallbacks (e.g. `getenv('X') ?: 'literal-secret'`) are reintroduced into the mailer scripts, given this has regressed once already.

## Infrastructure

- `docker-compose.yml` / `Dockerfile` — confirm production deployment doesn't reuse the dev-oriented compose file with default/weak credentials.
- Consider automated dependency updates review process for Dependabot PRs (multiple `dependabot/*` branches exist per repo history notes) so they don't pile up unmerged.
