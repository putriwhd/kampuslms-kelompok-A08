#!/bin/bash

BASE_URL="http://localhost:8000/api/v1"

# Masukkan token hasil php artisan tinker
STUDENT_TOKEN="1|..."
DOSEN_A_TOKEN="2|..."
DOSEN_B_TOKEN="3|..."

echo "=== UJI COBA API KAMPUSLMS ==="

# 1. Test 401 Unauthorized
echo -n "1. GET /me (Tanpa Token) -> "
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/me"

# 2. Test 403 Mahasiswa buat assignment
echo -n "2. POST /assignments (Mahasiswa) -> "
curl -s -o /dev/null -w "%{http_code}\n" -X POST "${BASE_URL}/assignments" \
  -H "Authorization: Bearer ${STUDENT_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"course_id":1,"title":"Test"}'

# 3. Test 201 Dosen A buat assignment
echo -n "3. POST /assignments (Dosen A) -> "
curl -s -o /dev/null -w "%{http_code}\n" -X POST "${BASE_URL}/assignments" \
  -H "Authorization: Bearer ${DOSEN_A_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"course_id":1,"title":"Tugas API","due_date":"2026-12-31"}'

# 4. Test 403 Cross-Dosen Update
echo -n "4. PUT /assignments/1 (Dosen B) -> "
curl -s -o /dev/null -w "%{http_code}\n" -X PUT "${BASE_URL}/assignments/1" \
  -H "Authorization: Bearer ${DOSEN_B_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"title":"Begal Tugas"}'