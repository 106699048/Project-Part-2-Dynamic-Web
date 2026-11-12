# Project 2 test report

Tested 11 November 2026 with PHP 8.4, PDO MySQL, MariaDB 11.8 and Chromium headless. Tests used an isolated local database and fictional application data. Test records and edits are NOT included in the deliverable; setup seeds the original two job descriptions and clearly labelled team templates.

## Passed

- PHP syntax checks on all 20 root PHP/include files; the CLI validation test also executed successfully.
- 32 validation assertions: valid application, bad references, invalid/overlong names, impossible/non-leap/future/misformatted/null-byte dates, invalid enumerations, address bounds including Unicode, postcode and phone lengths, email, missing/unknown skills, scalar/array tampering, optional text limit, trimming, slash stripping, HTML escaping and SQL LIKE metacharacter escaping.
- 40 live HTTP/database integration assertions covering:
  - Dynamic home jobs table and database job searches, including no-match and SQL-injection-shaped input.
  - Protected HR access, direct application-handler redirect, PHP reference prefill, absent JavaScript and disabled browser validation.
  - Invalid CSRF rejection, preserved form values, invalid submissions not inserted.
  - Three valid submissions and numbered receipts; receipt refresh and replay do not duplicate records.
  - Default New status despite a forged status value; leading-zero postcode preserved.
  - Login injection rejected; admin/admin accepted; stored HTML escaped on the dashboard.
  - Combined first/last-name filters, reference filter, sort order and malicious sort fallback.
  - Valid status update, invalid status rejection, nondestructive deletion preview and confirmed deletion affecting only the chosen reference.
  - Database About content, logout protection, repeated-failure account lock, hashed password storage.
  - Repeat setup preserves EOIs; edited jobs and member contributions appear dynamically.
  - Valid submission recreates a deliberately removed EOI table.
  - Strict HTML5 parser checks for six rendered public pages.
- Additional strict HTML5 parser check of the authenticated HR dashboard.
- Chromium checks with JavaScript disabled at 1280px and 390px: Home, Jobs, Apply, About and Login. No document-level horizontal overflow; all referenced images loaded.
- JavaScript-disabled browser submitted a completely empty application and received the PHP error summary (client-side validation did not intercept submission).
- Authenticated HR dashboard checked at 1280px and 390px without JavaScript; no document-level horizontal overflow.
- Screenshots of the desktop home/application and mobile application layout were produced; desktop home, mobile application and desktop HR screenshots were visually reviewed.

## Limits / remaining submission checks

- The rendered HTML was parsed using html5lib; this is not a full W3C Nu conformance or WCAG audit.
- Local Apache/XAMPP installation and your particular PHP/MySQL versions still need a smoke test. Automated execution used PHP's development server with the provided router and an isolated database, not XAMPP.
- The real group photo, names, student IDs, class details, actual contributions, quotes/translations, Jira project URL and contact-email confirmation must be supplied by your group.
- The account admin/admin and blank database password are assignment-only defaults. The package has not been deployed publicly.

Re-run the included pure-PHP regression test with `php tests/validation.php`. Follow the README checklist for local browser and marking checks.
