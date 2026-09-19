#!/usr/bin/env bash

# Uses @stellarwp/changelogger (npm) instead of jetpack-changelogger (composer).
#
# @stellarwp/changelogger's `write` command merges new entries into an existing version
# section (matched by header) instead of duplicating the header, and it updates every
# configured output file (changelog.md, readme.txt) in one pass. That means the old
# jetpack-changelogger "generate" vs "amend" distinction - including the separate perl-based
# readme.txt re-merge - collapses into a single `write` call here. "amend-version" (relabeling
# an existing header's placeholder date when there are no new pending entries left to write) is
# unchanged, since `write` intentionally no-ops when there are no pending changelog/*.yaml files.

RELEASE_VERSION=${1-}
CURRENT_VERSION=${2-}
ACTION_TYPE=${3-generate}
RELEASE_DATE=${4-today}

if [[ "$OSTYPE" == "darwin"* ]]; then
  # macOS with gdate
  RELEASE_DATE=$( gdate "+%Y-%m-%d" -d "$RELEASE_DATE" )
else
  # Linux
  RELEASE_DATE=$( date "+%Y-%m-%d" -d "$RELEASE_DATE" )
fi

sed_compatible() {
    if [[ "$1" == "-r" ]]; then
        # Remove the -r argument
        shift
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS with -E flag
            sed -i '' -E "$@"
        else
            # Linux with -r flag
            sed -i -r "$@"
        fi
    else
        # No -r argument, regular sed command
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i '' "$@"
        else
            sed -i "$@"
        fi
    fi
}

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

cd $SCRIPT_DIR/../

echo "RELEASE_DATE=$RELEASE_DATE"

if [ "$ACTION_TYPE" == "amend-version" ]; then
	sed_compatible "s/^### \[$CURRENT_VERSION\] .*$/### [$RELEASE_VERSION] $RELEASE_DATE/" changelog.md
	sed_compatible "s/^= \[$CURRENT_VERSION\] .* =$/= [$RELEASE_VERSION] $RELEASE_DATE =/" readme.txt
else
	npx @stellarwp/changelogger write --overwrite-version "$RELEASE_VERSION" --date "$RELEASE_DATE"
fi

CHANGELOG=$(awk '/^### / { if (p) { exit }; p=1; next } p && NF' changelog.md)

# Escape backslash, new line and ampersand characters. The order is important.
CHANGELOG=${CHANGELOG//\\/\\\\}
CHANGELOG=${CHANGELOG//$'\n'/\\n}
CHANGELOG=${CHANGELOG//&/\\&}

echo "CHANGELOG=$CHANGELOG"
