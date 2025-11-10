#!/bin/bash
##
## Local Testing Script for Beta Tender Module
##
## This script runs similar checks to the GitHub Actions workflows
## so you can test locally before pushing.
##

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODULE_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║     Beta Tender Module - Local Test Script               ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

cd "$MODULE_ROOT"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

# Function to print status
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ PASSED${NC}"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}❌ FAILED${NC}"
        FAILED=$((FAILED + 1))
    fi
}

print_warning() {
    echo -e "${YELLOW}⚠️  WARNING${NC}"
    WARNINGS=$((WARNINGS + 1))
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. PHP Syntax Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

PHP_ERRORS=0
while IFS= read -r -d '' file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo "  ❌ Syntax error in: $file"
        php -l "$file"
        PHP_ERRORS=$((PHP_ERRORS + 1))
    fi
done < <(find . -name "*.php" -type f -print0)

if [ $PHP_ERRORS -eq 0 ]; then
    echo "  All PHP files passed syntax check"
    print_status 0
else
    echo "  Found $PHP_ERRORS file(s) with syntax errors"
    print_status 1
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2. YAML Validation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if command -v yamllint &> /dev/null; then
    if yamllint -d relaxed $(find . -name "*.yml" -type f) 2>&1; then
        print_status 0
    else
        print_warning
    fi
else
    echo "  ⚠️  yamllint not installed (pip install yamllint)"
    print_warning
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3. Drupal Coding Standards (PHPCS)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if command -v phpcs &> /dev/null; then
    echo "  Running PHPCS with Drupal standard..."
    if phpcs --standard=Drupal --extensions=php src/ 2>&1; then
        print_status 0
    else
        print_warning
        echo "  Run 'phpcbf --standard=Drupal src/' to auto-fix some issues"
    fi
else
    echo "  ⚠️  PHPCS not installed"
    echo "     Install with: composer global require drupal/coder"
    echo "     Configure with: phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer"
    print_warning
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4. Check for Common Issues"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check for debug statements
echo "  Checking for debug statements..."
if grep -r "var_dump\|print_r\|dd(" --include="*.php" src/ 2>/dev/null; then
    echo "  ⚠️  Debug statements found - please remove before merge"
    print_warning
else
    echo "  No debug statements found"
    print_status 0
fi

# Check for TODO/FIXME
echo "  Checking for TODO/FIXME comments..."
TODO_COUNT=$(grep -r "TODO\|FIXME" --include="*.php" src/ 2>/dev/null | wc -l)
if [ $TODO_COUNT -gt 0 ]; then
    echo "  ⚠️  Found $TODO_COUNT TODO/FIXME comment(s)"
    grep -r "TODO\|FIXME" --include="*.php" src/ 2>/dev/null | head -5
    print_warning
else
    echo "  No TODO/FIXME comments found"
    print_status 0
fi

# Check for executable PHP files
echo "  Checking file permissions..."
EXEC_FILES=$(find . -type f -name "*.php" -executable 2>/dev/null | wc -l)
if [ $EXEC_FILES -gt 0 ]; then
    echo "  ⚠️  Found $EXEC_FILES executable PHP file(s)"
    find . -type f -name "*.php" -executable 2>/dev/null
    print_warning
else
    echo "  No executable PHP files found"
    print_status 0
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5. File Structure Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

REQUIRED_FILES=(
    "beta_tender.info.yml"
    "beta_tender.module"
    "beta_tender.routing.yml"
    "beta_tender.services.yml"
    "README.md"
    "composer.json"
)

MISSING_FILES=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "  ❌ Missing required file: $file"
        MISSING_FILES=$((MISSING_FILES + 1))
    fi
done

if [ $MISSING_FILES -eq 0 ]; then
    echo "  All required files present"
    print_status 0
else
    echo "  Missing $MISSING_FILES required file(s)"
    print_status 1
fi

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                    Test Summary                           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo -e "  ${GREEN}✅ Passed:${NC} $PASSED"
echo -e "  ${RED}❌ Failed:${NC} $FAILED"
echo -e "  ${YELLOW}⚠️  Warnings:${NC} $WARNINGS"
echo ""

if [ $FAILED -gt 0 ]; then
    echo "❌ Some tests failed. Please fix the issues before pushing."
    exit 1
elif [ $WARNINGS -gt 0 ]; then
    echo "⚠️  Tests passed with warnings. Review warnings before pushing."
    exit 0
else
    echo "✅ All tests passed! Ready to push."
    exit 0
fi
