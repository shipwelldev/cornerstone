# Artisan and skills tooling: proposed direction

Status: design only. Boost remains installed and interactive tooling selection remains intentional. This document scopes a future package; none of the proposed commands below exists yet.

## Evidence and boundaries

The current lockfile installs `laravel/boost` 2.7.0. Composer installs Boost interactively, preserves `boost.json`, adds `pestphp/pest-plugin-agent` and its skill, and updates configured resources. The project owns the remove-example skill under `.ai/skills`; the application itself does not call Boost. Personal usage cannot be inferred from ignored local configuration, so capability availability below is not proof that every developer uses it.

The installed package exposes tools under `vendor/laravel/boost/src/Mcp/Tools`, guideline composition in `src/Install/GuidelineComposer.php`, and installation/update commands under `src/Console`. Its hidden `boost:execute-tool` command already executes registered MCP tool classes using encoded arguments. That internal adapter is not the proposed public command interface: it still depends on MCP request/response classes and lacks ordinary capability-specific help.

The design goal is to let humans and shell-equipped agents use the same capabilities, with focused skills loading workflow guidance when needed. It does not depend on a claim that all MCP hosts load tools eagerly or that MCP is universally obsolete.

## Capability inventory

| Available Boost capability or integration | Existing application/package CLI starting point | Remaining work before replacement |
| --- | --- | --- |
| Application information | `php artisan about --json`, `composer show --locked`, `npm ls --depth=0` | Decide whether a bounded, redacted combined inventory adds value; do not duplicate existing output gratuitously |
| Database connections, schema and queries | `php artisan db:show`, `db:table`, `db`; inspect `help` for options | Identify actual need for non-interactive read-only queries, connection selection, output limits, and schema formats |
| Tinker execution | `php artisan tinker --execute` | Document environment, side effects, error behavior and output limits in a focused skill; no generic execution wrapper by default |
| Last error, log entries and browser logs | `php artisan pail`, application log files, browser-test diagnostics | Bounded structured log extraction and browser capture may still need dedicated implementation; avoid dumping sensitive records |
| Version-specific documentation search | `php artisan docs` opens documentation | Search is a real gap: Boost uses its hosted documentation API and installed-package versions; resolve provider access and supported-version coverage before replacing it |
| Absolute URL generation | Application URL configuration and named-route generation | Establish whether a public route-to-URL command is useful; preserve named routes and environment awareness |
| RecordRule and generated guidance | Canonical Markdown and `.ai/guidelines` | Keep rule changes subject to human authority; provide reviewed source updates rather than silent policy generation |
| Skills and personal tooling installation/update | `.ai/skills`, interactive Boost install/update; Pest agent skill installed by Composer | Replace discovery, selected-tool output, package skills, update ownership and preservation of user edits |
| Cornerstone generators/stub publication | `php artisan make:*`, `cornerstone:stubs` from `cornerstone-support` | Already independent of Boost; retain their existing behavior |

Recheck this inventory against the locked version and developer workflows when implementation begins. Prefer an existing command plus a skill where the command already satisfies the task.

## Public interface and personal configuration

Commands should have ordinary named arguments/options, discoverable `list` and `help`, useful examples, and bounded readable output. Offer `--json` where machines need structured data; use a documented versioned schema, send diagnostics to stderr, and return nonzero for invalid input or failed operations. Never emit a success-shaped object for a failure. Respect `--no-interaction`; incomplete configuration should produce an actionable error or an explicitly documented optional-step skip.

An installer should let the developer choose tools interactively and persist those choices in ignored configuration. An explicit tool option can support unattended installation without guessing preferences. Project sources remain under `.ai`; generated destinations are adapters. Updates should preview changes, distinguish generated content from user edits, and require deliberate replacement for conflicts. Repeated install/update must be safe, and removing the package must not remove user-authored guidance. Keep implementation per capability independent of editor-specific adapters.

## First capability and migration acceptance

Start with a focused **project orientation skill** using existing `about`, package-listing, command-help, and canonical documentation sources. Trial it with a human and an agent in fresh checkouts with no Boost configuration. Record the commands needed, irrelevant output, missing facts, and whether reading only the focused skill is sufficient. Add a proposed `cornerstone:context` command only if that trial demonstrates a useful missing bounded inventory; it should return version information, canonical document paths and available workflows without secrets or full configuration values.

Before removing Boost:

1. Confirm which capabilities developers require and explicitly record replacements or accepted omissions; documentation search and browser logs need concrete decisions.
2. Demonstrate CLI help, failures, bounded/structured output and unattended execution at public command seams.
3. Verify install/update for the selected supported tools, package-skill availability, repeatability, and preservation of personal choices and edits.
4. Exercise project creation, repeated setup, dependency update, and exported applications with the replacement package.
5. Provide a migration and rollback procedure that removes only package-owned generated configuration, retains `.ai` sources, and can restore the prior Boost integration.

Keep this work in a separate package project. No protocol framework or speculative command suite is needed to improve Cornerstone's current documentation and commands.
