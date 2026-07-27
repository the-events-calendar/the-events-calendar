#!/usr/bin/env bash

# npm/@stellarwp/changelogger variant of check-changelog.sh.

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

echo "Validating changelog files..."
npx @stellarwp/changelogger validate
