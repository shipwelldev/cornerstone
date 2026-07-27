# Cornerstone

Cornerstone is a strongly opinionated Laravel Livewire starter kit from [Ship Well Development](https://shipwell.dev). It provides application defaults, quality gates, an accessible example, and optional agent tooling without prescribing product features such as authentication, teams, or billing.

Cornerstone is [available on Packagist](https://packagist.org/packages/shipwelldev/cornerstone). Only the latest stable release is supported.

## What is included

- Laravel 13 and Livewire 4
- Tailwind CSS 4 and Vite
- Pest 4 unit, feature, architecture, and browser testing
- Larastan static analysis and Laravel Pint formatting
- Laravel Boost integration for supported coding agents
- Opinionated generators, published stubs, and coding standards from `cornerstone-support`
- A disposable canonical application example that demonstrates the expected architecture

## Requirements

- PHP `~8.4.0 || ~8.5.0` with the extensions required by `composer.json` and the PDO driver for your selected database
- Composer `>=2.9 <3.0`
- Node.js `22.12+` or `24`, with its bundled npm; npm is the only supported JavaScript package manager
- A supported database: SQLite 3.26+; MySQL 8.4 or 9.7 LTS; MariaDB 10.11, 11.4, 11.8, or 12.3 LTS; PostgreSQL 14 through 18; or SQL Server 2022 or 2025 with current patches
- A supported browser: the last two stable desktop majors of Chrome, Edge, Firefox, or Safari, current Chrome for Android, or current Safari on iOS

## Create an application

Create a new project from the published Composer package:

```shell
composer create-project --prefer-dist shipwelldev/cornerstone your-app
```

Then enter the new application directory:

```shell
cd your-app
```

Project creation:

- Installs the locked Composer dependencies.
- Updates `cornerstone-support` to its latest compatible release.
- Publishes the initial Cornerstone generator stubs without overwriting existing files.
- Creates `.env` and generates an application key.
- Installs locked npm dependencies and Playwright Chromium.
- Builds the production frontend assets.
- Runs the interactive Laravel Boost installer when creation is performed in a terminal.

When project creation is run non-interactively by an agent or CI, every unattended setup step still runs, but the Boost installer is skipped. Run `composer setup` later in an interactive terminal to finish Boost configuration.

Cornerstone deliberately does not create a database or run migrations during project creation.

## Set up an existing checkout

Run the canonical setup command after cloning or otherwise receiving an existing Cornerstone application:

```shell
composer setup
```

Setup is safe to repeat. It installs from the Composer and npm lockfiles, preserves an existing `.env` and application key, configures Boost when an interactive terminal is available and `boost.json` does not exist, installs Playwright Chromium, and builds production assets. It does not initialize or migrate the database.

## Configure the database

Choose the database explicitly before running the application.

For SQLite, create the file with a portable PHP command and set the connection in `.env`:

```shell
php -r "is_file('database/database.sqlite') || touch('database/database.sqlite');"
```

```dotenv
DB_CONNECTION=sqlite
# Leave DB_DATABASE unset to use database/database.sqlite.
```

For MySQL, MariaDB, PostgreSQL, or SQL Server, create an empty database using that platform's administration tools, install its PHP PDO extension, and set the corresponding `.env` values. For example:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_app
DB_USERNAME=your_app
DB_PASSWORD=
```

Use `mysql`, `mariadb`, or `sqlsrv` instead of `pgsql` as appropriate. Then apply the schema:

```shell
php artisan migrate
```

Database choice and migration are intentionally outside project creation and setup.

## Maintain published stubs

Project creation publishes the initial stubs from `cornerstone-support`. Later package updates do not replace published stubs because an application may have customized them.

To replace the application's published stubs with the current Cornerstone versions, explicitly run:

```shell
php artisan cornerstone:stubs --force
```

Review or preserve any local stub customizations before using `--force`.

## Development workflows

- `composer setup` installs dependencies, prepares the environment, configures Boost interactively when needed, installs browser tooling, and builds assets.
- `composer dev` supervises the web server, queue listener, application log stream, and Vite; if one process fails, the group stops.
- `composer fix` applies the formatter and may rewrite source. Review its diff.
- `composer analyse` runs Larastan independently.
- `composer test` runs all Pest suites, including Chromium browser tests, independently and in parallel.
- `composer build` creates production frontend assets independently.
- `composer verify` performs non-correcting formatting validation, analysis, all tests, and a production build in that order.

For normal development, configure and migrate the database, run `composer dev`, and open the URL printed by the Laravel server. Before requesting review, run `composer fix`, review its changes, and run `composer verify`.

## License

Cornerstone is available under the [MIT License](LICENSE.md).
