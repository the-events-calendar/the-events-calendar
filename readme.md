# The Events Calendar

Welcome to the official git repository for the WordPress plugin [The Events Calendar](http://wordpress.org/plugins/the-events-calendar/).

Please feel free to fork this repository and submit pull requests!

-----

We do have some guidelines that you should keep in mind while working with this repository:

1. **We have an internal release cycle and review pull requests once per cycle.** Unfortunately we can't guarantee a regular schedule for our review process, but please know that while it may take 1-2 months (at the most) before we review your request, we WILL review it.

2. **Please submit a detailed description with your pull request.** If we can't figure out what you're submitting, then we may close your request.

3. **We do not accept or handle support issues or bug reports here.** You may notice that we have disabled the Issues tab on this GitHub project. Please submit bug reports at [WordPress.org](http://wordpress.org/support/plugin/the-events-calendar). If you have purchased any of our premium add-ons, you may create an account and submit support requests at our [Premium Support Forums](http://evnt.is/kj).

4. **Please fork `main` and submit your pull requests against that branch.** This is the branch with the latest code we are working on. Pull requests will be re-targeted against the branch for the next appropriate release before merged.

5. **When developing apply `define('SCRIPT_DEBUG', true)` in your wp-config.php**. This will load unminified versions of our js assets and give you some debugging information in your dev console.

6. **Make yourself familiar with the dev folder, its readme and run the appropriate Grunt tasks before committing**. Js minification, css minification, debug code removal, linting and more should be performed as you develop. Its all done with the grunt tasks defined in the dev folder.

7. **Lastly: We don't recommend using the develop branch on production sites!** You’re welcome to give it a shot, but it is totally unrecommended - and we cannot provide any support if things go wrong. You’re on your own if you run develop code on your site.

That's all! please have fun and code responsibly :)

## Further Information

* **Official Release**: https://wordpress.org/plugins/the-events-calendar/
* **Readme**: https://github.com/the-events-calendar/the-events-calendar/blob/main/readme.txt
* **Website**: https://theeventscalendar.com/product/wordpress-events-calendar/
* **Support**: https://theeventscalendar.com/support
* **Documentation**: https://theeventscalendar.com/functions/

## Specs and planning

Work on this repository is planned before it is written, using
[OpenSpec](https://github.com/Fission-AI/OpenSpec). **This is enforced** — every
pull request is checked for a plan.

Plans do not live here. A TEC feature routinely spans several repositories, so a
spec kept in one of them is invisible from the others. They all live in one shared
store instead: [`the-events-calendar/plans`](https://github.com/the-events-calendar/plans).

### First time only

```bash
npm install -g @fission-ai/openspec
git clone git@github.com:the-events-calendar/plans.git ~/repos/tec-plans
openspec store register ~/repos/tec-plans --id tec-plans
```

The clone path is yours to choose. The `--id` is not — every command refers to the
store as `tec-plans`.

### Working on a ticket

1. **Create the change before writing code**, named after the ticket:
   `openspec new change SOFT-1234 --store tec-plans --description "what this does"`
2. Write the proposal, then commit and push it in the plans repo.
3. Branch as usual: `feat/SOFT-1234/short-desc`.
4. Implement. If the work shows the plan was wrong — it often does — update the
   change rather than letting the plan and the code drift apart.
5. Open the PR. The template asks for the change ID, and CI checks the plan exists
   and is still active.

Every `openspec` command takes `--store tec-plans`. There is no default and no
repo-side link, so omitting it writes the change into whatever repository you
happen to be standing in.

### Where the rest is written down

This section covers what is specific to working here. The
[`tec-openspec` skill](https://github.com/stellarwp/skills) covers the
workflow itself — writing a proposal worth reviewing, keeping it current, and
archiving it once (after the last repository merges, not per repo). Install it with:

The below will work only once the `stellarwp/skills` becomes public.

```
/plugin marketplace add stellarwp/skills
/plugin install nexcess
```

## Running the tests

PHP tests are Codeception + wp-browser suites run inside Docker by [slic](https://github.com/stellarwp/slic), exactly as CI does.

### Setup

`common` is a git submodule of this plugin, but slic treats `the-events-calendar/common` as its own target: it gets its own `composer install`. Run every `slic` command from the **parent directory** that contains `the-events-calendar/`.

```bash
# 1. Clone the plugin with its common submodule, and slic beside it.
cd /path/to/plugins           # parent dir; slic will scan it for targets
git clone --recursive git@github.com:the-events-calendar/the-events-calendar.git
git clone git@github.com:stellarwp/slic.git slic
# already cloned without --recursive?
# (cd the-events-calendar && git submodule update --init --recursive)

# 2. Point slic at this directory and quiet the prompts (CI's exact flags).
export SLIC_BIN="$PWD/slic/slic"
${SLIC_BIN} here
${SLIC_BIN} interactive off
${SLIC_BIN} build-prompt off
${SLIC_BIN} build-subdir off
${SLIC_BIN} xdebug off
${SLIC_BIN} info

# 3. Install dependencies for common FIRST, then the plugin.
${SLIC_BIN} use the-events-calendar/common
${SLIC_BIN} composer install --no-dev
${SLIC_BIN} use the-events-calendar
${SLIC_BIN} composer install

# 4. Start the containers.
${SLIC_BIN} up wordpress
${SLIC_BIN} up chrome                 # only needed by views_ui (WPWebDriver)
${SLIC_BIN} wp theme install twentytwenty --activate
```

Two suites need an extra plugin in the WordPress container:

```bash
${SLIC_BIN} wp plugin install elementor        # elementor_integration
${SLIC_BIN} wp plugin install wordpress-seo    # integrations_plugin_wordpress_seo
```

### Running a suite

```bash
${SLIC_BIN} use the-events-calendar
${SLIC_BIN} run integration

# a single file, or a single method
${SLIC_BIN} run wpunit tests/wpunit/Tribe/Events/Event_Test.php
${SLIC_BIN} run wpunit tests/wpunit/Tribe/Events/Event_Test.php:it_creates_an_event

# filter by name across the suite
${SLIC_BIN} run views_integration --filter=month
```

Do not run all suites in one `codecept run`; WordPress globals leak between them.

### Suites

| Suite | Covers | CI |
|---|---|---|
| `aggregatorv1` | Event Aggregator v1 REST endpoints | every PR |
| `blocks_editor_integration` | Block editor / Gutenberg blocks | every PR |
| `ct1_integration` | Custom Tables v1 schema and queries | every PR |
| `ct1_migration` | CT1 migration from the legacy meta storage | every PR |
| `ct1_multisite_integration` | CT1 under multisite | every PR |
| `ct1_wp_json_api` | WP REST API with CT1 enabled | every PR |
| `deprecated` | Deprecated functions and classes still resolve | every PR |
| `elementor_integration` | Elementor widgets (needs `elementor`) | every PR (separate workflow) |
| `embed_calendar_integration` | Calendar embed shortcode/block | every PR |
| `event_status` | Event status (cancelled/postponed) | every PR |
| `features` | Feature-flag layer | no |
| `integration` | General plugin integration | every PR |
| `integration_category_colors` | Category colors feature | every PR |
| `integrations_plugin_wordpress_seo` | Yoast SEO compat (needs `wordpress-seo`, WP 6.9) | every PR (separate workflow) |
| `muintegration` | Multisite integration | every PR |
| `rest_tec_v1_integration` | `tec/v1` REST API | every PR (+ OpenAPI lint) |
| `restv1` | Legacy `tribe/events/v1` REST API | every PR |
| `rewrite_functional` | Permalinks and rewrite rules | every PR |
| `views_integration` | Views v2 rendering | every PR |
| `views_rest` | Views v2 REST responses | no — commented out of the matrix ("weird error with RBE changes") |
| `views_settings` | Views v2 settings | every PR |
| `views_ui` | Views v2 browser tests (needs Chrome container) | every PR |
| `views_v2_customizer_integration` | Views v2 Customizer styles | every PR |
| `views_widgets` | Views v2 widgets | every PR |
| `views_wpunit` | Views v2 unit-level | every PR |
| `wp_json_api` | Core WP REST API for TEC post types | every PR |
| `wpml_integration` | WPML compat | no |
| `wpunit` | Plugin unit tests | every PR |

All three workflows are gated on a PHP-file-change check, so a docs-only PR runs none of them.

### How this differs from CI

- CI pins WordPress with `wp core update --force --version=6.8` (6.9 for the SEO suite). Locally the container's bundled version is usually fine; add the same `wp core update` if you need to reproduce a version-specific failure.
- CI appends `--ext DotReporter` for compact logs; skip it locally for readable output.
- CI's ssh-agent, composer cache and `docker network prune -f` steps are runner housekeeping with no local equivalent.
- After `rest_tec_v1_integration`, CI also runs `npm ci` and `npm run spectral -- http://localhost:8888/wp-json/tec/v1/docs/` to lint the OpenAPI doc. To reproduce: `${SLIC_BIN} wp plugin activate the-events-calendar && ${SLIC_BIN} wp rewrite structure '/%postname%/' --hard`, then run those npm commands on the host.
