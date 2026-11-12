# Halcyon Health — Project 2

A PHP/MySQL conversion of the Project 1 careers website. All minimum functionality works without JavaScript. No JavaScript files or client-side validation are used.

## Quick start (XAMPP / local Apache)

1. Extract the ZIP. Put the **project2** folder in XAMPP's `htdocs` folder (for example `C:\xampp\htdocs\project2`). Keep the lowercase folder name.
2. Start Apache and MySQL from the XAMPP control panel.
3. Open `settings.php`. Defaults are host `localhost`, user `root`, password `''` (blank), database `halcyon_health`. Set the host/user/database to match your LOCAL environment if needed. The database password is intentionally blank, as the brief requires.
4. Open a terminal in the project2 folder and run:

   ```text
   php setup.php
   ```

   If PHP is not on your Windows PATH, run:

   ```text
   C:\xampp\php\php.exe setup.php
   ```

   On macOS XAMPP, the PHP executable is usually `/Applications/XAMPP/xamppfiles/bin/php`.
5. Open `http://localhost/project2/index.php` in a browser. Do not double-click PHP files.
6. Open the HR portal and sign in with **admin / admin**.

Setup creates the database, four tables (`jobs`, `eoi`, `users`, `about`), two job records, three explicitly unfinished team templates, and the marker account. Re-running setup preserves applications, edited jobs/members and existing account passwords. Setup is CLI-only and intentionally cannot run from a browser. `schema.sql` alone is not the complete installer: use setup.php, which also creates the EOI table and hashes the marker password.

The account password **admin** is hashed in the users table. It is distinct from the intentionally blank MySQL connection password. These settings are for local marking, not public hosting.

### PHP development server alternative

With MySQL running and setup completed:

```text
php -S localhost:8000 router.php
```

Open `http://localhost:8000/`. Use the supplied router to prevent serving private files. For XAMPP/Apache the included `.htaccess` blocks direct `.inc`, `.sql`, `.md` and `.log` downloads; AllowOverride must be enabled. Apache must process PHP files normally.

Requires PHP 8.0+ with PDO MySQL enabled and MySQL 5.7+/MariaDB 10.3+ with InnoDB. `mbstring`, Composer and Node are not needed. The local database user must be allowed to create the database/tables. If your account cannot create databases, ask your local administrator to provision the database name from settings.php first.

## What is implemented

- `header.inc`, `nav.inc`, `footer.inc`: shared UI used on every displayed PHP page.
- `settings.php`, `database.php`: central database credentials and PDO connections.
- `index.php`: company branding, merged-cell jobs table, database-backed search entry and contextual inclusive employment statement.
- `jobs.php`, `search.php`: all role details come from MySQL. Search checks reference, title, description, salary, reporting line, location, employment type and all requirements. Blank search lists all roles.
- `apply.php`: job reference first; values preserved after server validation errors. Job links prefill references using PHP. The form uses `novalidate`, no `required`, `pattern`, length or range constraints; `aria-required` describes fields without enabling browser validation.
- `process_eoi.php`: POST-only handler, CSRF protection, trimming, stripslashes, server validation, database insert and automatic EOI-table creation if absent. Output is HTML-escaped when displayed, rather than storing HTML entities in the database. Prepared statements safely bind all submitted data.
- `confirmation.php`: session-bound receipt showing the generated EOInumber. The POST/redirect/GET flow and unique submission key prevent ordinary refresh/double-submit duplicates.
- `login.php`, `logout.php`: hashed passwords, regenerated session IDs, generic failed-login messages, five-minute account lock after five failed attempts, POST-only logout and 30-minute inactivity timeout.
- `manage.php`: protected list of all EOIs; exact job-reference filtering; partial first-name, last-name or combined matching; allowlisted sorting; all submitted fields visible in expandable details; status changes to New/Current/Final; two-step deletion of all EOIs by reference. All write actions use POST and CSRF tokens.
- `about.php`: member details, quotes, facts and contributions for both projects load from the `about` table.
- `styles/style.css`: shared responsive styling and accessible focus/error states. Limited inline and embedded examples retain Project 1 requirements. `styles/images/` is reserved for CSS background assets; the current graphic is a CSS gradient.

## Details you must personalise

1. In phpMyAdmin, choose `halcyon_health` → `about` → Browse/Edit. Replace the member names, student IDs, contributions for BOTH projects, quotes, translations and fun facts. Add/delete template members to match your real group. Set `quote_language` to the correct BCP 47 language code (for example `my`, `zh`, `ms`, `en`). `und` means the placeholder language is undetermined. No contributions or personal quotes have been invented.
2. Edit the group name and class day/time/tutor in about.php.
3. Replace the illustration with ONE real photograph of the whole group under 300 KB; change its filename, dimensions, alt text and caption in about.php. The placeholder is not a real group photo and does not meet that rubric item.
4. In footer.inc, replace the Jira placeholder with your actual project URL. Confirm the supplied GitHub URL and replace the demonstration email if a working address is expected. Shared includes mean this edit applies everywhere.
5. You can edit job text in the `jobs` table; put each list item on a separate line. Keep references exactly five alphanumeric characters.

## Validation decisions carried over from Project 1

- Job reference: exactly five alphanumeric characters AND an existing job. The submitted reference is canonicalised to the stored reference.
- Names: 1–20 A–Z/a–z letters, matching the supplied brief; this intentionally does not support every real-world name.
- DOB: exact dd/mm/yyyy, real calendar date, not in the future; stored as a DATE and rendered back in dd/mm/yyyy.
- Gender and state: only the provided options are accepted, even in forged requests.
- Street and suburb: required, up to 40 Unicode characters.
- Postcode: four digits, preserving leading zeroes. State/postcode pairing is not checked because it was not specified.
- Email: valid format, max 254 bytes. Phone: 8–12 digits without spaces/symbols, stored as text.
- Skills: all three listed core skills are required to match the previous form's literal “all inputs required” instruction. Unknown skills are rejected. Other skills is optional with a 5,000-character server limit.
- Arrays submitted in place of scalar fields are rejected. HTML is escaped at every display point, including manager views and repopulated forms. An applicant cannot set their own initial status; the database defaults it to New.

## Testing

Run the validation regression tests:

```text
php tests/validation.php
```

See TEST-REPORT.md for actual checks run during preparation. For browser review, turn JavaScript off and test:

1. Home/Jobs search for `Ruby`, `FE001`, an unknown term, and a blank term.
2. Apply from each role. Submit an empty form: PHP should show errors, with no database insert.
3. Try `31/02/2000`, `29/02/2001`, a future DOB, three-digit postcode, invalid email, short phone, missing skills and an unknown reference.
4. Submit a valid fictional application. Check the receipt number and database status New. Refresh the receipt: it must not create a duplicate.
5. While signed out, visit manage.php directly: it must redirect to login.php. GET process_eoi.php must redirect to apply.php.
6. Sign in; filter first and last names separately and together, filter by reference, sort, expand details, update status, preview/cancel deletion, then confirm deletion on test records.
7. Change a job and a contribution in phpMyAdmin; refresh to confirm the pages reflect the database.
8. Tab through navigation/forms, review 390px and desktop widths, and validate the rendered HTML using the Nu HTML checker (send the rendered source, not the PHP source).

If “could not find driver” appears in the server log, enable `pdo_mysql` in php.ini and restart Apache. If access is denied, check settings.php and the local account. If port 3306 is occupied, resolve the local MySQL service conflict. Public pages deliberately show a generic database error; technical details go to the PHP server log.
