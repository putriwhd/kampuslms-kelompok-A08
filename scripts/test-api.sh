#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${API_BASE_URL:-http://localhost:8000/api/v1}"

# Fungsi bantu untuk login dan mengambil token (DITARUH DI SINI)
get_token() {
  local email="$1"
  local pass="$2"
  curl -s -X POST "$BASE_URL/auth/login" \
    -H "Accept: application/json" -H "Content-Type: application/json" \
    -d "{\"email\":\"$email\",\"password\":\"$pass\"}" | php -r '
      $d = json_decode(file_get_contents("php://stdin"), true);
      echo $d["data"]["token"] ?? $d["token"] ?? "";
    '
}

DOSEN_A_TOKEN="${DOSEN_A_TOKEN:-$(get_token 'dosen@kampuslms.test' 'password')}"
DOSEN_B_TOKEN="${DOSEN_B_TOKEN:-$(get_token 'dosen2@kampuslms.test' 'password')}"
STUDENT_TOKEN="${STUDENT_TOKEN:-$(get_token 'mahasiswa@kampuslms.test' 'password')}"
OTHER_STUDENT_TOKEN="${OTHER_STUDENT_TOKEN:-$(get_token 'mahasiswa2@kampuslms.test' 'password')}"

COURSE_ID="${COURSE_ID:-1}"
OTHER_COURSE_ID="${OTHER_COURSE_ID:-2}"

RESPONSE_FILE="$(mktemp)"
UPLOAD_FILE="$(mktemp)"
UPLOAD_FILE_CURL="$UPLOAD_FILE"
if command -v cygpath >/dev/null 2>&1; then
    UPLOAD_FILE_CURL="$(cygpath -m "$UPLOAD_FILE")"
fi
trap 'rm -f "$RESPONSE_FILE" "$UPLOAD_FILE"' EXIT
LAST_BODY=""

assert_status() {
    local expected="$1"
    local label="$2"
    shift 2

    local actual
    if ! actual="$(curl --silent --show-error --output "$RESPONSE_FILE" \
        --write-out '%{http_code}' "$@")"; then
        printf 'FAIL %s: curl request failed\n' "$label" >&2
        return 1
    fi

    LAST_BODY="$(cat "$RESPONSE_FILE")"
    if [[ "$actual" != "$expected" ]]; then
        printf 'FAIL %s: expected HTTP %s, got HTTP %s\n%s\n' \
            "$label" "$expected" "$actual" "$LAST_BODY" >&2
        return 1
    fi

    printf 'PASS %s: HTTP %s\n' "$label" "$actual"
}

JSON_HEADERS=(-H 'Accept: application/json' -H 'Content-Type: application/json')

assert_status 401 'GET /me tanpa token' \
    "$BASE_URL/me" -H 'Accept: application/json'

assert_status 401 'POST /auth/logout tanpa token' \
    -X POST "$BASE_URL/auth/logout" -H 'Accept: application/json'

assert_status 401 'GET /courses tanpa token' \
    "$BASE_URL/courses" -H 'Accept: application/json'

assert_status 401 'GET /courses/{id} tanpa token' \
    "$BASE_URL/courses/$COURSE_ID" -H 'Accept: application/json'

assert_status 401 'GET /courses/{id}/materials tanpa token' \
    "$BASE_URL/courses/$COURSE_ID/materials" -H 'Accept: application/json'

assert_status 401 'GET /courses/{id}/assignments tanpa token' \
    "$BASE_URL/courses/$COURSE_ID/assignments" -H 'Accept: application/json'

assert_status 401 'POST /assignments tanpa token' \
    -X POST "$BASE_URL/assignments" \
    -H 'Accept: application/json' --data '{}'

assert_status 401 'PATCH /assignments/{id} tanpa token' \
    -X PATCH "$BASE_URL/assignments/1" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' --data '{}'

assert_status 401 'DELETE /assignments/{id} tanpa token' \
    -X DELETE "$BASE_URL/assignments/1" -H 'Accept: application/json'

assert_status 401 'POST /submissions tanpa token' \
    -X POST "$BASE_URL/submissions" -H 'Accept: application/json'

assert_status 401 'PUT /submissions/{id}/grade tanpa token' \
    -X PUT "$BASE_URL/submissions/1/grade" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' --data '{}'

assert_status 401 'GET /assignments/{id}/submissions tanpa token' \
    "$BASE_URL/assignments/1/submissions" -H 'Accept: application/json'

assert_status 401 'POST /assignments/{id}/submissions tanpa token' \
    -X POST "$BASE_URL/assignments/1/submissions" -H 'Accept: application/json'

assert_status 401 'GET /notifications tanpa token' \
    "$BASE_URL/notifications" -H 'Accept: application/json'

assert_status 401 'POST /notifications/{id}/read tanpa token' \
    -X POST "$BASE_URL/notifications/00000000-0000-0000-0000-000000000000/read" \
    -H 'Accept: application/json'

assert_status 422 'Login dengan kredensial salah ditolak' \
    -X POST "$BASE_URL/auth/login" \
    "${JSON_HEADERS[@]}" \
    --data '{"email":"tidak-ada@kampuslms.test","password":"salah"}'

assert_status 200 'Dosen pemilik dapat melihat daftar courses' \
    "$BASE_URL/courses" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Pengguna terautentikasi dapat melihat profil sendiri' \
    "$BASE_URL/me" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Mahasiswa dapat melihat daftar courses' \
    "$BASE_URL/courses" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN"

assert_status 200 'Dosen dapat melihat materials course miliknya' \
    "$BASE_URL/courses/$COURSE_ID/materials" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Dosen dapat memfilter assignments course miliknya' \
    "$BASE_URL/courses/$COURSE_ID/assignments?status=published&page=1" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Pengguna terautentikasi dapat melihat course' \
    "$BASE_URL/courses/$COURSE_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Mahasiswa dapat melihat course aktif' \
    "$BASE_URL/courses/$COURSE_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN"

assert_status 403 'Dosen lain tidak dapat melihat detail course' \
    "$BASE_URL/courses/$OTHER_COURSE_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 403 'Mahasiswa tidak boleh membuat assignment' \
    -X POST "$BASE_URL/assignments" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN" \
    -F "course_id=$COURSE_ID" \
    -F 'title=Pengujian role mahasiswa' \
    -F 'instructions=Permintaan ini harus ditolak karena role.' \
    -F 'due_at=2030-12-31T23:59:00'

assert_status 403 'Dosen tidak dapat membuat assignment di course dosen lain' \
    -X POST "$BASE_URL/assignments" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN" \
    -F "course_id=$OTHER_COURSE_ID" \
    -F 'title=Pengujian ownership' \
    -F 'instructions=Permintaan ini harus ditolak karena pemilik course.' \
    -F 'due_at=2030-12-31T23:59:00'

assert_status 201 'Dosen pemilik course membuat assignment' \
    -X POST "$BASE_URL/assignments" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN" \
    -F "course_id=$COURSE_ID" \
    -F 'title=Uji API Minggu 6' \
    -F 'instructions=Assignment sementara untuk pengujian API.' \
    -F 'due_at=2030-12-31T23:59:00' \
    -F 'status=published'

ASSIGNMENT_ID="$(printf '%s' "$LAST_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    echo $response["data"]["id"] ?? "";
')"
if [[ -z "$ASSIGNMENT_ID" ]]; then
    printf 'FAIL response store tidak berisi data.id\n%s\n' "$LAST_BODY" >&2
    exit 1
fi

printf 'Berkas submission sementara untuk pengujian API.\n' > "$UPLOAD_FILE"
assert_status 201 'Mahasiswa dapat mengumpulkan assignment melalui endpoint nested' \
    -X POST "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN" \
    -F "file=@$UPLOAD_FILE_CURL;filename=jawaban-api.txt" \
    -F 'note=Pengujian API'

SUBMISSION_ID="$(printf '%s' "$LAST_BODY" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    echo $response["data"]["id"] ?? "";
')"
if [[ -z "$SUBMISSION_ID" ]]; then
    printf 'FAIL response submission tidak berisi data.id\n%s\n' "$LAST_BODY" >&2
    exit 1
fi

assert_status 403 'Mahasiswa yang tidak terdaftar tidak dapat mengumpulkan tugas' \
    -X POST "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $OTHER_STUDENT_TOKEN" \
    -F "file=@$UPLOAD_FILE_CURL;filename=jawaban-tidak-terdaftar.txt"

assert_status 422 'Mahasiswa tidak dapat mengirim submission duplikat' \
    -X POST "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN" \
    -F "file=@$UPLOAD_FILE_CURL;filename=jawaban-duplikat.txt"

assert_status 403 'Dosen tidak dapat mengirim submission' \
    -X POST "$BASE_URL/submissions" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 403 'Mahasiswa tidak dapat memberi nilai' \
    -X PUT "$BASE_URL/submissions/$SUBMISSION_ID/grade" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN" \
    -H 'Content-Type: application/json' --data '{"score":80}'

assert_status 403 'Dosen lain tidak boleh menilai submission' \
    -X PUT "$BASE_URL/submissions/$SUBMISSION_ID/grade" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_B_TOKEN" \
    -H 'Content-Type: application/json' --data '{"score":80}'

# --- PENGUJIAN PENILAIAN PERTAMA KALI (HTTP 201) ---
assert_status 201 'Dosen pemilik dapat menilai submission pertama kali' \
    -X PUT "$BASE_URL/submissions/$SUBMISSION_ID/grade" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN" \
    -H 'Content-Type: application/json' --data '{"score":80,"feedback":"Lulus pengujian API."}'

# --- PENGUJIAN PEMBARUAN NILAI / UPDATE GRADE (HTTP 200) ---
assert_status 200 'Dosen pemilik dapat memperbarui nilai submission' \
    -X PUT "$BASE_URL/submissions/$SUBMISSION_ID/grade" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN" \
    -H 'Content-Type: application/json' --data '{"score":90,"feedback":"Nilai diperbarui."}'

# --- PENGUJIAN NOTIFIKASI ---
assert_status 200 'Pengguna dapat mengambil daftar notifikasi' \
    "$BASE_URL/notifications" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN"

assert_status 403 'Pengguna tidak dapat menandai notifikasi milik pengguna lain/tidak ada' \
    -X POST "$BASE_URL/notifications/00000000-0000-0000-0000-000000000000/read" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN"

assert_status 200 'Dosen pemilik dapat melihat submissions assignment' \
    "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Dosen pemilik dapat memperbarui assignment' \
    -X PATCH "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN" \
    -H 'Content-Type: application/json' --data '{"title":"Assignment API teruji"}'

assert_status 403 'Dosen lain tidak boleh memperbarui assignment' \
    -X PATCH "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_B_TOKEN" \
    -H 'Content-Type: application/json' --data '{"title":"Perubahan tanpa izin"}'

assert_status 403 'Mahasiswa tidak boleh memperbarui assignment' \
    -X PATCH "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN" \
    -H 'Content-Type: application/json' --data '{"title":"Perubahan tanpa izin"}'

assert_status 403 'Dosen lain tidak boleh menghapus assignment' \
    -X DELETE "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_B_TOKEN"

assert_status 403 'Mahasiswa tidak boleh menghapus assignment' \
    -X DELETE "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $STUDENT_TOKEN"

assert_status 204 'Dosen pemilik menghapus assignment' \
    -X DELETE "$BASE_URL/assignments/$ASSIGNMENT_ID" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_A_TOKEN"

assert_status 200 'Pengguna terautentikasi dapat logout' \
    -X POST "$BASE_URL/auth/logout" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_B_TOKEN"

assert_status 401 'Token yang sudah logout tidak dapat digunakan kembali' \
    "$BASE_URL/me" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $DOSEN_B_TOKEN"

printf 'Semua pengujian API lulus.\n'