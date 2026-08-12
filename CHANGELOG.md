# Changelog

All Notable changes to `payment` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## Unreleased

### Added
- A test suite covering the service provider, the facade, the payment events, the configuration merge and the blade
  rendering of the redirection form. It runs against a driver that answers like a gateway would, without touching the
  network, and covers 100% of `src/`.
- GitHub Actions workflows running the test suite (PHP 8.4 and 8.5, Laravel 12 and 13, lowest and highest
  dependencies), the coding style check, the static analysis and the code coverage on every pull request and on every
  push to `master`. The coverage has to stay above 95%.
- A `Dockerfile` (with pcov, for coverage) and a `Makefile` to run the test suite and every check inside a container,
  so that no PHP installation is needed on the host.
- A `phpcs.xml.dist` ruleset, a `phpstan.neon.dist` configuration and a `rector.php` configuration.
- The `test-coverage`, `analyse`, `rector` and `ci` composer scripts.
- The payment manager can be resolved and injected by its class name (`Shetabit\Multipay\Payment`) as well, next to
  the `shetabit-payment` binding the facade uses. The name of that binding is available as
  `Shetabit\Payment\Facade\Payment::SERVICE_NAME`.

### Changed
- **Breaking:** PHP 8.4 is now the minimum required version (was PHP 8.0).
- **Breaking:** only the last two major versions of Laravel are supported: `^12.0|^13.0` (was `^8.0` and up). The
  package now also requires `illuminate/contracts` and `illuminate/view`, which it has always used.
- **Breaking:** `shetabit/multipay` is required as `^3.0.1` (was `^2.4`). 3.0.0 is left out on purpose: it requires
  `guzzlehttp/guzzle: ^8.0`, which `laravel/framework` does not allow, and 3.0.1 relaxed that to `^7.8.2|^8.0`.
- The package was modernized for PHP 8.4. Every parameter, return value and property of `src/` declares a type now,
  the payment events use promoted `readonly` constructor properties, class constants are typed and
  `dirname(__DIR__, 2)` replaces the `__DIR__ . '/../../'` paths.
- **Breaking:** the `$driver`, `$invoice` and `$receipt` properties of `InvoicePurchasedEvent` and
  `InvoiceVerifiedEvent` are `readonly`, so a listener can no longer overwrite them.
- The listeners of the payment events are only registered once per process. The payment manager keeps them in a static
  property, so booting a second application (a test suite, a queue worker) used to dispatch every event twice.
- The redirection form's view is read with a check in place: an unreadable view now raises a `RuntimeException` that
  names the file, instead of rendering a form built from `false`.
- A published redirection form's view is rendered to a string, instead of handing the view object to the payment
  manager and relying on it being cast to a string.
- `resources/views/redirectForm.blade.php` carries the `@csrf` field itself now, so a published view behaves like the
  one the package renders on the fly. A view that was published before this release does not have it: add `@csrf`
  inside the `<form>` of `resources/views/vendor/shetabitPayment/redirectForm.blade.php` if the driver in use posts
  the form back into the application. Its countdown script also uses `let`, `textContent` and a function reference
  instead of `var`, `innerHTML` and a string passed to `setTimeout()`.
- `PaymentServiceProvider::registerEvents()` is `protected` and the redirection form's renderer moved into its own
  `registerRedirectionFormRenderer()` method.
- The `@method` annotations of the facade match the payment manager again, including the `RedirectionForm` that
  `pay()` returns, and the events dropped the docblocks that only repeated their types.
- `phpunit.xml` was migrated to the current PHPUnit schema and made strict about risky tests, warnings, notices and
  deprecations. A deprecation a dependency triggers on its own (`ignoreIndirectDeprecations`) is the exception: the
  database configuration of Testbench 10 reads the deprecated `PDO::MYSQL_ATTR_SSL_CA` constant on PHP 8.5, which is
  nothing this package can fix. A deprecation that `src/` triggers still fails the build.
- The development dependencies were updated: PHPUnit 11.5/12/13, Testbench 10/11, PHPStan 2.2 and Rector 2.6.
- **Breaking:** the readme files document a custom driver the way `shetabit/multipay` expects it today: without
  redeclaring `$invoice` and `$settings`, with the return types of `purchase()`, `pay()` and `verify()`, and with
  `pay()` returning a `RedirectionForm` instead of a Laravel redirect.

### Removed
- The Travis CI configuration (`.travis.yml`), replaced by GitHub Actions.
- The StyleCI configuration (`.styleci.yml`), replaced by the PHP_CodeSniffer workflow.
- The `squizlabs/php_codesniffer` development dependency, replaced by `phpcsstandards/php_codesniffer`, the package of
  the [team that maintains PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer).
- The StyleCI, Code Climate and Scrutinizer badges from the readme files, replaced by the workflow badges and a code
  coverage badge.
- The `config/app.php` instructions from the readme files: package discovery has registered the provider and the alias
  since Laravel 5.5, and 12 is the oldest version supported now.

## Date - 2019-01-09

### Fixed
- Nothing

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
