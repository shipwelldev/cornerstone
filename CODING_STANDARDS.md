# Coding Standards

This is the single coding standard for Cornerstone maintenance and every generated application. The words Rule, Guideline, and Recommendation have the meanings below; they are not interchangeable.

## Principles

Keep these overarching principles in mind when writing code:

- YAGNI "You ain't gonna need it" is your friend. That means: Implement things when you actually need them, never when you just foresee that you need them.
- Prefer simple clear and obvious code over clever "shortcuts". Assume a novice may need to read and understand what the code does.
- Class/Method/Variable names matter. Verbosity is preferred over short vague names that don't convey what it is or what it does.
- Prefer explicit code over implicit code. Don't assume something is clear, make it clear.

## Authority and precedence

Rules are mandatory. Guidelines are expected and may be overridden only by an explicit human decision. Recommendations are preferred; an agent may depart from one when context clearly favors another approach if it discloses the departure and rationale.

Applicable law and an explicit security policy take precedence. After those, this document overrides generated agent files, package guidance, framework examples, generic agent instructions, and individual preferences. A more specific Rule in this document overrides a more general one. When two Rules genuinely conflict, stop and obtain a recorded human decision rather than silently choosing one.

Only a human project owner or a human reviewer they designate may change this standard, authorize a Rule exception, weaken enforcement or analysis, or approve a suppression. Human authority must be explicit; an agent's request, inference, or generated approval is not authority. Agents cannot weaken Rules, enforcement, analysis, or suppressions.

## Suppressions and exceptions

A formatter exclusion, disabled rule, static-analysis ignore or baseline, test skip, architecture-test exception, coverage exclusion, or inline suppression is a suppression. Suppressions require prior human approval and must be the narrowest possible scope with a written reason. Temporary suppressions must link to an issue that owns removal. Permanent suppressions must record why the Rule cannot be enforced reliably. Agents may identify and propose a suppression, but may not add, broaden, renew, or approve one.

A human-approved exception changes neither the Rule nor its default enforcement. Record its scope and rationale where reviewers will encounter it. Do not use an exception to conceal a failing gate.

## Rules

### Language, formatting, and analysis

- **LANG-01** Every standalone PHP file declares `strict_types=1`, and application declarations use accurate native parameter, property, and return types.
- **LANG-02** Committed PHP follows the committed PER-based Pint configuration. `composer fix` may correct source; `composer verify` validates formatting without mutation.
- **LANG-03** Larastan analyzes all standalone application PHP at the committed level. New application PHP belongs in an analyzed path.
- **LANG-04** Blade contains no raw PHP. PHP behavior belongs in a class; Blade remains presentation-only.
- **LANG-05** Use supported Laravel metadata attributes when the framework provides them for application metadata.

### Structure and naming

- **STRUCT-01** Follow Laravel naming and placement conventions. Classes and files use PascalCase; methods and variables use camelCase; database names use snake_case; Blade views use kebab-case.
- **STRUCT-02** Extracted application orchestration is a Service under `App\Services` with a `Service` suffix. Do not create application Action classes.
- **STRUCT-03** Data transfer objects belong under `App\Data`, use a `Data` suffix, and are readonly classes with typed promoted properties.
- **STRUCT-04** Controllers and Livewire components are entry boundaries, not homes for extracted business orchestration.
- **STRUCT-05** Application TODO and FIXME comments link to the issue that owns their removal.

### Eloquent and persistence

- **DATA-01** Every application-owned model explicitly declares mass-assignment metadata through a supported attribute. When a model has a factory, it declares that factory through a supported attribute. Hidden, visible, cast, table, connection, timestamp, and key metadata are explicit whenever behavior differs from an unambiguous framework default.
- **DATA-02** Application-owned Eloquent models under `App\Models`, including custom pivot and morph-pivot models, use `Glhd\Bits\Database\HasSnowflakes` for their primary identifiers. Generated model stubs preserve this convention. Related foreign keys use compatible types; framework infrastructure tables and pivot tables without an application-owned model do not acquire this requirement merely by existing.
- **DATA-03** Every Eloquent relationship is an explicitly named model method with an accurate native `Relation` subtype return type.
- **DATA-04** Tests create application models through factories and useful factory states rather than duplicating model construction.
- **DATA-05** Migrations are safe for populated databases, preserve data intentionally, use matching key types and indexes, and avoid environment-specific schema behavior.
- **DATA-06** Multi-write operations that must succeed or fail together use a database transaction. Race-sensitive operations use an appropriate atomic lock, uniqueness constraint, or row lock.
- **DATA-07** Application code does not permit hidden lazy loading.

### Livewire, Blade, and browser state

- **UI-01** Livewire components are named classes under `App\Livewire` with external Blade views. Every component has a direct Livewire test.
- **UI-02** Livewire public properties have native types and are validated at the boundary or locked against client mutation. Authorization-sensitive identifiers are locked even when validation also applies.
- **UI-03** Livewire owns server and persisted interaction state. Alpine owns ephemeral browser-only state and uses Livewire's bundled Alpine runtime; do not install or boot a duplicate Alpine runtime.
- **UI-04** Blade uses escaped `{{ }}` output by default. Unescaped output is permitted only for an explicitly trusted, reviewed safe-HTML value.

### Entry boundaries, security, and APIs

- **BOUNDARY-01** Validate and authorize untrusted operations at every HTTP, Livewire, console, queue, and consumer API entry boundary. Validation never substitutes for authorization.
- **BOUNDARY-02** Generate internal URLs with named routes. Read environment variables only from configuration files; application code consumes configuration.
- **BOUNDARY-03** Never expose, log, commit, paste, or place secrets in command arguments. Treat credentials and user-sensitive data as secrets throughout errors, queues, logs, and third-party calls.
- **BOUNDARY-04** Uploads require validation of type and size, application-generated storage names, non-executable storage, and explicit authorization for upload and retrieval.
- **BOUNDARY-05** Preserve Laravel's CSRF, signed URL, encryption, hashing, and request protections. Disabling a framework protection is a Rule exception.
- **BOUNDARY-06** Apply explicit rate limiting to authentication, expensive, abusive, or externally exposed operations, with limits chosen for the operation rather than a universal placeholder.
- **BOUNDARY-07** Consumer APIs are versioned and return Eloquent data through API Resources rather than exposing models directly.
- **BOUNDARY-08** Exceptions are surfaced to Laravel's reporting pipeline. Do not swallow failures or convert them to misleading success responses.

### Testing

- **TEST-01** Pest is the only application test style. Every behavior change has a functional test at its public seam with semantic assertions.
- **TEST-02** Feature tests use the globally configured database refresh. Tests that create models use factories.
- **TEST-03** Every Livewire component has a direct component test in addition to any route or browser coverage.
- **TEST-04** Test names describe behavior. Avoid tautologies, implementation-coupled mocks, broad snapshots, weak source-text proxies, and assertions that merely restate configuration.
- **TEST-05** Tests contain no unowned skips or placeholders. TODO and FIXME comments are issue-linked.
- **TEST-06** Tests assert semantic outcomes and observable workflow transitions. Visible content assertions are appropriate when content is the behavior, such as validation feedback, a calculated recommendation, or an accessible control name. Scope assertions to the relevant result or control; avoid incidental instructional copy, styling, and unscoped strings or numbers that could appear elsewhere on the page.

## Enforcement map

Rule identifiers are stable references for review and tool failures. Do not renumber existing rules when adding another. Every rule still requires review of its intent; automation covers only the facts listed here.

| Rules | Automated evidence | Remaining human review |
| --- | --- | --- |
| LANG-01–03 | Pint strict-types/formatting checks and Larastan at the committed level and paths | Accurate types, complete analyzed paths, and the limitations recorded below |
| LANG-04, STRUCT-01 | Architecture checks for Blade raw PHP, view filenames, declaration paths/casing, and method names | Presentation boundaries, variable/database naming, and understandable names |
| LANG-05, DATA-01 | Architecture reflection checks for mass-assignment attributes | Other supported metadata, factory declaration and metadata intent |
| STRUCT-02–03 | Architecture checks for prohibited Actions, Service/Data namespace contents, suffixes, readonly Data and typed promoted properties | Meaningful orchestration and Data boundaries |
| STRUCT-04–05 | Review | Thin entry boundaries and issue-linked comments |
| DATA-02 | Architecture checks for model and published-stub snowflake traits | Compatible schema, foreign keys, and identifier representation |
| DATA-03–07 | Larastan checks declared relationship return types where inferable; application tests exercise behavior | Cardinality, factories, data preservation, concurrency, and eager loading |
| UI-01–02 | Architecture checks for Livewire placement and native public-property types; direct component tests | Test completeness, validation, locking, and authorization |
| UI-03–04 | Application/browser tests where relevant | State ownership, a single Alpine runtime, escaping and trusted HTML provenance |
| BOUNDARY-02 | Architecture check rejects application `env()` use | Named routes and configuration boundaries |
| BOUNDARY-01, BOUNDARY-03–08 | Functional tests and analysis where relevant | Security, entry-boundary coverage, compatibility, and observable failures |
| TEST-01–06 | Architecture check rejects PHPUnit application test classes; Pest runs behavior tests | Semantic assertions, adequate failure cases, factories, refresh scope, and no unowned skips |

### Existing analysis limitations

The committed `phpstan.neon.dist` contains these existing suppressions. This inventory records their effect; it does not authorize additional ignores or DocBlocks.

| Suppression | Current scope and rationale | Review obligation |
| --- | --- | --- |
| `missingType.iterableValue` | All analyzed paths; native PHP cannot express iterable element types and the project avoids DocBlocks. Unmatched reports are disabled. | Analysis cannot prove element types from a bare `array` or iterable declaration. Prefer meaningful Data objects for owned structures, validate external collections at boundaries, and review element assumptions explicitly. A future narrowing requires evidence that framework collections and tests remain analyzable under the no-DocBlock policy. |
| `missingType.generics` for `HasFactory` | Only the matching trait diagnostic under `app/Models/*`; Larastan does not infer the generic from `UseFactory`. Unmatched reports are enabled. | Keep the factory attribute accurate; reassess when attribute inference changes. |
| `missingType.generics` for `Factory` | Only the matching parent-class diagnostic under `database/factories/*`; Larastan does not infer the generic from `UseModel`. Unmatched reports are enabled. | Keep the model attribute accurate; reassess when attribute inference changes. |

The factory limitations reference [Larastan issue 2328](https://github.com/larastan/larastan/issues/2328). `composer analyse` passing does not establish iterable element correctness. Architecture namespace exclusions have specific roles: the canonical controller, Livewire, and test-base namespaces are excluded only from checks that prohibit those declarations elsewhere. Review changes to these scopes under the suppression policy.

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
