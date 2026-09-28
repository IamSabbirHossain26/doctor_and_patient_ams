#!/usr/bin/env bash
# DPAMS smoke test: login as each role and check pages return 200.
BASE="http://dpams.local"
PASS=0
FAIL=0

check() { # $1 = label, $2 = expected code, $3 = actual code
    if [ "$2" = "$3" ]; then
        PASS=$((PASS+1)); echo "  OK   [$3] $1"
    else
        FAIL=$((FAIL+1)); echo "  FAIL [$3, expected $2] $1"
    fi
}

login_and_check() {
    local label="$1"; local email="$2"; shift 2
    local jar="/tmp/dpams_$label.jar"
    rm -f "$jar"

    # Get CSRF token + cookie
    local login_page
    login_page=$(curl -s -c "$jar" "$BASE/login" --max-time 15)
    local token
    token=$(echo "$login_page" | grep -o 'name="csrf_test_name" value="[^"]*"' | sed 's/.*value="//; s/"//')

    # POST login (CI uses 303 See Other for redirect-after-POST)
    local code
    code=$(curl -s -o /dev/null -w "%{http_code}" -b "$jar" -c "$jar" \
        -d "csrf_test_name=$token" -d "email=$email" -d "password=password123" \
        "$BASE/login" --max-time 15)
    check "login POST $email (303 expected)" 303 "$code"

    # Follow to dashboard
    code=$(curl -s -o /dev/null -w "%{http_code}" -b "$jar" -L "$BASE/dashboard" --max-time 15)
    check "  dashboard" 200 "$code"

    # Check provided pages
    for page in "$@"; do
        code=$(curl -s -o /dev/null -w "%{http_code}" -b "$jar" "$BASE$page" --max-time 15)
        check "  $page" 200 "$code"
    done
}

# Public pages
for p in "/" "/about" "/login" "/register" "/doctors" "/doctors/view/1"; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE$p" --max-time 15)
    check "public $p" 200 "$code"
done

# Guest redirected from protected page
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/appointments" --max-time 15)
check "guest /appointments redirects (302)" 302 "$code"

# Manager
login_and_check manager manager@dpams.test \
    /doctors/create /doctors/edit/1 /patients /patients/create /patients/view/1 \
    /patients/edit/1 /appointments /appointments/book /appointments/view/1 \
    /reports /reports/upload /reports/view/1 /staff /staff/create /staff/edit/1

# Nurse
login_and_check nurse nurse@dpams.test \
    /patients /patients/create /appointments /appointments/book \
    /appointments/view/2 /reports /reports/upload /reports/view/2

# Doctor
login_and_check doctor doctor@dpams.test \
    /appointments /appointments/view/1 /reports /patients /doctors

# Patient
login_and_check patient patient@dpams.test \
    /appointments /appointments/book /appointments/view/4 /reports /reports/view/1 \
    /my-profile /doctors /doctors/view/2

# Patient must NOT access staff (role filter must redirect to /dashboard)
jar="/tmp/dpams_patient.jar"
loc=$(curl -s -o /dev/null -w "%{redirect_url}" -b "$jar" "$BASE/staff" --max-time 15)
if echo "$loc" | grep -q "/dashboard"; then
    PASS=$((PASS+1)); echo "  OK   [302 -> $loc] patient /staff denied"
else
    FAIL=$((FAIL+1)); echo "  FAIL [redirect: $loc] patient /staff denied"
fi

echo
echo "PASS=$PASS FAIL=$FAIL"
