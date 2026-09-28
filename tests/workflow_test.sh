#!/usr/bin/env bash
# DPAMS workflow tests: real POST actions for every module.
BASE="http://dpams.local"
PASS=0; FAIL=0

check() {
    if [ "$2" = "$3" ]; then PASS=$((PASS+1)); echo "  OK   $1"; else FAIL=$((FAIL+1)); echo "  FAIL $1 (got: $3)"; fi
}

csrf_of() { # $1 = jar, $2 = page url that renders a form
    local jar="$1" page="$2" tok
    tok=$(curl -s -b "$jar" -c "$jar" "$BASE$page" --max-time 15 \
        | grep -o 'name="csrf_test_name" value="[^"]*"' | head -1 | sed 's/.*value="//; s/"//')
    if [ -z "$tok" ]; then # guests get redirected to /login, which has a form
        tok=$(curl -s -b "$jar" -c "$jar" "$BASE/login" --max-time 15 \
            | grep -o 'name="csrf_test_name" value="[^"]*"' | head -1 | sed 's/.*value="//; s/"//')
    fi
    echo "$tok"
}

form_page_for() { # $1 = POST url -> GET page holding the form with the CSRF token
    case "$1" in
        /doctors/store)        echo "/doctors/create" ;;
        /patients/store)       echo "/patients/create" ;;
        /staff/store)          echo "/staff/create" ;;
        /reports/store)        echo "/reports/upload" ;;
        /appointments/store)   echo "/appointments/book" ;;
        /appointments/status/*|/appointments/bill/*|/appointments/test/*)
            echo "/appointments/view/${1##*/}" ;;
        *)                     echo "$1" ;;
    esac
}

login() { # $1 = email, $2 = jar name
    local jar="/tmp/dpw_$2.jar"
    rm -f "$jar"
    curl -s -c "$jar" "$BASE/login" -o /dev/null --max-time 15
    local token; token=$(csrf_of "$jar" /login)
    curl -s -o /dev/null -b "$jar" -c "$jar" \
        -d "csrf_test_name=$token" -d "email=$1" -d "password=password123" "$BASE/login" --max-time 15
    echo "$jar"
}

post() { # $1 jar, $2 url, rest: form fields. Grabs a FRESH token from the matching form page right before POSTing.
    local jar="$1"; local url="$2"; shift 2
    local token; token=$(csrf_of "$jar" "$(form_page_for "$url")")
    local args=(-s -o /dev/null -w "%{http_code}" -b "$jar" -c "$jar" -d "csrf_test_name=$token")
    for f in "$@"; do args+=(-d "$f"); done
    curl "${args[@]}" "$BASE$url" --max-time 15
}

TOMORROW=$(date -d "+1 day" +%Y-%m-%d 2>/dev/null || date -v+1d +%Y-%m-%d)

# ---------------- Manager: create doctor ----------------
MGR=$(login manager@dpams.test mgr)
code=$(post "$MGR" /doctors/store \
    "doctor_name=Dr. Test Cardiologist" \
    "description=Test doctor created by workflow test" \
    "skills=Cardiology, Testing" \
    "services=Test service" \
    "contact_info=01900-000000" \
    "status=active")
check "manager creates doctor (303)" 303 "$code"

NEW_DOC_ID=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT MAX(doctor_id) FROM dpams_db.doctors;" 2>/dev/null | tr -d '\r')

# ---------------- Manager: create patient ----------------
code=$(post "$MGR" /patients/store \
    "patient_name=Test Workflow Patient" \
    "contact_info=01800-000000" \
    "address=Test Street 42" \
    "date_of_birth=1990-01-15" \
    "gender=male")
check "manager creates patient (303)" 303 "$code"

NEW_PAT_ID=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT MAX(patient_id) FROM dpams_db.patients;" 2>/dev/null | tr -d '\r')

# ---------------- Manager: book appointment for the new patient ----------------
code=$(post "$MGR" /appointments/store \
    "doctor_id=$NEW_DOC_ID" "patient_id=$NEW_PAT_ID" \
    "appointment_date=$TOMORROW" "appointment_time=09:30")
check "manager books appointment (303)" 303 "$code"

NEW_APT_ID=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT MAX(appointment_id) FROM dpams_db.appointments;" 2>/dev/null | tr -d '\r')

# ---------------- Slot conflict: same doctor/date/time must be rejected ----------------
code=$(post "$MGR" /appointments/store \
    "doctor_id=$NEW_DOC_ID" "patient_id=1" \
    "appointment_date=$TOMORROW" "appointment_time=09:30")
DB_COUNT=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT COUNT(*) FROM dpams_db.appointments WHERE doctor_id=$NEW_DOC_ID AND appointment_date='$TOMORROW' AND appointment_time='09:30';" 2>/dev/null | tr -d '\r')
check "slot conflict rejected (still 1 row, got $DB_COUNT)" 1 "$DB_COUNT"

# ---------------- Manager: update status, bill, test ----------------
code=$(post "$MGR" "/appointments/status/$NEW_APT_ID" "status=confirmed")
check "update appointment status (303)" 303 "$code"

code=$(post "$MGR" "/appointments/bill/$NEW_APT_ID" "bill_amount=1500.50" "bill_status=paid")
check "update bill (303)" 303 "$code"

code=$(post "$MGR" "/appointments/test/$NEW_APT_ID" "test_name=CBC" "test_status=pending")
check "prescribe test (303)" 303 "$code"

ROW=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT CONCAT(status,'|',bill_amount,'|',bill_status,'|',test_name,'|',test_status) FROM dpams_db.appointments WHERE appointment_id=$NEW_APT_ID;" 2>/dev/null | tr -d '\r')
check "appointment row correct ($ROW)" "confirmed|1500.50|paid|CBC|pending" "$ROW"

# ---------------- Nurse: upload a report for the appointment ----------------
NURSE=$(login nurse@dpams.test nurse)
code=$(post "$NURSE" "/reports/store" \
    "appointment_id=$NEW_APT_ID" \
    "test_name=CBC" \
    "report_details=Hemoglobin 13.5 g/dL. WBC 7,200/uL. Platelets 250,000/uL. All within normal limits.")
check "nurse saves report (303)" 303 "$code"

ROW=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT test_status FROM dpams_db.appointments WHERE appointment_id=$NEW_APT_ID;" 2>/dev/null | tr -d '\r')
check "appointment test_status -> collected ($ROW)" "collected" "$ROW"

# ---------------- Manager: create staff (nurse) ----------------
code=$(post "$MGR" "/staff/store" \
    "name=Test Nurse" "email=test.nurse@dpams.test" "password=secret1" "role=nurse" "status=active")
check "manager creates staff account (303)" 303 "$code"

# New staff can login
JAR="/tmp/dpw_newnurse.jar"; rm -f "$JAR"
curl -s -c "$JAR" "$BASE/login" -o /dev/null --max-time 15
token=$(csrf_of "$JAR")
code=$(curl -s -o /dev/null -w "%{http_code}" -b "$JAR" -c "$JAR" \
    -d "csrf_test_name=$token" -d "email=test.nurse@dpams.test" -d "password=secret1" "$BASE/login" --max-time 15)
check "new staff can login (303)" 303 "$code"

# ---------------- Patient self-registration ----------------
JAR="/tmp/dpw_reg.jar"; rm -f "$JAR"
curl -s -c "$JAR" "$BASE/register" -o /dev/null --max-time 15
token=$(curl -s -b "$JAR" -c "$JAR" "$BASE/register" | grep -o 'name="csrf_test_name" value="[^"]*"' | head -1 | sed 's/.*value="//; s/"//')
code=$(curl -s -o /dev/null -w "%{http_code}" -b "$JAR" -c "$JAR" \
    -d "csrf_test_name=$token" -d "name=Self Registered" -d "email=selfreg@dpams.test" \
    -d "password=secret1" -d "confirm=secret1" -d "gender=female" \
    -d "contact_info=01777-777777" -d "address=Self Road 9" -d "date_of_birth=1995-05-05" \
    "$BASE/register" --max-time 15)
check "patient self-registration (303)" 303 "$code"

ROW=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT COUNT(*) FROM dpams_db.patients WHERE patient_name='Self Registered';" 2>/dev/null | tr -d '\r')
check "registration created linked patient row (1)" 1 "$ROW"

# ---------------- Patient books their own appointment ----------------
SELF_PAT=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT patient_id FROM dpams_db.patients WHERE patient_name='Self Registered';" 2>/dev/null | tr -d '\r')
# Registered user's role is patient, so /appointments/store resolves patient_id from session.
code=$(post "$JAR" "/appointments/store" \
    "doctor_id=2" "patient_id=$SELF_PAT" "appointment_date=$TOMORROW" "appointment_time=14:00")
ROW=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT COUNT(*) FROM dpams_db.appointments WHERE patient_id=$SELF_PAT AND doctor_id=2;" 2>/dev/null | tr -d '\r')
check "registered patient booked appointment ($ROW)" 1 "$ROW"

# ---------------- Unauthorized actions are blocked ----------------
PAT=$(login patient@dpams.test pat)
code=$(post "$PAT" "/appointments/status/1" "status=cancelled")
ROW=$(/c/xampp/mysql/bin/mysql.exe -u root -N -e "SELECT status FROM dpams_db.appointments WHERE appointment_id=1;" 2>/dev/null | tr -d '\r')
check "patient cannot change status ($ROW stays confirmed)" "confirmed" "$ROW"

echo
echo "PASS=$PASS FAIL=$FAIL"
