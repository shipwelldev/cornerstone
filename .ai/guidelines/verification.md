## Verification

Only consider implementation work complete after both canonical workflows pass in this order:

1. Run `composer fix`, review every correction, and resolve unintended changes.
2. Run `composer verify` and fix every failing non-correcting gate.

If a later fix changes code, restart verification from `composer fix`.
