#!/usr/bin/env bash
#
# php-compat.sh — scan this plugin for PHP 8.0–8.4 compatibility issues.
#
# Self-contained: downloads a pinned PHP_CodeSniffer phar plus the
# PHPCompatibility + PHPCSUtils standards (develop branch — required for the
# PHP 8.1–8.4 sniffs, which are not in any tagged release yet), along with the
# PHPCompatibilityWP + PHPCompatibilityParagonie standards so that functions
# WordPress polyfills (str_starts_with, str_contains, array_key_first, …) are
# NOT flagged as missing on older PHP. Everything lands in the git-ignored
# dev/php-compat/ cache and is never committed. This does NOT touch the
# dependency tree, so it never conflicts with the WPCS / phpcs 3.x stack used
# for coding-standards linting.
#
# Usage (prefer the composer wrapper):
#   composer compat                          # scan the paths in phpcompat.xml.dist
#   composer compat -- --report=summary      # extra args after -- go to phpcs
#   composer compat -- src/Tribe/Foo.php     # scan a specific path
# or invoke directly:
#   bin/php-compat.sh [phpcs args...]
#
# Env overrides:
#   PHP_BIN             php binary to use (default: php)
#   PHPCS_VERSION       PHP_CodeSniffer release (default below)
#   PHPCOMPAT_REF       PHPCompatibility git ref/sha (default below)
#   PHPCSUTILS_REF      PHPCSUtils git ref/sha (default below)
#   PHPCOMPATWP_REF     PHPCompatibilityWP git ref/sha (default below)
#   PHPCOMPATPARA_REF   PHPCompatibilityParagonie git ref/sha (default below)
#
set -euo pipefail

# --- pinned versions (override via env) -------------------------------------
PHPCS_VERSION="${PHPCS_VERSION:-4.0.1}"
PHPCOMPAT_REF="${PHPCOMPAT_REF:-d9a91bdf66d39fbd5c22272a592c8b63a1d0954f}"
PHPCSUTILS_REF="${PHPCSUTILS_REF:-69b7d58f7284fa61e4aeeceeb6fae7d89d9f0c8a}"
PHPCOMPATWP_REF="${PHPCOMPATWP_REF:-0c3d688ebd61feaaf1e31759b815304a1f4ed57a}"
PHPCOMPATPARA_REF="${PHPCOMPATPARA_REF:-6eabdaf544d203454627928b9b0358b43f26dce4}"
PHP_BIN="${PHP_BIN:-php}"

# --- paths ------------------------------------------------------------------
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
REPO_ROOT="$( cd "${SCRIPT_DIR}/.." && pwd )"
CACHE_DIR="${REPO_ROOT}/dev/php-compat"
STANDARD="${REPO_ROOT}/phpcompat.xml.dist"

PHPCS_PHAR="${CACHE_DIR}/phpcs-${PHPCS_VERSION}.phar"
COMPAT_DIR="${CACHE_DIR}/PHPCompatibility-${PHPCOMPAT_REF}"
UTILS_DIR="${CACHE_DIR}/PHPCSUtils-${PHPCSUTILS_REF}"
WP_DIR="${CACHE_DIR}/PHPCompatibilityWP-${PHPCOMPATWP_REF}"
PARA_DIR="${CACHE_DIR}/PHPCompatibilityParagonie-${PHPCOMPATPARA_REF}"

mkdir -p "${CACHE_DIR}"
# Make the cache self-ignoring so it never needs a root .gitignore entry.
[ -f "${CACHE_DIR}/.gitignore" ] || printf '*\n' > "${CACHE_DIR}/.gitignore"

fetch() { # url dest
  echo "  fetching $(basename "$2") ..."
  curl -fsSL -o "$2" "$1"
}

fetch_standard() { # owner repo ref dest
  local owner="$1" repo="$2" ref="$3" dest="$4"
  [ -d "${dest}" ] && return 0
  local tgz="${CACHE_DIR}/${repo}-${ref}.tgz"
  fetch "https://github.com/${owner}/${repo}/archive/${ref}.tar.gz" "${tgz}"
  tar xzf "${tgz}" -C "${CACHE_DIR}"
  rm -f "${tgz}"
}

echo "PHP 8 compatibility scan (WordPress polyfill-aware)"
echo "  phpcs ${PHPCS_VERSION} | PHPCompatibility ${PHPCOMPAT_REF:0:8} | WP ${PHPCOMPATWP_REF:0:8}"

[ -f "${PHPCS_PHAR}" ] || fetch \
  "https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${PHPCS_VERSION}/phpcs.phar" \
  "${PHPCS_PHAR}"
fetch_standard PHPCompatibility PHPCompatibility          "${PHPCOMPAT_REF}"     "${COMPAT_DIR}"
fetch_standard PHPCSStandards   PHPCSUtils                "${PHPCSUTILS_REF}"    "${UTILS_DIR}"
fetch_standard PHPCompatibility PHPCompatibilityParagonie "${PHPCOMPATPARA_REF}" "${PARA_DIR}"
fetch_standard PHPCompatibility PHPCompatibilityWP        "${PHPCOMPATWP_REF}"   "${WP_DIR}"

echo "  scanning ..."
exec "${PHP_BIN}" "${PHPCS_PHAR}" -p \
  --runtime-set installed_paths "${COMPAT_DIR},${UTILS_DIR},${PARA_DIR},${WP_DIR}" \
  --standard="${STANDARD}" \
  "$@"
