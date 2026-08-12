# Unused files pulled out of the Laravel app root (2026-08-12)

Nothing in the running app references any of these. Paths below are relative to
the project root — restore by moving a file back to the same path.

## Frontend build toolchain (never used)

The app loads its stylesheets and scripts as plain static files from `public/`
(`public/css/site.css`, `public/css/admin.css`, `public/js/admin.js`), pulled in
with `asset()` in `resources/views/layouts/app.blade.php` and
`layouts/admin.blade.php`. The Vite/Tailwind pipeline was scaffolding that was
never wired up — `node_modules/` was never even installed.

- `package.json`
- `vite.config.js`
- `resources/css/app.css` — Tailwind entrypoint
- `resources/js/app.js`, `resources/js/bootstrap.js` — axios bootstrap
- `resources/views/welcome.blade.php` — stock Laravel splash, the only view that
  called `@vite(...)`; no route pointed at it (`/` goes to `HomeController`)

Related edit made in the app: `composer.json` had `npm install` / `npm run build`
in its `setup` script and `npm run dev` in its `dev` script. Both steps were
removed so those scripts still work.

`.env` still has a `VITE_APP_NAME` line. Harmless, left alone.

## Leftover dev/scaffolding files

- `index.php` — legacy redirect to `http://lab-booking.test/`, i.e. to itself.
  Left over from the pre-Laravel version of the site; Laravel serves from
  `public/index.php`.
- `screenshot-dash-current.cjs` — throwaway Playwright script. Points at a
  `/private/tmp` scratchpad path that no longer exists, and has a staff login
  hardcoded in it.
- `README.md` — stock Laravel framework README, nothing project-specific.
- `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` — stock Laravel
  example tests. `phpunit.xml` and `tests/TestCase.php` were kept so real tests
  can be added later.
