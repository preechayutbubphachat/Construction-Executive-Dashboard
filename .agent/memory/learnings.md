# Learnings & Gotchas

## 1. Windows App Container & Sandbox Permissions
- **Problem:** Files created by agent or CLI processes on Windows may fail to be read by the PHP process running inside the Windows App Container sandbox (`Access is denied`).
- **Fix:** Immediately run:
  `icacls <filepath> /grant "*S-1-15-2-1:(OI)(CI)(M)"`
  to grant read/write access to the application container token.

## 2. Temporary Directory in Windows Sandbox
- **Problem:** Writing to host user `AppData\Local\Temp` fails in the sandbox environment, breaking PhpSpreadsheet and temp file writers.
- **Fix:** Explicitly override `$env:TEMP` and `$env:TMP` to `application\tests\.tmp` before executing test commands.

## 3. Unauthenticated Route Handling
- **Problem:** In Laravel when calling routes unauthenticated without a named `login` route, `auth` middleware can throw `RouteNotFoundException [login]`.
- **Fix:** In web route handlers or controller constructors, use `abort_unless(request()->user() !== null, 403);` or ensure a login fallback route is defined.

## 4. PHPUnit Wrapper Script vs Direct PHP
- **Problem:** Running `vendor/bin/phpunit` directly on Windows can hang waiting for MSYS/TTY console input.
- **Fix:** Always execute via direct PHP: `php vendor/phpunit/phpunit/phpunit`.
