#!/usr/bin/env bash
# Build a WordPress.org-compatible zip: top-level folder must be at-admin-customizer.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

VERSION="$(grep -E '^\s*\* Version:' at-admin-customizer.php | head -1 | awk '{print $3}')"
SLUG="at-admin-customizer"
STAGE="$(mktemp -d)"
OUT="${ROOT}/dist/${SLUG}-${VERSION}.zip"
SUBMISSION="${ROOT}/submission/${SLUG}-${VERSION}.zip"
rm -f "$OUT" "$SUBMISSION"

mkdir -p "${ROOT}/dist" "${ROOT}/submission" \
  "${STAGE}/${SLUG}/includes" \
  "${STAGE}/${SLUG}/assets/css" \
  "${STAGE}/${SLUG}/assets/js"

cp at-admin-customizer.php readme.txt "${STAGE}/${SLUG}/"
cp includes/*.php "${STAGE}/${SLUG}/includes/"
cp assets/css/*.css "${STAGE}/${SLUG}/assets/css/"
cp assets/js/*.js "${STAGE}/${SLUG}/assets/js/"

(
  cd "$STAGE"
  zip -r "$OUT" "$SLUG" -x '*/.DS_Store' '*/.git/*'
)

cp "$OUT" "$SUBMISSION"
rm -rf "$STAGE"

if ! unzip -p "$OUT" "${SLUG}/assets/js/color-panel.js" | grep -q 'cadColorPanel'; then
	echo "ERROR: zip JS missing color panel" >&2
	exit 1
fi

if ! unzip -p "$OUT" "${SLUG}/includes/class-brand-colors.php" | grep -q 'cad_brand_colors'; then
	echo "ERROR: zip PHP missing brand colors module" >&2
	exit 1
fi

if ! unzip -p "$OUT" "${SLUG}/at-admin-customizer.php" | grep -q 'Plugin Name: AT Admin Customizer'; then
	echo "ERROR: zip main file missing AT Admin Customizer header" >&2
	exit 1
fi

echo "Wrote $OUT"
echo "Submission copy: $SUBMISSION"
unzip -l "$OUT"
