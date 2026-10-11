#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${API_BASE_URL:-http://localhost:8000/api/v1}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@kampuslms.test}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-password}"
LECTURER_EMAIL="${LECTURER_EMAIL:-dosen@kampuslms.test}"
LECTURER_PASSWORD="${LECTURER_PASSWORD:-password}"
STUDENT_EMAIL="${STUDENT_EMAIL:-mahasiswa@kampuslms.test}"
STUDENT_PASSWORD="${STUDENT_PASSWORD:-password}"
OTHER_STUDENT_EMAIL="${OTHER_STUDENT_EMAIL:-mahasiswa2@kampuslms.test}"
OTHER_STUDENT_PASSWORD="${OTHER_STUDENT_PASSWORD:-password}"
PASSWORD="${PASSWORD:-password}"

RESPONSE_FILE="$(mktemp)"
TOKEN_FILE="$(mktemp)"
cleanup() {
    if [[ -f "$TOKEN_FILE" ]]; then
        while IFS= read -r token; do
            [[ -z "$token" ]] && continue
            curl --silent --output /dev/null -X POST "$BASE_URL/auth/logout" \
                -H 'Accept: application/json' \
                -H "Authorization: Bearer $token" || true
        done < "$TOKEN_FILE"
    fi
    rm -f "$RESPONSE_FILE" "$TOKEN_FILE"
}
trap cleanup EXIT
LAST_BODY=""

fail() {
    printf 'FAIL %s\n' "$*" >&2
    exit 1
}

request_status() {
    local expected="$1"
    local label="$2"
    shift 2

    local actual
    if ! actual="$(curl --silent --show-error --output "$RESPONSE_FILE" \
        --write-out '%{http_code}' "$@")"; then
        fail "$label: request curl gagal"
    fi

    LAST_BODY="$(cat "$RESPONSE_FILE")"
    if [[ "$actual" != "$expected" ]]; then
        printf 'FAIL %s: diharapkan HTTP %s, diterima HTTP %s\n%s\n' \
            "$label" "$expected" "$actual" "$LAST_BODY" >&2
        exit 1
    fi

    printf 'PASS %s: HTTP %s\n' "$label" "$actual"
}

login_token() {
    local email="$1"
    local password="$2"
    local response
    local status

    status="$(curl --silent --show-error --output "$RESPONSE_FILE" \
        --write-out '%{http_code}' -X POST "$BASE_URL/auth/login" \
        -H 'Accept: application/json' \
        -H 'Content-Type: application/json' \
        --data "{\"email\":\"$email\",\"password\":\"$password\"}")" \
        || fail "login request untuk $email gagal"
    response="$(cat "$RESPONSE_FILE")"

    if [[ "$status" != "200" ]]; then
        printf 'Gagal login sebagai %s (HTTP %s).\n%s\n' \
            "$email" "$status" "$response" >&2
        exit 1
    fi

    local token
    token="$(printf '%s' "$response" | php -r '
        $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
        $token = $response["data"]["token"] ?? "";
        if ($token === "") {
            fwrite(STDERR, "Response login tidak berisi data.token.\n");
            exit(1);
        }
        echo $token;
    ')"
    printf '%s\n' "$token" >> "$TOKEN_FILE"
    printf '%s' "$token"
}

ADMIN_TOKEN="${ADMIN_TOKEN:-$(login_token "$ADMIN_EMAIL" "$ADMIN_PASSWORD")}"
LECTURER_TOKEN="${LECTURER_TOKEN:-$(login_token "$LECTURER_EMAIL" "$LECTURER_PASSWORD")}"
STUDENT_TOKEN="${STUDENT_TOKEN:-$(login_token "$STUDENT_EMAIL" "$STUDENT_PASSWORD")}"
OTHER_STUDENT_TOKEN="${OTHER_STUDENT_TOKEN:-$(login_token "$OTHER_STUDENT_EMAIL" "$OTHER_STUDENT_PASSWORD")}"

AUTH_ADMIN=(-H "Authorization: Bearer $ADMIN_TOKEN")
AUTH_LECTURER=(-H "Authorization: Bearer $LECTURER_TOKEN")
AUTH_STUDENT=(-H "Authorization: Bearer $STUDENT_TOKEN")
AUTH_OTHER_STUDENT=(-H "Authorization: Bearer $OTHER_STUDENT_TOKEN")
JSON_HEADERS=(-H 'Accept: application/json' -H 'Content-Type: application/json')

request_status 401 'Daftar course tanpa token ditolak' \
    "$BASE_URL/courses" -H 'Accept: application/json'

request_status 200 'Admin dapat melihat daftar seluruh course' \
    "$BASE_URL/courses" -H 'Accept: application/json' "${AUTH_ADMIN[@]}"
ADMIN_COURSES_BODY="$LAST_BODY"

COURSE_ID="${COURSE_ID:-$(printf '%s' "$ADMIN_COURSES_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach ($response["data"] ?? [] as $course) {
        if (($course["lecturer"]["email"] ?? null) === $argv[1]) {
            echo $course["id"];
            exit;
        }
    }
    fwrite(STDERR, "Tidak ada course untuk dosen pemilik yang dikonfigurasi.\n");
    exit(1);
' "$LECTURER_EMAIL")}"

OTHER_COURSE_ID="${OTHER_COURSE_ID:-$(printf '%s' "$ADMIN_COURSES_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach ($response["data"] ?? [] as $course) {
        if (($course["lecturer"]["email"] ?? null) !== $argv[1]) {
            echo $course["id"];
            exit;
        }
    }
    fwrite(STDERR, "Tidak ditemukan course milik dosen lain.\n");
    exit(1);
' "$LECTURER_EMAIL")}"

OTHER_LECTURER_EMAIL="${OTHER_LECTURER_EMAIL:-$(printf '%s' "$ADMIN_COURSES_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach ($response["data"] ?? [] as $course) {
        if ((string) ($course["id"] ?? "") === $argv[1]) {
            echo $course["lecturer"]["email"] ?? "";
            exit;
        }
    }
    fwrite(STDERR, "Tidak dapat menentukan dosen course pembanding.\n");
    exit(1);
' "$OTHER_COURSE_ID")}"
OTHER_LECTURER_PASSWORD="${OTHER_LECTURER_PASSWORD:-password}"
OTHER_LECTURER_TOKEN="${OTHER_LECTURER_TOKEN:-$(login_token "$OTHER_LECTURER_EMAIL" "$OTHER_LECTURER_PASSWORD")}"
AUTH_OTHER_LECTURER=(-H "Authorization: Bearer $OTHER_LECTURER_TOKEN")

request_status 200 'Dosen pengampu dapat melihat course sendiri' \
    "$BASE_URL/courses/$COURSE_ID" -H 'Accept: application/json' "${AUTH_LECTURER[@]}"

request_status 403 'Dosen lain tidak dapat melihat course dosen pengampu' \
    "$BASE_URL/courses/$COURSE_ID" -H 'Accept: application/json' "${AUTH_OTHER_LECTURER[@]}"

request_status 200 'Mahasiswa terdaftar dapat melihat course' \
    "$BASE_URL/courses/$COURSE_ID" -H 'Accept: application/json' "${AUTH_STUDENT[@]}"

request_status 403 'Mahasiswa yang tidak terdaftar ditolak' \
    "$BASE_URL/courses/$COURSE_ID" -H 'Accept: application/json' "${AUTH_OTHER_STUDENT[@]}"

request_status 403 'Mahasiswa tidak dapat membuat tugas' \
    -X POST "$BASE_URL/assignments" "${JSON_HEADERS[@]}" "${AUTH_STUDENT[@]}" \
    --data "{\"course_id\":$COURSE_ID,\"title\":\"Uji role mahasiswa\",\"instructions\":\"Harus ditolak.\",\"due_at\":\"2030-12-31T23:59:00\"}"

request_status 200 'Admin dapat melihat tugas draft' \
    "$BASE_URL/courses/$COURSE_ID/assignments?status=draft" \
    -H 'Accept: application/json' "${AUTH_ADMIN[@]}"
ADMIN_DRAFTS_BODY="$LAST_BODY"
DRAFT_ASSIGNMENT_ID="$(printf '%s' "$ADMIN_DRAFTS_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    $assignment = $response["data"][0] ?? null;
    if ($assignment === null || ($assignment["status"] ?? null) !== "draft") {
        fwrite(STDERR, "Course contoh tidak memiliki tugas draft.\n");
        exit(1);
    }
    echo $assignment["id"];
')"

request_status 200 'Dosen pengampu dapat melihat tugas draft' \
    "$BASE_URL/courses/$COURSE_ID/assignments?status=draft" \
    -H 'Accept: application/json' "${AUTH_LECTURER[@]}"
php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    if (($response["data"][0]["status"] ?? null) !== "draft") {
        fwrite(STDERR, "FAIL dosen pengampu tidak menerima tugas draft.\n");
        exit(1);
    }
' <<< "$LAST_BODY"
printf 'PASS tugas draft tersedia untuk dosen pengampu\n'

request_status 200 'Mahasiswa terdaftar hanya menerima tugas published' \
    "$BASE_URL/courses/$COURSE_ID/assignments" \
    -H 'Accept: application/json' "${AUTH_STUDENT[@]}"
printf '%s' "$LAST_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach ($response["data"] ?? [] as $assignment) {
        if (($assignment["status"] ?? null) !== "published") {
            fwrite(STDERR, "FAIL daftar mahasiswa memuat tugas yang bukan published.\n");
            exit(1);
        }
    }
'
printf 'PASS daftar mahasiswa tidak membocorkan tugas draft\n'

request_status 200 'Mahasiswa tetap hanya menerima tugas published pada filter draft' \
    "$BASE_URL/courses/$COURSE_ID/assignments?status=draft" \
    -H 'Accept: application/json' "${AUTH_STUDENT[@]}"
php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach ($response["data"] ?? [] as $assignment) {
        if (($assignment["status"] ?? null) !== "published") {
            fwrite(STDERR, "FAIL filter draft mahasiswa membocorkan tugas draft.\n");
            exit(1);
        }
    }
' <<< "$LAST_BODY"
printf 'PASS filter draft tidak membocorkan tugas draft kepada mahasiswa\n'

request_status 403 'Dosen lain tidak dapat melihat tugas draft course asing' \
    "$BASE_URL/courses/$COURSE_ID/assignments?status=draft" \
    -H 'Accept: application/json' "${AUTH_OTHER_LECTURER[@]}"

request_status 403 'Dosen lain tidak dapat membuka submission course asing' \
    "$BASE_URL/assignments/$DRAFT_ASSIGNMENT_ID/submissions" \
    -H 'Accept: application/json' "${AUTH_OTHER_LECTURER[@]}"

printf 'Semua skenario uji otorisasi API lulus.\n'
