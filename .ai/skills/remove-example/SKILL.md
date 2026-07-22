---
name: remove-example
description: Remove Cornerstone's disposable canonical expedition example after the application has a real vertical slice. Use when the user asks to remove the starter example, expedition planner, canonical homepage, or example domain while preserving user-owned code and assigning the home route deliberately.
---

# Remove Canonical Example

Remove the example as a vertical slice, not as a blind file list. User-owned code may already depend on its generic pieces.

## 1. Inventory

Read `CODING_STANDARDS.md`, inspect the current routes and Livewire components, and search the repository for every reference to these example candidates:

- `app/Livewire/Home.php`
- `app/Data/ExpeditionPlanData.php`
- `app/Enums/Destination.php`
- `app/Enums/MissionPurpose.php`
- `app/Services/ExpeditionPlanningService.php`
- `resources/views/livewire/home.blade.php`
- `resources/views/components/faq-item.blade.php`
- The canonical expedition terms in `CONTEXT.md`
- `tests/Feature/HomePageTest.php`
- `tests/Feature/Livewire/HomeTest.php`
- `tests/Unit/ExpeditionPlanningServiceTest.php`
- `tests/Browser/CanonicalExampleTest.php`

Inspect Git status, the current `HEAD`, recent commits, and the diff. Run `composer verify` to establish the baseline. If verification fails, report the failures and ask whether the user wants to continue before proceeding.

Inventory is complete when every candidate has been classified as example-only, reused by user-owned code, or already absent, and the baseline result is known.

## 2. Decide Route Ownership

Inspect existing routes and Livewire components for credible user-owned replacements for `/`. Present candidates without inventing a new component, then ask the user to choose one of these outcomes:

- Assign `/` and the `home` route name to a specific user-owned component.
- Remove `/` entirely.
- Postpone removal because no real vertical slice is ready.

If `/` or the `home` route name will be removed, include every dependent link, redirect, and test in the proposed cleanup.

Route ownership is decided only when the user explicitly chooses an outcome.

## 3. Confirm The Cleanup

Explain the exact files and code sections that will be deleted, retained, or modified. Call out every candidate referenced by user-owned code and ask whether to retain, rename, or replace it. Pay particular attention to `<x-faq-item>`, `CONTEXT.md`, etc, which may have become shared application assets.

Ask for one explicit confirmation authorizing both the stated cleanup and its required Git checkpoint. Do not modify files or create a commit before receiving confirmation.

Confirmation is complete only when route ownership, reused candidates, the full change list, and Git operations are all explicitly approved.

## 4. Establish A Checkpoint

Use the existing clean `HEAD` as the rollback checkpoint and report its hash. Do not create an empty commit.

If the repository has no commit, inspect all candidate files for secrets and create an initial checkpoint commit only under the confirmation granted in the previous step. Match the repository's commit-message style.

If the worktree is dirty, stop and ask the user how those changes should be handled. Never stage unrelated changes, secrets, or files whose ownership is unclear.

The checkpoint is complete when a safe commit hash exists and has been reported to the user.

## 5. Remove The Slice

Delete example-only classes, views, and tests. Preserve or adapt reused pieces exactly as agreed. Remove only the expedition section from `CONTEXT.md` when user vocabulary exists; delete the file only when it contains no user-owned language.

Apply the chosen home-route outcome and repair all affected named-route references. Keep every surviving Livewire component covered by a direct component test, and preserve functional coverage for every surviving behavior.

Cleanup is complete when searches find no unintended expedition references and no user-owned dependency is broken.

## 6. Verify

Run `composer verify`. Fix failures caused by the cleanup, then inspect Git status and the complete diff. Do not create a post-cleanup commit unless the user explicitly asks for one.

Report the rollback checkpoint, route outcome, removed and retained pieces, verification result, and any baseline failures that remain.

The skill is complete only when full verification passes or the user has a precise report of a pre-existing blocker.
