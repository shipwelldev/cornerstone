# Coding Standards

This is the single coding standard for Cornerstone maintenance and every generated application. The words Rule, Guideline, and Recommendation have the meanings below; they are not interchangeable.

## Authority and precedence

Rules are mandatory. Guidelines are expected and may be overridden only by an explicit human decision. Recommendations are preferred; an agent may depart from one when context clearly favors another approach if it discloses the departure and rationale.

Applicable law and an explicit security policy take precedence. After those, this document overrides generated agent files, package guidance, framework examples, generic agent instructions, and individual preferences. A more specific Rule in this document overrides a more general one. When two Rules genuinely conflict, stop and obtain a recorded human decision rather than silently choosing one.

Only a human project owner or a human reviewer they designate may change this standard, authorize a Rule exception, weaken enforcement or analysis, or approve a suppression. Human authority must be explicit; an agent's request, inference, or generated approval is not authority. Agents cannot weaken Rules, enforcement, analysis, or suppressions.

## Suppressions and exceptions

A formatter exclusion, disabled rule, static-analysis ignore or baseline, test skip, architecture-test exception, coverage exclusion, or inline suppression is a suppression. Suppressions require prior human approval and must be the narrowest possible scope with a written reason. Temporary suppressions must link to an issue that owns removal. Permanent suppressions must record why the Rule cannot be enforced reliably. Agents may identify and propose a suppression, but may not add, broaden, renew, or approve one.

A human-approved exception changes neither the Rule nor its default enforcement. Record its scope and rationale where reviewers will encounter it. Do not use an exception to conceal a failing gate.

## Rules

### Language, formatting, and analysis

- Every standalone PHP file declares `strict_types=1`, and application declarations use accurate native parameter, property, and return types.
- Committed PHP follows the committed PER-based Pint configuration. `composer fix` may correct source; `composer verify` validates formatting without mutation.
- Larastan analyzes all standalone application PHP at the committed level. New application PHP belongs in an analyzed path.
- Blade contains no raw PHP. PHP behavior belongs in a class; Blade remains presentation-only.
- Use supported Laravel metadata attributes when the framework provides them for application metadata.

### Structure and naming

- Follow Laravel naming and placement conventions. Classes and files use PascalCase; methods and variables use camelCase; database names use snake_case; Blade views use kebab-case.
- Extracted application orchestration is a Service under `App\Services` with a `Service` suffix. Do not create application Action classes.
- Data transfer objects belong under `App\Data`, use a `Data` suffix, and are readonly classes with typed promoted properties.
- Controllers and Livewire components are entry boundaries, not homes for extracted business orchestration.
- Application TODO and FIXME comments link to the issue that owns their removal.

### Eloquent and persistence

- Every application-owned model explicitly declares mass-assignment metadata and its factory through supported attributes. Hidden, visible, cast, table, connection, timestamp, and key metadata are explicit whenever behavior differs from an unambiguous framework default.
- Every Eloquent relationship is an explicitly named model method with an accurate native `Relation` subtype return type.
- Tests create application models through factories and useful factory states rather than duplicating model construction.
- Migrations are safe for populated databases, preserve data intentionally, use matching key types and indexes, and avoid environment-specific schema behavior.
- Multi-write operations that must succeed or fail together use a database transaction. Race-sensitive operations use an appropriate atomic lock, uniqueness constraint, or row lock.
- Application code does not permit hidden lazy loading.

### Livewire, Blade, and browser state

- Livewire components are named classes under `App\Livewire` with external Blade views. Every component has a direct Livewire test.
- Livewire public properties have native types and are validated at the boundary or locked against client mutation. Authorization-sensitive identifiers are locked even when validation also applies.
- Livewire owns server and persisted interaction state. Alpine owns ephemeral browser-only state and uses Livewire's bundled Alpine runtime; do not install or boot a duplicate Alpine runtime.
- Blade uses escaped `{{ }}` output by default. Unescaped output is permitted only for an explicitly trusted, reviewed safe-HTML value.

### Entry boundaries, security, and APIs

- Validate and authorize untrusted operations at every HTTP, Livewire, console, queue, and consumer API entry boundary. Validation never substitutes for authorization.
- Generate internal URLs with named routes. Read environment variables only from configuration files; application code consumes configuration.
- Never expose, log, commit, paste, or place secrets in command arguments. Treat credentials and user-sensitive data as secrets throughout errors, queues, logs, and third-party calls.
- Uploads require validation of type and size, application-generated storage names, non-executable storage, and explicit authorization for upload and retrieval.
- Preserve Laravel's CSRF, signed URL, encryption, hashing, and request protections. Disabling a framework protection is a Rule exception.
- Apply explicit rate limiting to authentication, expensive, abusive, or externally exposed operations, with limits chosen for the operation rather than a universal placeholder.
- Consumer APIs are versioned and return Eloquent data through API Resources rather than exposing models directly.
- Exceptions are surfaced to Laravel's reporting pipeline. Do not swallow failures or convert them to misleading success responses.

### Testing

- Pest is the only application test style. Every behavior change has a functional test at its public seam with semantic assertions.
- Feature tests use the globally configured database refresh. Tests that create models use factories.
- Every Livewire component has a direct component test in addition to any route or browser coverage.
- Test names describe behavior. Avoid tautologies, implementation-coupled mocks, broad snapshots, weak source-text proxies, and assertions that merely restate configuration.
- Tests contain no unowned skips or placeholders. TODO and FIXME comments are issue-linked.

## Guidelines

- DocBlocks should be avoided entirely. A desire to use a DocBlock to designate a complex type or nonintuitive array shape should be considered a sign a formal Object is needed instead.
- Migrations should move forward only, and the `down()` method be omitted.
- Prefer conventional Laravel layers and thin HTTP and Livewire boundaries. Extract only meaningful orchestration or domain seams.
- Introduce interfaces only at real substitution or system boundaries. Prefer framework Facades where available. Agents do not create repository layers.
- Prefer `Fillable` declarations, forward-only migrations, portable queries, bounded result sets, eager loading, and database work that is explicit about ordering and concurrency.
- Use Livewire for server state, Alpine for local state, and Blade for presentation. Create reusable components only at genuine reuse or ownership boundaries.
- Prefer Tailwind utilities over `@apply`. Build mobile-first, semantic, keyboard-operable interfaces targeting WCAG 2.2 AA.
- Prefer semantic behavior tests over implementation assertions. Use Form Requests where an HTTP validation boundary merits a dedicated request object.
- Make queue uniqueness, retry, timeout, and idempotency policy explicit. Outbound HTTP calls define timeouts, appropriate retries, and failure handling.
- Do not declare application classes `final` unless a framework or language constraint requires it.
- Introduce Data objects at meaningful boundaries, not as wrappers around arbitrary arrays with no semantic value.
- Use Artisan generators for framework artifacts. Prefer resourceful or invokable controllers.
- Use Laravel's maintained editor configuration as the baseline rather than introducing a competing editor style.
- Let code breathe where valid PHP and the committed Pint configuration permit it. Use intentional spacing and line breaks to separate meaningful stages, emphasize structure, and improve scanning. Prefer semantic grouping over either maximal compactness or indiscriminate vertical whitespace. The Pint configuration may intentionally depart from PER defaults where additional spacing improves human readability.

A Guideline override requires an explicit human instruction identifying the affected Guideline and scope. Record durable overrides near the code or in project documentation. Agents may not infer an override from existing inconsistent code.

## Recommendations

- Use enums for finite, behaviorally meaningful sets.
- Derive Livewire keys from stable domain identity rather than loop position.
- Keep Tailwind utility ordering readable and allow the formatter to normalize it where tooling supports that.
- Use property-level readonly declarations when they clarify immutability without requiring the entire class to be readonly.

Agents may depart from a Recommendation only when context clearly favors another approach. The completion report or review must name the departure and rationale.

## Human review obligations

Automation proves only mechanically reliable facts. The author must implement and self-review the semantic obligations below; the reviewer must independently verify them before approval. Passing tools is not evidence that these obligations are satisfied.

| Obligation | Author ownership | Reviewer ownership |
| --- | --- | --- |
| Validation and authorization | Identify every entry boundary, validate untrusted input, and enforce the correct policy or permission. | Trace each operation from entry to side effect and verify validation cannot substitute for authorization. |
| Escaping and trusted HTML | Keep output escaped and document the provenance and sanitization of any safe-HTML value. | Review every unescaped output path and reject trust based only on a variable name or type assertion. |
| Secrets and sensitive data | Keep secrets out of source, arguments, chat, logs, errors, fixtures, and third-party payloads. | Inspect changed configuration, workflows, logging, exception, and integration paths for disclosure. |
| Upload safety | Define accepted content, size, storage, naming, retrieval authorization, and lifecycle. | Verify content cannot become executable or publicly retrievable outside the intended policy. |
| Rate limiting and request protection | Select limits and framework protections from abuse risk and operational needs. | Verify coverage, keys, response behavior, and that protections were not bypassed for convenience. |
| Transactions and concurrency | Identify atomicity and race boundaries and choose transactions, locks, and constraints deliberately. | Challenge failure, retry, duplicate, and concurrent execution paths. |
| Migrations and data safety | Plan forward application, populated-table behavior, compatibility, indexing, and recovery. | Review realistic existing data and deployment order; reject destructive assumptions hidden by empty test databases. |
| Queue and HTTP reliability | Define idempotency, timeout, retry, backoff, and terminal failure behavior. | Verify retries cannot duplicate side effects and failures remain observable. |
| API compatibility | Define consumer versioning, resource shape, identifier representation, and error behavior. | Review the external contract independently of internal model convenience. |
| Accessibility and UI semantics | Implement semantic structure, labels, focus, keyboard behavior, state announcements, contrast, and motion handling. | Exercise the interaction and assess WCAG 2.2 AA obligations that automated assertions cannot establish. |
| Test adequacy | Test each behavior at a durable public seam and include meaningful failure cases. | Confirm assertions would fail for plausible regressions and do not test only implementation shape. |
| Relationship and model intent | Declare accurate metadata, relationship cardinality, inverse behavior, loading, and factory states. | Verify metadata and relationships match the domain and query behavior, not merely reflection requirements. |

Authors and reviewers also verify that every suppression or weakening has explicit human authority. If a semantic obligation cannot be established, the change is not complete; do not add a proxy test merely to make it appear automated.
