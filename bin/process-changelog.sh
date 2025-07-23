#!/usr/bin/env bash

RELEASE_VERSION=${1-}
CURRENT_VERSION=${2-}
ACTION_TYPE=${3-generate}
RELEASE_DATE=${4-today}
CHANGELOG_FULL_URL=${5-https://evnt.is/1b5k}

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

# Check for changelog URL in package.json first, then fall back to parameter, then default
if [ -f "package.json" ] && command -v jq >/dev/null 2>&1; then
	PACKAGE_CHANGELOG_URL=$(jq -r '.tec.changelog_url // empty' package.json 2>/dev/null)
	if [ -n "$PACKAGE_CHANGELOG_URL" ] && [ "$PACKAGE_CHANGELOG_URL" != "null" ]; then
		CHANGELOG_FULL_URL="$PACKAGE_CHANGELOG_URL"
		echo "Using changelog URL from package.json: $CHANGELOG_FULL_URL"
	fi
fi

echo "RELEASE_DATE=$RELEASE_DATE"
echo "CHANGELOG_FULL_URL=$CHANGELOG_FULL_URL"

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

# Process readme.txt
if [ "$ACTION_TYPE" == "amend-version" ]; then
	sed_compatible "s/^= \[$CURRENT_VERSION\] .* =$/= [$RELEASE_VERSION] $RELEASE_DATE =/" readme.txt
else
	if [ "$ACTION_TYPE" == "amend" ]; then
	perl -i -p0e "s/= \[$RELEASE_VERSION\].*? =(.*?)(\n){2}(?==)//s" readme.txt # Delete the existing changelog for the release version first
	fi

	# Add new changelog entry to readme.txt (prepended to changelog section)
	sed_compatible -r "s|(== Changelog ==)|\1\n\n= [$RELEASE_VERSION] $RELEASE_DATE =\n\n$CHANGELOG|" readme.txt
fi

# Apply word count checking and trimming to readme.txt (but NOT to changelog.md)
if [ -f "readme.txt" ]; then
	echo "Processing readme.txt changelog section for word count limits..."

	# Make the Bash script executable
	chmod +x "$SCRIPT_DIR/trim-readme-changelog.sh"

	# Run the Bash trimming script
	if "$SCRIPT_DIR/trim-readme-changelog.sh" "readme.txt" "$CHANGELOG_FULL_URL" --max-words 5000; then
		echo "readme.txt changelog processing completed successfully"
	else
		echo "Warning: readme.txt changelog processing encountered issues but continuing..."
	fi
else
	echo "Warning: readme.txt not found, skipping changelog trimming"
fi
