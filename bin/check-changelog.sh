#!/usr/bin/env bash

# Uses @stellarwp/changelogger (npm) instead of jetpack-changelogger (composer).

BASE=${1-origin/main}
HEAD=${2-HEAD}

# Get only added files from git diff.
CHANGELOG_FILES=$(git diff --name-only --diff-filter=A "$BASE" "$HEAD" | grep '^changelog\/')

if [[ -n "$CHANGELOG_FILES" ]]; then
	echo "Found changelog file(s):"
	echo "$CHANGELOG_FILES"
else
	echo "::error::No changelog found."
	echo "Add at least one changelog file for your PR by running: npm run changelog"
	echo "Choose *patch* to leave it empty if the change is not significant. You can add multiple changelog files in one PR by running this command a few times."
	echo "Remove changelog in readme.txt and changelog.md if you have already added them in your PR."
	exit 1
fi

# Both `changelogger validate` and `changelogger write` skip anything that is not a *.yaml file, so a
# leftover jetpack-changelogger entry passes every check and is then dropped from the release without a
# word. Scan the whole directory, not just this PR's additions, so a stale file cannot survive merges.
STRAY_FILES=$(find changelog -maxdepth 1 -type f ! -name '*.yaml' ! -name '.gitkeep' | sort)

if [[ -n "$STRAY_FILES" ]]; then
	echo "::error::Unsupported changelog file(s) found."
	echo "$STRAY_FILES"
	echo "Only changelog/*.yaml entries are processed at release. Re-create them by running: npm run changelog"
	exit 1
fi

# The release version-bump workflow find-and-replaces on "version", which also matches
# changelogger's type label further down the same file. Catch a corrupted label here
# rather than in a shipped changelog.
TYPE_LABEL=$(jq -r '.changelogger.types.version // empty' package.json 2>/dev/null)

if [[ -n "$TYPE_LABEL" && "$TYPE_LABEL" != "Version" ]]; then
	echo "::error::changelogger types.version in package.json must be \"Version\", found \"$TYPE_LABEL\"."
	echo "It was most likely overwritten by the release version bump. Restore the label."
	exit 1
fi

echo "Validating changelog files..."
npx @stellarwp/changelogger validate
