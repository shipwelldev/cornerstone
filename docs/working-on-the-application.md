# Working on the application

Start with the [README setup and commands](../README.md#development-workflows), then read the [coding standards](../CODING_STANDARDS.md) and the current [domain vocabulary](../CONTEXT.md). These sources apply with or without agent tooling. The standards contain stable rule identifiers, an enforcement map, and the limitations of static analysis.

## Find the behavior

The disposable expedition example is a small vertical slice:

| Concern | Starting point |
| --- | --- |
| HTTP route and component boundary | `routes/web.php`, `app/Livewire/Home.php` |
| Planning calculation | `app/Services/ExpeditionPlanningService.php` |
| Result and finite domain values | `app/Data`, `app/Enums` |
| Presentation | `resources/views/livewire/home.blade.php`, `resources/views/components` |
| Public behavior tests | `tests/Feature`, `tests/Unit/ExpeditionPlanningServiceTest.php`, `tests/Browser` |
| Mechanically enforceable conventions | `tests/Unit/ArchitectureTest.php` |

Follow a change from its entry boundary through the calculation and displayed result. Keep domain terminology consistent across code, tests, and `CONTEXT.md`.

## Common tasks

**Add a Livewire feature.** Inspect the current example, use `php artisan help make:livewire` and the generator, and keep server state in a named component with an external Blade view. Extract orchestration into a Service only when there is a meaningful boundary. Add a direct component test for outcomes and failure cases; add browser coverage when browser interaction is part of the behavior. Run `composer fix`, review the diff, and run `composer verify`.

**Change generated code.** The application's published `stubs` are authored, customizable inputs for future generator runs. Generate an artifact in a disposable checkout and inspect its behavior, types, and placement. Package updates preserve local stubs; explicit replacement uses the [documented stub command](../README.md#maintain-published-stubs). A change to the shared generator implementation belongs in `cornerstone-support`.

**Remove the expedition example.** Follow the [remove-example skill](../.ai/skills/remove-example/SKILL.md), including route ownership and checks for reused code. Update this example map and `CONTEXT.md` alongside the slice. Never assume a starter file is still exclusively example-owned.

## Own the source, generate the integration

| Content | Ownership |
| --- | --- |
| README, coding standards, domain vocabulary, this guide | Canonical project documentation; edit directly and keep links current |
| `.ai/guidelines`, `.ai/skills` | Project-authored sources; keep guidance focused and link to canonical documents |
| `boost.json`, generated agent instructions and tool configuration | Personal developer tooling choices; ignored intentionally |
| `stubs` | Application-owned generator templates; preserve local customizations |
| `vendor`, `node_modules`, compiled assets | Generated from dependencies and source; regenerate through documented commands |

Interactive `boost:install` lets each developer select their tooling. Non-interactive setup intentionally skips that selection; it does not prevent a human or agent from reading this guide or using the CLI. Run `composer setup` in an interactive terminal when ready to choose integrations. Change shared guidance in `.ai`, not in generated files that an update may replace.

For starter-kit maintenance, the repository checkout also contains `CONTRIBUTING.md` and `docs/maintenance`. Those documents, maintenance tests, and repository-only workflows are intentionally absent from exported applications. Application tests, standards, this guide, and project-authored `.ai` sources ship downstream.
