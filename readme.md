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
[`tec-openspec` skill](https://github.com/the-events-calendar/skills) covers the
workflow itself — writing a proposal worth reviewing, keeping it current, and
archiving it once (after the last repository merges, not per repo). Install it with:

```
/plugin marketplace add the-events-calendar/skills
/plugin install tec
```
