# Cornerstone

Cornerstone is a strongly opinionated Laravel Livewire foundation from [Ship Well Development](https://shipwell.dev). It provides application defaults, quality gates, an accessible example, and optional agent-mediated integration workflows without adding product features such as authentication, teams, or billing.

## Prerequisites and compatibility

- PHP `~8.4.0 || ~8.5.0` with the extensions required by `composer.json` and the PDO driver for your selected database
- Composer `>=2.9 <3.0`
- Node.js `22.12+` or `24`, with its bundled npm; npm is the only supported JavaScript package manager
- A supported database: SQLite 3.26+; MySQL 8.4 or 9.7 LTS; MariaDB 10.11, 11.4, 11.8, or 12.3 LTS; PostgreSQL 14 through 18; or SQL Server 2022 or 2025 with current patches
- A supported browser: the last two stable desktop majors of Chrome, Edge, Firefox, or Safari, current Chrome for Android, or current Safari on iOS

__NOTE:__ Only the latest stable Cornerstone release is supported.

## Create an application

> [!WARNING]
> Cornerstone is a Work In Progress and has not yet been relased.

Once relased, you will be able to create the project from its Composer distribution:

```shell
composer create-project --prefer-dist shipwelldev/cornerstone your-app
cd your-app
```

Creation installs locked Composer and npm dependencies, creates `.env` and an application key, installs Playwright Chromium, and builds production assets. It deliberately does not create or migrate a database.

If you received the source without running Composer project creation, run `composer setup`. Setup is repeatable: it preserves an existing `.env` and application key, installs from both lockfiles, installs Chromium, and builds assets. It never initializes or migrates the database.

## Choose and migrate a database

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

Database choice and migration are intentionally outside creation and setup.

## Application workflows

- `composer setup` repeats dependency, environment, browser, and asset preparation without changing an existing key or database.
- `composer dev` supervises the web server, queue listener, application log stream, and Vite; if one process fails, the group stops.
- `composer fix` applies the formatter and may rewrite source. Review its diff.
- `composer analyse` runs Larastan independently.
- `composer test` runs all Pest suites, including Chromium browser tests, independently and in parallel.
- `composer build` creates production frontend assets independently.
- `composer verify` performs non-correcting formatting validation, analysis, all tests, and a production build in that order.

For normal development, choose and migrate the database, run `composer dev`, and open the URL printed by the Laravel server. Before requesting review, run `composer fix`, review the changes, and run `composer verify`.

## License

Cornerstone is available under the [MIT License](LICENSE).
