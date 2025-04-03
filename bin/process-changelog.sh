#!/usr/bin/env bash

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
else
	if [ "$ACTION_TYPE" == "generate" ]; then
		CHANGELOG_FLAG=""
		echo "Generating the changelog entries."
	else
		CHANGELOG_FLAG="--amend"
		echo "Amending the changelog entries."
	fi

	# Run changelogger through the project's base dir.
	./vendor/bin/changelogger write --use-version="$RELEASE_VERSION" --release-date="$RELEASE_DATE" $CHANGELOG_FLAG --no-interaction --yes
fi

CHANGELOG=$(awk '/^### / { if (p) { exit }; p=1; next } p && NF' changelog.md)

# Escape backslash, new line and ampersand characters. The order is important.
CHANGELOG=${CHANGELOG//\\/\\\\}
CHANGELOG=${CHANGELOG//$'\n'/\\n}
CHANGELOG=${CHANGELOG//&/\\&}

echo "CHANGELOG=$CHANGELOG"

if [ "$ACTION_TYPE" == "amend-version" ]; then
	sed_compatible "s/^= \[$CURRENT_VERSION\] .* =$/= [$RELEASE_VERSION] $RELEASE_DATE =/" readme.txt
else
	if [ "$ACTION_TYPE" == "amend" ]; then
	perl -i -p0e "s/= \[$RELEASE_VERSION\].*? =(.*?)(\n){2}(?==)//s" readme.txt # Delete the existing changelog for the release version first
	fi

	sed_compatible -r "s|(== Changelog ==)|\1\n\n= [$RELEASE_VERSION] $RELEASE_DATE =\n\n$CHANGELOG|" readme.txt
fi
