#!/usr/bin/env bash

# Changelog trimmer for readme.txt files.
# Handles word count limits and section-based trimming for WordPress plugin readme files.

set -euo pipefail

# Default values
MAX_WORDS=5000
READONLY_PATH=""
FULL_CHANGELOG_URL=""

# Function to show usage
usage() {
    echo "Usage: $0 <readme_path> <full_changelog_url> [--max-words <number>]"
    echo ""
    echo "Arguments:"
    echo "  readme_path         Path to readme.txt file"
    echo "  full_changelog_url  URL to full changelog"
    echo ""
    echo "Options:"
    echo "  --max-words <number>  Maximum word count for changelog section (default: 5000)"
    echo ""
    echo "Examples:"
    echo "  $0 readme.txt https://evnt.is/1b5k"
    echo "  $0 readme.txt https://evnt.is/1b5k --max-words 3000"
    exit 1
}

# Function to count words in text, excluding release headers
count_words() {
    local text="$1"
    # Remove release headers and count words
    echo "$text" | sed 's/^= \[.*\] .* =$//g' | wc -w | tr -d ' \t'
}

# Function to extract changelog section from readme.txt
extract_changelog_section() {
    local readme_file="$1"
    local temp_file=$(mktemp)

    # Extract the changelog section using awk
    awk '
    /^== Changelog ==/ {
        in_changelog = 1
        print $0
        next
    }
    in_changelog && /^== / && !/^== Changelog ==/ {
        exit
    }
    in_changelog {
        print $0
    }
    ' "$readme_file" > "$temp_file"

    if [ ! -s "$temp_file" ]; then
        rm -f "$temp_file"
        return 1
    fi

    echo "$temp_file"
    return 0
}

# Function to get release entry positions and metadata
get_release_entries() {
    local changelog_file="$1"
    local temp_entries=$(mktemp)

    # Find all release headers with line numbers
    grep -n "^= \[.*\] .* =$" "$changelog_file" | while IFS=: read -r line_num header; do
        # Extract version and date from header
        version=$(echo "$header" | sed 's/^= \[\([^]]*\)\] .* =$/\1/')
        date=$(echo "$header" | sed 's/^= \[.*\] \(.*\) =$/\1/')
        echo "$line_num|$version|$date"
    done > "$temp_entries"

    echo "$temp_entries"
}

# Function to extract content for a specific release entry
extract_entry_content() {
    local changelog_file="$1"
    local start_line="$2"
    local end_line="$3"

    if [ "$end_line" = "EOF" ]; then
        sed -n "${start_line},\$p" "$changelog_file"
    else
        sed -n "${start_line},$((end_line-1))p" "$changelog_file"
    fi
}

# Function to trim changelog entries to stay within word limit
trim_changelog_entries() {
    local changelog_file="$1"
    local max_words="$2"
    local entries_file="$3"
    local output_file="$4"

    local total_words=0
    local kept_entries=()
    local was_trimmed=false
    local entry_count=0
    local original_count=0

    # Count total entries first
    original_count=$(wc -l < "$entries_file")

    # Read entries and calculate word counts
    while IFS='|' read -r line_num version date; do
        # Determine end line for this entry
        local next_line_num=$(sed -n "$((entry_count + 2))p" "$entries_file" | cut -d'|' -f1)
        local end_line="${next_line_num:-EOF}"

        # Extract content for this entry
        local entry_content=$(extract_entry_content "$changelog_file" "$line_num" "$end_line")
        local entry_words=$(count_words "$entry_content")

        # Check if adding this entry would exceed the limit
        local new_total=$((total_words + entry_words))

        if [ $new_total -le $max_words ]; then
            kept_entries+=("$line_num|$version|$date|$entry_words")
            total_words=$new_total
        else
            was_trimmed=true
            break
        fi

        ((entry_count++))
    done < "$entries_file"

    # Write kept entries to output file
    if [ ${#kept_entries[@]} -gt 0 ]; then
        printf "%s\n" "${kept_entries[@]}" > "$output_file"
    else
        touch "$output_file"
    fi

    # Return statistics
    echo "original_count=$original_count"
    echo "kept_count=${#kept_entries[@]}"
    echo "total_words=$total_words"
    echo "was_trimmed=$was_trimmed"
}

# Function to rebuild changelog content from kept entries
rebuild_changelog() {
    local changelog_file="$1"
    local kept_entries_file="$2"
    local output_file="$3"

    # Start with the header
    echo "== Changelog ==" > "$output_file"
    echo "" >> "$output_file"

    # If no entries to keep, just return
    if [ ! -s "$kept_entries_file" ]; then
        return 0
    fi

    # Create array of all line numbers for proper end detection
    local line_numbers=()
    while IFS='|' read -r line_num version date entry_words; do
        line_numbers+=("$line_num")
    done < "$kept_entries_file"

        # Get all original entry line numbers for boundary detection
    local all_entry_lines=$(grep -n "^= \[.*\] .* =$" "$changelog_file" | cut -d: -f1)
    local all_lines_array=($all_entry_lines)

    # Add each kept entry
    local entry_index=0
    while IFS='|' read -r line_num version date entry_words; do
        # Find the end line for this entry by looking at ALL original entries
        local end_line="EOF"
        for (( i=0; i<${#all_lines_array[@]}; i++ )); do
            if [ "${all_lines_array[$i]}" = "$line_num" ] && [ $((i + 1)) -lt ${#all_lines_array[@]} ]; then
                end_line="${all_lines_array[$((i + 1))]}"
                break
            fi
        done

                # Extract entry content
        local entry_content=$(extract_entry_content "$changelog_file" "$line_num" "$end_line")

        # Add entry content (preserving internal formatting)
        echo "$entry_content" >> "$output_file"

        # Add single empty line only if this is not the last entry
        if [ $((entry_index + 1)) -lt ${#line_numbers[@]} ]; then
            echo "" >> "$output_file"
        fi

        ((entry_index++))
    done < "$kept_entries_file"
}

# Function to add full changelog link
add_full_changelog_link() {
    local content_file="$1"
    local full_changelog_url="$2"

    local link_text="[See changelog for all versions]($full_changelog_url)"

    # Check if the link is already present
    if grep -q "$full_changelog_url" "$content_file"; then
        return 0
    fi

    # Check if file ends with empty line, if not add one
    if [ -s "$content_file" ] && [ "$(tail -c 1 "$content_file")" != "" ]; then
        echo "" >> "$content_file"
    fi

    # Add single empty line before link only if the last line isn't already empty
    if [ -s "$content_file" ] && [ "$(tail -n 1 "$content_file")" != "" ]; then
        echo "" >> "$content_file"
    fi

    # Add the link
    echo "$link_text" >> "$content_file"
}

# Function to replace changelog section in readme.txt
replace_changelog_section() {
    local readme_file="$1"
    local new_changelog_file="$2"
    local output_file="$3"

    # Extract everything before changelog section
    awk '/^== Changelog ==/ {exit} {print}' "$readme_file" > "$output_file"

    # Append the new changelog content
    cat "$new_changelog_file" >> "$output_file"

    # Extract everything after changelog section
    awk '
    found_changelog && /^== / && !/^== Changelog ==/ {
        found_other = 1
    }
    /^== Changelog ==/ {
        found_changelog = 1
    }
    found_other {
        print
    }
    ' "$readme_file" >> "$output_file"
}

# Main processing function
process_readme_changelog() {
    local readme_path="$1"
    local full_changelog_url="$2"
    local max_words="$3"

    # Validate input file
    if [ ! -f "$readme_path" ]; then
        echo "Warning: $readme_path not found" >&2
        return 1
    fi

    echo "Processing readme.txt changelog section for word count limits..."

    # Extract changelog section
    local changelog_temp_file
    if ! changelog_temp_file=$(extract_changelog_section "$readme_path"); then
        echo "Warning: No changelog section found in readme.txt" >&2
        return 1
    fi

    # Get release entries
    local entries_file
    entries_file=$(get_release_entries "$changelog_temp_file")

    if [ ! -s "$entries_file" ]; then
        echo "Warning: No release entries found in changelog section" >&2
        rm -f "$changelog_temp_file" "$entries_file"
        return 1
    fi

    # Trim entries if necessary
    local kept_entries_file=$(mktemp)
    local stats_file=$(mktemp)
    trim_changelog_entries "$changelog_temp_file" "$max_words" "$entries_file" "$kept_entries_file" > "$stats_file"

    # Parse statistics from file
    local original_count=$(grep "^original_count=" "$stats_file" | cut -d'=' -f2)
    local kept_count=$(grep "^kept_count=" "$stats_file" | cut -d'=' -f2)
    local total_words=$(grep "^total_words=" "$stats_file" | cut -d'=' -f2)
    local was_trimmed=$(grep "^was_trimmed=" "$stats_file" | cut -d'=' -f2)

    rm -f "$stats_file"

    # Rebuild changelog content
    local new_changelog_file=$(mktemp)
    rebuild_changelog "$changelog_temp_file" "$kept_entries_file" "$new_changelog_file"

    # Add full changelog link if trimming occurred
    if [ "$was_trimmed" = "true" ]; then
        add_full_changelog_link "$new_changelog_file" "$full_changelog_url"
    fi

    # Only proceed if trimming occurred or the link was added
    if [ "$was_trimmed" = "false" ]; then
        echo "No changes needed to changelog section"
        rm -f "$changelog_temp_file" "$entries_file" "$kept_entries_file" "$new_changelog_file"
        return 0
    fi

    # Create final readme with updated changelog
    local final_readme_file=$(mktemp)
    replace_changelog_section "$readme_path" "$new_changelog_file" "$final_readme_file"

    # Write back to original file
    if cp "$final_readme_file" "$readme_path"; then
        echo "Changelog processing complete:"
        echo "  Original entries: $original_count"
        echo "  Kept entries: $kept_count"
        echo "  Total word count: $total_words"
        echo "  Was trimmed: $was_trimmed"

        # Cleanup
        rm -f "$changelog_temp_file" "$entries_file" "$kept_entries_file" "$new_changelog_file" "$final_readme_file"
        return 0
    else
        echo "Error: Failed to write updated content to $readme_path" >&2
        rm -f "$changelog_temp_file" "$entries_file" "$kept_entries_file" "$new_changelog_file" "$final_readme_file"
        return 1
    fi
}

# Parse command line arguments
if [ $# -lt 2 ]; then
    usage
fi

READONLY_PATH="$1"
FULL_CHANGELOG_URL="$2"
shift 2

# Parse optional arguments
while [ $# -gt 0 ]; do
    case $1 in
        --max-words)
            if [ $# -lt 2 ]; then
                echo "Error: --max-words requires a number" >&2
                exit 1
            fi
            MAX_WORDS="$2"
            shift 2
            ;;
        -h|--help)
            usage
            ;;
        *)
            echo "Error: Unknown argument $1" >&2
            usage
            ;;
    esac
done

# Validate max-words is a positive integer
if ! [[ "$MAX_WORDS" =~ ^[0-9]+$ ]] || [ "$MAX_WORDS" -le 0 ]; then
    echo "Error: --max-words must be a positive integer" >&2
    exit 1
fi

# Run the main processing
if process_readme_changelog "$READONLY_PATH" "$FULL_CHANGELOG_URL" "$MAX_WORDS"; then
    exit 0
else
    exit 1
fi
