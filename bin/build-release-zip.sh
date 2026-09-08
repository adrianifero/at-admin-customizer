#!/usr/bin/env bash
# Build a WordPress.org-compatible zip: top-level folder must be customize-admin-dashboard.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

VERSION="$(grep -E '^\s*\* Version:' customize-admin-dashboard.php | head -1 | awk '{print $3}')"
SLUG="customize-admin-dashboard"
STAGE="$(mktemp -d)"
OUT="${ROOT}/dist/${SLUG}-${VERSION}.zip"
TEST_OUT="${ROOT}/test-builds/${SLUG}-${VERSION}.zip"
rm -f "$OUT" "$TEST_OUT"

mkdir -p "${ROOT}/dist" "${ROOT}/test-builds" \
  "${STAGE}/${SLUG}/includes" \
  "${STAGE}/${SLUG}/assets/css" \
  "${STAGE}/${SLUG}/assets/js"

cp customize-admin-dashboard.php readme.txt "${STAGE}/${SLUG}/"
cp includes/*.php "${STAGE}/${SLUG}/includes/"
cp assets/css/*.css "${STAGE}/${SLUG}/assets/css/"
cp assets/js/*.js "${STAGE}/${SLUG}/assets/js/"

(
  cd "$STAGE"
  zip -r "$OUT" "$SLUG" -x '*/.DS_Store' '*/.git/*'
)

cp "$OUT" "$TEST_OUT"
rm -rf "$STAGE"

if ! unzip -p "$OUT" "${SLUG}/assets/js/color-panel.js" | grep -q 'cadColorPanel'; then
	echo "ERROR: zip JS missing color panel" >&2
	exit 1
fi

if ! unzip -p "$OUT" "${SLUG}/includes/class-brand-colors.php" | grep -q 'cad_brand_colors'; then
	echo "ERROR: zip PHP missing brand colors module" >&2
	exit 1
fi

echo "Wrote $OUT"
echo "Test copy: $TEST_OUT"
unzip -l "$OUT"
