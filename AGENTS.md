# Working in this repository

Guidance for any agent that writes or reviews code here: Claude Code, Codex,
Cursor, Copilot, CodeRabbit. `CLAUDE.md` is a symlink to this file.

This file is synced from
[`the-events-calendar/actions`](https://github.com/the-events-calendar/actions)
(`templates/AGENTS.md`) into every TEC plugin repository. Edit it there; a
change made here is overwritten by the next sync.

The rules below are the short version. The long version is the org's shared
skills in [`stellarwp/skills-se`](https://github.com/stellarwp/skills-se); when
this file and a skill disagree, the skill is right and this file needs a fix.

## Plan first: OpenSpec is required

Work on TEC products is planned with [OpenSpec](https://github.com/Fission-AI/OpenSpec)
before it is written. This is enforced: every pull request is checked for a plan.

- Plans do not live in this repository. Every TEC spec lives in one shared store,
  [`the-events-calendar/plans`](https://github.com/the-events-calendar/plans),
  registered locally as `tec-plans`. Every `openspec` command takes
  `--store tec-plans`, and every TEC engineer sets it as the global default with
  `openspec config set defaultStore tec-plans`.
- The change is named after the ticket, lower case: ticket `SOFT-1234`, branch
  `feat/SOFT-1234/short-desc`, change `soft-1234`. One ticket, one change,
  however many repositories it touches.
- Trivial work needs no proposal: a typo, a comment, a one-line fix with no
  behavior change. Anything a reviewer would call a feature or a fix does.
- Guide the developer through OpenSpec rather than running it for them. Do not
  register the store or set the default silently; tell them what is needed.
- Archive a change once, after the last repository merges, not per repo.

Setup and the full workflow: the `openspec-workflow` skill and its TEC
extension `tec-openspec`.

## The skills

Install the shared skills once per machine:

```text
/plugin marketplace add stellarwp/skills-se
/plugin install nexcess-se
```

or `npx skills add stellarwp/skills-se`, or clone the repo and run `install.sh`.
The repository is private; the package routes work once it is public.

Each TEC skill extends a base skill. Load the base first, then the extension.

| When you are | Load |
|---|---|
| Deciding which repo owns a feature, naming a hook, touching common | `tec-products` |
| Planning, proposing, or running any `openspec` command | `openspec-workflow`, then `tec-openspec` |
| Adding a class, service, endpoint, hook, or fixing a bug | `code-quality`, then `tec-code-quality` |
| Changing anything a third party may call | `wordpress-plugin-development` |
| Writing a test, or choosing a suite for one | `writing-tests`, `slic`, then `tec-slic` |
| Reaching for `$wpdb`, `wp_enqueue_*`, `dbDelta`, `wp_cron`, admin notices | `stellarwp-libraries`, then `tec-stellarwp-libraries`; `stellarwp-db` for queries |
| Creating a branch, a commit, or a pull request | `naming-branches`, `writing-commits`, `writing-prs` |

## Where code goes

- **New code** goes under `src/{Domain}/`, PSR-4 against the product's `TEC\`
  namespace: `src/Events/` in TEC, `src/Tickets/` in ET, `src/Common/` in
  common, `src/Events_Pro/` in ECP, `src/Tickets_Plus/` in ETP,
  `src/Filter_Bar/` in FBAR, `src/Events_Community/` in CE, `src/Eventbrite/`
  in EBRT. Match the domain, not the file next door.
- **Legacy** is `src/Tribe/`, `Tribe__*` classes and `src/functions/`. It takes
  bug fixes, not features. When editing it, match the file around you; do not
  refactor it toward SOLID as a side effect. A fix big enough to want the new
  structure becomes a new class in `src/{Domain}/` that the legacy code calls.
- `src/views/`, `src/admin-views/`, `src/resources/` and `src/modules/` still
  hold templates and assets. PHP classes do not go there.

## Structure

- YAGNI decides whether code exists; SOLID decides how what exists is shaped.
  No interface with one implementation, no factory for one product, no layer for
  a second case that does not exist.
- Every domain separates `Provider`, `Hooks` and `Assets`. Follow that split.
- Register in a `Service_Provider` (`TEC\Common\Contracts\Service_Provider`),
  resolve with `tribe( Class::class )`, constructor-inject collaborators. No
  `new` inside business logic, no singletons.
- Prefer a StellarWP library over rewriting the capability: `DB` over raw
  `$wpdb`, `Assets` over `wp_enqueue_*`, `Schema` over `dbDelta`, `Shepherd`
  over `wp_cron`, `AdminNotices` over hand-built notices. They are loaded and
  configured by common under the `TEC\Common\StellarWP\` prefix; the unprefixed
  `StellarWP\` classes do not exist at runtime. Do not re-run a library's
  `Config::` setters from a plugin.
- Anything two or more products need belongs in common, behind an API both
  call, not copied into each.

## Public API and hooks

Filters, actions, hook names, template files, global functions, REST routes and
shortcodes are public API the moment they ship. A site running fifty plugins
cannot tell you it depends on yours.

- New hooks use the `tec_` prefix for the product (`tec_events_`,
  `tec_tickets_`, `tec_common_`, `tec_events_pro_`, `tec_tickets_plus_`,
  `tec_events_filterbar_`). `tribe_` is legacy: use it only inside existing
  legacy code, never in a new name.
- Renaming a hook fires the old name too through `_deprecated_hook()` for at
  least one major cycle. Changing a signature keeps the old function, marked
  with `_deprecated_function()`, forwarding to the new one. Changing a filter's
  value type or an action's arguments is a breaking change even if the name stays.
- Extend through hooks, not by editing the class or copying the template. If a
  behavior needs to change and there is no hook, the change is to add one.

## Common

`tribe-common` is bundled inside both TEC and ET as `common/`. The copy with the
highest version wins at runtime. **Make common changes only in the copy inside
`the-events-calendar`**; that is the only place its suites run. A common change
lands in every product at once, so check callers in both TEC and ET.

## Tests and checks

- A new class ships with tests. A bug fix ships with a regression test that
  fails without the fix: write it first, watch it fail, then fix.
- Prefer integration tests over unit tests. Mock only what cannot be run, and
  say why in a comment.
- Tests are Codeception suites run through [slic](https://github.com/stellarwp/slic),
  never `vendor/bin/codecept` directly. `ls tests/*.suite.dist.yml` lists the
  suites; `tec-slic` says which one a test belongs in. The file mirrors the
  class: `src/Tickets/Foo/Bar.php` becomes `tests/wpunit/Tickets/Foo/Bar_Test.php`.
- Every PR that changes behavior carries a changelog entry from
  `npm run changelog`, committed on the branch.
- Version placeholders in docblocks are the literal `TBD`: `@since TBD`,
  `@deprecated TBD`, `_deprecated_function( __METHOD__, 'TBD' )`. The release
  workflow replaces them with the version being shipped. Never write a real
  version number there, and do not flag `TBD` as an unfinished placeholder.
- `phpcs` runs on the `stellarwp/coding-standards` ruleset. Fix what it reports
  in the lines you touched; a pre-existing violation in a line you did not
  change is not yours to fix in this diff.

## Working agreements

- Do not commit, push, or open a PR unless the human asks in that turn.
- Branches are `{type}/{TICKET-ID}/{short-desc}` with Conventional Commit types.
  Most PRs target a `release/*` or `bucket/*` branch, not the default branch;
  work out the base, do not assume it.
- When a change spans repositories, say so rather than fixing one and leaving
  the siblings broken. Fix at the owner, then check the extenders: ET then ETP,
  TEC then ECP.

## For reviewers

Review against the rules above, and do not raise these:

- `tribe_` names, `Tribe__*` classes or non-SOLID structure inside a legacy
  file, when the change matches the file it is in. Raise them in new code
  under `src/{Domain}/`.
- Refactors of legacy code the PR did not set out to change.
- `readme.txt`, `changelog.md`, `lang/*.pot`, `package-lock.json` and built
  assets. They are generated by the release tooling, not written by hand.
- The synced files: `.github/workflows/release-*.yml`, `lint.yml`, `phpcs.yml`,
  `changelogger.yml`, `link-project.yml`, `openspec-plan.yml`, `bin/*.sh`,
  `.github/pull_request_template.md`, and this file. They are copies; the
  finding belongs in `the-events-calendar/actions`.
- Missing docblocks on test methods, or a test that reaches into WordPress
  globals. Integration tests here are meant to run against a real WordPress.

Do raise: a `tribe_` or `Tribe__` name introduced in new code, a public hook or
function changed without a deprecation path, raw `$wpdb` or `wp_enqueue_*` where
a StellarWP library exists, a behavior change with no test, a common change made
in ET's copy, unescaped output, unsanitized input, and a missing capability or
nonce check on anything a request can reach.
