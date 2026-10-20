# Dokumentasi API KampusLMS

API menggunakan prefix `/api/v1`, token Laravel Sanctum, dan JSON. Koleksi menggunakan bentuk `{ "data": [...], "meta": { "current_page": 1, "last_page": 5, "total": 47 } }`; respons tunggal menggunakan `{ "data": {...} }`. Error validasi memakai status `422` dan pesan `Data yang diberikan tidak valid.`; error akses memakai status `403` dan pesan `Anda tidak memiliki akses ke sumber daya ini.`; error autentikasi memakai status `401` dan pesan `Tidak terautentikasi.`.

## Persiapan

Jalankan server dan isi database dengan akun demo:

```bash
php artisan serve
php artisan db:seed
```

> [!WARNING]
> Menjalankan `php artisan db:seed` berulang kali pada database yang sudah berisi data akan menambah 5 mata kuliah demo baru beserta data terkait pada setiap pemanggilan karena seeder menggunakan factory create. Untuk mereset data pengujian ke kondisi awal yang bersih, gunakan `php artisan migrate:fresh --seed`.

Seeder menyediakan `admin@kampuslms.test`, `admin2@kampuslms.test`,
`dosen@kampuslms.test`, `dosen2@kampuslms.test`, `mahasiswa@kampuslms.test`,
dan `mahasiswa2@kampuslms.test`; semuanya memakai kata sandi `password`.
`dosen2` mengampu course demo, sedangkan `mahasiswa2` sengaja tidak terdaftar
di course demo untuk pengujian penolakan akses. Kedua akun admin memiliki hak
admin yang sama. Login untuk memperoleh token:

Untuk antarmuka web, rute `/register` tetap tersedia dan aktif secara publik
untuk pembuatan akun mahasiswa, namun tautannya disembunyikan dari tampilan form
login (`/login`) sehingga harus diakses langsung melalui URL. Penetapan role
Admin/Dosen dan pendaftaran mahasiswa ke mata kuliah tetap dikelola Admin.
Admin mendaftarkan mahasiswa dari detail mata kuliah. Dosen membuat materi dan
tugas dari daftar materi/tugas pada course yang diampu, lalu memberi nilai di
detail tugas. Mahasiswa melihat nilai dan umpan balik miliknya di detail tugas
yang sudah diikuti.

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"dosen@kampuslms.test","password":"password"}'
```

Respons sukses `200` berisi token plaintext sekali tampil dan Resource pengguna di dalam `data`:

```json
{
  "data": {
    "token": "1|<token>",
    "user": {
      "id": 2,
      "name": "Dosen Demo",
      "email": "dosen@kampuslms.test",
      "nim_nip": "NIP001",
      "role": "dosen"
    }
  }
}
```

Simpan token, lalu gunakan pada request yang dilindungi:

```bash
export API_TOKEN='<token-dari-login>'
```

Tambahkan header `Accept: application/json` agar Laravel mengembalikan error dalam format JSON. Login dibatasi 5 request per menit; grup endpoint terautentikasi dibatasi 60 request per menit. Selain login, seluruh endpoint berikut membutuhkan `Authorization: Bearer <token>`.

## Endpoint

### Autentikasi

| Method | URI | Akses | Hasil utama |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Publik | `200`, token dan pengguna |
| `POST` | `/api/v1/auth/logout` | Pengguna terautentikasi | `200`, pesan berhasil logout |
| `GET` | `/api/v1/me` | Pengguna terautentikasi | `200`, Resource pengguna |

`POST /auth/login` menerima JSON `email` (wajib dan format email) serta `password` (wajib). Controller tidak membaca field `device_name` dan selalu membuat token Sanctum dengan nama tetap `auth_token`. Kredensial salah menghasilkan `422` dengan pesan generik; validasi input juga menghasilkan `422`.

Contoh response kredensial salah:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "email": ["Email atau kata sandi yang Anda masukkan salah."]
  }
}
```

`POST /auth/logout` menghapus token aktif dan mengembalikan `200`:

```json
{
  "message": "Berhasil logout."
}
```

Token yang tidak dikirim atau tidak valid pada endpoint terlindungi menghasilkan `401`:

```json
{
  "message": "Tidak terautentikasi."
}
```

```bash
curl http://localhost:8000/api/v1/me \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"

curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Jalankan logout setelah request yang membutuhkan token karena token yang digunakan langsung dicabut.

Contoh response `GET /me`:

```json
{
  "data": {
    "id": 2,
    "name": "Dosen Demo",
    "email": "dosen@kampuslms.test",
    "nim_nip": "NIP001",
    "role": "dosen"
  }
}
```

`GET /me` tidak menerima parameter atau body; `POST /auth/logout` tidak memerlukan body. Keduanya memerlukan token valid. Tanpa token, keduanya mengembalikan `401`.

### Mata kuliah

| Method | URI | Akses | Hasil utama |
|---|---|---|---|
| `GET` | `/api/v1/courses` | Admin / dosen pengampu / mahasiswa terdaftar | `200`, koleksi terpaginasikan |
| `GET` | `/api/v1/courses/{id}` | Admin / dosen pengampu / mahasiswa terdaftar | `200`, Resource; `403` jika tak berhak; `404` draft bagi mhs / ID nihil |
| `GET` | `/api/v1/courses/{id}/materials` | Admin / dosen pengampu / mahasiswa terdaftar | `200`, koleksi materi terpaginasikan |
| `GET` | `/api/v1/courses/{id}/assignments` | Admin / dosen pengampu / mahasiswa terdaftar | `200`, koleksi tugas; mendukung `status` dan `page` |

Daftar dibatasi berdasarkan peran pengguna:
- **Admin**: dapat melihat seluruh mata kuliah.
- **Dosen**: hanya melihat mata kuliah yang diampunya.
- **Mahasiswa**: hanya melihat mata kuliah yang diikutinya dengan status bukan `draft`.
Daftar memakai pagination 15 item per halaman serta eager loading dosen dan jumlah materi/tugas.

`GET /courses` menerima parameter query `page` dari paginator. `GET /courses/{id}` menerima ID pada path.
Aturan hak akses detail mata kuliah:
- **Admin**: dapat melihat detail seluruh mata kuliah (termasuk status draft).
- **Dosen**: hanya dapat melihat detail mata kuliah yang diampunya; mengakses mata kuliah dosen lain menghasilkan `403`.
- **Mahasiswa**: hanya dapat melihat detail mata kuliah yang diikutinya dan tidak berstatus `draft`. Jika mahasiswa mengakses mata kuliah berstatus `draft`, server mengembalikan `404`. Jika mahasiswa mengakses mata kuliah yang bukan `draft` tetapi tidak terdaftar di dalamnya, server mengembalikan `403`.
- ID mata kuliah yang tidak ditemukan mengembalikan `404`.

```bash
curl 'http://localhost:8000/api/v1/courses?page=1' \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"

curl http://localhost:8000/api/v1/courses/1 \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Contoh response daftar mengikuti bentuk kontrak:

```json
{
  "data": [
    {
      "id": 1,
      "code": "IF101",
      "name": "Pengantar Informatika",
      "description": "Contoh deskripsi",
      "sks": 3,
      "lecturer_id": 2,
      "status": "active",
      "counts": {
        "materials": 2,
        "assignments": 1
      },
      "created_at": "2026-10-06T10:00:00+00:00",
      "lecturer": {
        "id": 2,
        "name": "Dosen Demo",
        "email": "dosen@kampuslms.test",
        "nim_nip": "NIP001",
        "role": "dosen"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

Metadata koleksi hanya berisi `current_page`, `last_page`, dan `total`.

Detail course memakai Resource yang sama serta menyertakan relasi dosen, materials, assignments, dan counts yang sudah dimuat. Mahasiswa hanya dapat melihat detail course yang diikutinya dan bukan draft; mahasiswa yang tidak terdaftar menerima `403`, sedangkan course draft bagi mahasiswa menghasilkan `404`. Daftar materi dan tugas juga tersedia melalui endpoint koleksi pada tabel di atas. Endpoint materi (`GET /courses/{id}/materials`) dapat diakses oleh Admin, dosen pengampu, dan mahasiswa terdaftar pada course tersebut.

```bash
curl 'http://localhost:8000/api/v1/courses/1/materials?page=1' \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"

curl 'http://localhost:8000/api/v1/courses/1/assignments?status=published&page=1' \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Contoh detail course berstatus aktif tanpa material atau assignment:

```json
{
  "data": {
    "id": 1,
    "code": "IF101",
    "name": "Pengantar Informatika",
    "description": "Contoh deskripsi",
    "sks": 3,
    "lecturer_id": 2,
    "status": "active",
    "counts": {
      "materials": 0,
      "assignments": 0
    },
    "created_at": "2026-10-06T10:00:00+00:00",
    "lecturer": {
      "id": 2,
      "name": "Dosen Demo",
      "email": "dosen@kampuslms.test",
      "nim_nip": "NIP001",
      "role": "dosen"
    },
    "materials": [],
    "assignments": []
  }
}
```

Detail course tanpa token menghasilkan `401`; dosen yang bukan pengampu dan mahasiswa yang tidak terdaftar mendapat `403`, sedangkan course berstatus draft bagi mahasiswa serta ID course yang tidak ditemukan menghasilkan `404`.

Contoh response dosen lintas course atau mahasiswa tidak terdaftar (`403`):

```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

Contoh response course draft bagi mahasiswa atau ID tidak ditemukan (`404`):

```json
{
  "message": "Not found."
}
```

### Tugas

| Method | URI | Akses | Hasil utama |
|---|---|---|---|
| `GET` | `/api/v1/courses/{id}/assignments` | Admin / mahasiswa terdaftar / dosen pemilik | `200`, koleksi dengan filter `status` dan pagination |
| `POST` | `/api/v1/assignments` | Admin; dosen pengampu | `201`, Resource tugas |
| `PUT` atau `PATCH` | `/api/v1/assignments/{id}` | Admin; dosen pengampu tugas | `200`, Resource tugas |
| `DELETE` | `/api/v1/assignments/{id}` | Admin; dosen pengampu tugas | `204`, tanpa body |

Admin dapat mengelola tugas di semua course, sedangkan dosen hanya dapat
mengelola tugas pada course yang diampunya.

Payload `POST`:

| Field | Ketentuan |
|---|---|
| `course_id` | Wajib, ID mata kuliah |
| `title` | Wajib, string maksimal 255 karakter |
| `instructions` | Wajib, string |
| `due_at` | Wajib, tanggal/waktu yang valid |
| `max_score` | Opsional, integer 1–100; default 100 dari database |
| `allow_late` | Opsional, boolean; default true dari database |
| `status` | Opsional: `draft` atau `published`; default `published` |

`created_by` diisi oleh server dari pengguna yang login. Contoh membuat tugas:

Materi tersedia melalui `POST /api/v1/courses/{id}/materials`,
`PUT`/`PATCH /api/v1/materials/{id}`, dan `DELETE /api/v1/materials/{id}`.
Operasi ini hanya untuk Admin atau dosen pengampu. Payload menerima
`title`, `description`, `type` (`file` atau `link`), dan
`external_url` untuk jenis `link`; klien tidak dapat menetapkan path berkas.
**Catatan penting:** Endpoint API Material hanya menerima dan menyimpan
metadata (tidak menyediakan fitur pengunggahan berkas fisik via API); unggahan
berkas fisik materi hanya didukung melalui antarmuka web.

```bash
curl -X POST http://localhost:8000/api/v1/assignments \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN" \
  -F 'course_id=1' \
  -F 'title=Latihan API' \
  -F 'instructions=Kerjakan latihan sesuai materi.' \
  -F 'due_at=2026-12-31T23:59:00' \
  -F 'status=published'
```

Contoh response sukses `201`:

```json
{
  "data": {
    "id": 1,
    "course_id": 1,
    "created_by": 2,
    "title": "Latihan API",
    "instructions": "Kerjakan latihan sesuai materi.",
    "due_at": "2026-12-31T23:59:00.000000Z",
    "max_score": 100,
    "allow_late": true,
    "status": "published",
    "course": {
      "id": 1,
      "code": "IF101",
      "name": "Pengantar Informatika",
      "description": "Contoh deskripsi",
      "sks": 3,
      "lecturer_id": 2,
      "status": "active",
      "counts": {
        "materials": 2,
        "assignments": 1
      },
      "created_at": "2026-10-06T10:00:00+00:00",
      "lecturer": {
        "id": 2,
        "name": "Dosen Demo",
        "email": "dosen@kampuslms.test",
        "nim_nip": "NIP001",
        "role": "dosen"
      }
    },
    "creator": {
      "id": 2,
      "name": "Dosen Demo",
      "email": "dosen@kampuslms.test",
      "nim_nip": "NIP001",
      "role": "dosen"
    }
  }
}
```

Untuk `PUT`/`PATCH`, kirim satu atau lebih field yang sama selain `course_id`; field wajib hanya diwajibkan jika disertakan. Admin dapat mengelola tugas di semua course; dosen yang bukan pengampu menerima `403`. Pengguna yang bukan Admin/dosen juga mendapat `403`, sedangkan input tidak valid mendapat `422`.

`PUT` dan `PATCH` menerima `title`, `instructions`, `due_at`, `max_score` (integer 1–100), `allow_late`, dan `status` secara opsional. `course_id` tidak dapat diubah melalui endpoint update. Tanpa token, route terlindungi mengembalikan `401`; ID tugas yang tidak ditemukan mengembalikan `404`.

`PATCH /assignments/{id}` yang berhasil menghasilkan `200` dengan bentuk Resource yang sama seperti contoh create `201`; field tugas pada `data` mencerminkan nilai setelah perubahan. `DELETE /assignments/{id}` yang berhasil mengembalikan `204` dengan body kosong.

Contoh response error validasi untuk `POST /assignments`:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

Contoh response `403` jika role bukan Admin/dosen atau dosen bukan pengampu course:

```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

```bash
curl -X PATCH http://localhost:8000/api/v1/assignments/1 \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $API_TOKEN" \
  -d '{"title":"Latihan API - Revisi"}'

curl -X DELETE http://localhost:8000/api/v1/assignments/1 \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

### Pengumpulan dan penilaian

| Method | URI | Akses | Hasil utama |
|---|---|---|---|
| `GET` | `/api/v1/assignments/{id}/submissions` | Admin; dosen pengampu | `200`, koleksi submission terpaginasikan |
| `POST` | `/api/v1/assignments/{id}/submissions` | Mahasiswa terdaftar | `201`, Resource submission; multipart `file` |
| `POST` | `/api/v1/submissions` | Mahasiswa terdaftar | `201`, Resource submission |
| `PUT` | `/api/v1/submissions/{id}/grade` | Dosen pengampu mata kuliah | `201` grade baru; `200` penilaian ulang |

Pengumpulan melalui route kontrak `/assignments/{id}/submissions` memakai `multipart/form-data`: `file` wajib dan `note` opsional; ID assignment berasal dari path. Mahasiswa harus terdaftar di mata kuliah tugas tersebut dan hanya dapat membuat satu submission per tugas. Route kompatibilitas `POST /submissions` juga tersedia dan menerima `assignment_id` di body. Berkas disimpan pada disk lokal Laravel di direktori `submissions/`.

**Catatan Operasi Submission:** Endpoint yang tersedia saat ini hanya melayani operasi **pembuatan (pengumpulan)** submission baru. Belum tersedia rute aktif (baik di web maupun API) untuk memperbarui atau mengunggah ulang file submission yang sudah dikirimkan, meskipun `SubmissionPolicy::update` sudah didefinisikan pada level policy.

Contoh response `403` mahasiswa yang tidak terdaftar:

```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

Contoh response `422` saat submission tugas yang sama dikirim ulang:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "assignment_id": ["Mahasiswa sudah mengumpulkan tugas ini."]
  }
}
```

```bash
curl -X POST http://localhost:8000/api/v1/assignments/1/submissions \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN" \
  -F 'file=@./jawaban.pdf' \
  -F 'note=Jawaban latihan'

curl 'http://localhost:8000/api/v1/assignments/1/submissions?page=1' \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Contoh response sukses `201` (nilai `grade` belum ada saat submission dibuat):

```json
{
  "data": {
    "id": 12,
    "assignment_id": 1,
    "user_id": 5,
    "file_path": "submissions/example.pdf",
    "original_name": "jawaban.pdf",
    "file_size": 2048,
    "note": "Jawaban latihan",
    "submitted_at": "2026-10-06T07:00:00.000000Z",
    "is_late": false,
    "assignment": {
      "id": 1,
      "course_id": 1,
      "created_by": 2,
      "title": "Latihan API",
      "instructions": "Kerjakan latihan sesuai materi.",
      "due_at": "2026-12-31T23:59:00.000000Z",
      "max_score": 100,
      "allow_late": true,
      "status": "published",
      "course": {
        "id": 1,
        "code": "IF101",
        "name": "Pengantar Informatika",
        "description": "Contoh deskripsi",
        "sks": 3,
        "lecturer_id": 2,
        "status": "active",
        "counts": {
          "materials": 2,
          "assignments": 1
        },
        "created_at": "2026-10-06T10:00:00+00:00",
        "lecturer": {
          "id": 2,
          "name": "Dosen Demo",
          "email": "dosen@kampuslms.test",
          "nim_nip": "NIP001",
          "role": "dosen"
        }
      }
    },
    "student": {
      "id": 5,
      "name": "Mahasiswa Demo",
      "email": "mahasiswa@kampuslms.test",
      "nim_nip": "MHS0001",
      "role": "mahasiswa"
    },
    "grade": null
  }
}
```

Untuk memberi atau memperbarui nilai, kirim `score` (angka 0–100) dan `feedback` opsional. Hanya dosen pengampu mata kuliah yang boleh menilai; dosen lain mendapat `403`.

Tanpa token endpoint grade mengembalikan `401`; pengguna bukan dosen dan dosen yang bukan pengampu menerima `403`; submission tidak ditemukan menghasilkan `404`; nilai di luar rentang atau input tidak valid menghasilkan `422`.

Contoh response `403` untuk dosen yang bukan pengampu:

```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

```bash
curl -X PUT http://localhost:8000/api/v1/submissions/1/grade \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $API_TOKEN" \
  -d '{"score":90,"feedback":"Pekerjaan baik."}'
```

Penilaian pertama mengembalikan `201`; permintaan berikutnya untuk submission yang sama memperbarui grade dan mengembalikan `200`. Keduanya memakai Resource submission dengan nilai berikut:

```json
{
  "data": {
    "id": 12,
    "assignment_id": 1,
    "user_id": 5,
    "file_path": "submissions/example.pdf",
    "original_name": "jawaban.pdf",
    "file_size": 2048,
    "note": "Jawaban latihan",
    "submitted_at": "2026-10-06T07:00:00.000000Z",
    "is_late": false,
    "assignment": {
      "id": 1,
      "course_id": 1,
      "created_by": 2,
      "title": "Latihan API",
      "instructions": "Kerjakan latihan sesuai materi.",
      "due_at": "2026-12-31T23:59:00.000000Z",
      "max_score": 100,
      "allow_late": true,
      "status": "published",
      "course": {
        "id": 1,
        "code": "IF101",
        "name": "Pengantar Informatika",
        "description": "Contoh deskripsi",
        "sks": 3,
        "lecturer_id": 2,
        "status": "active",
        "counts": {
          "materials": 2,
          "assignments": 1
        },
        "created_at": "2026-10-06T10:00:00+00:00",
        "lecturer": {
          "id": 2,
          "name": "Dosen Demo",
          "email": "dosen@kampuslms.test",
          "nim_nip": "NIP001",
          "role": "dosen"
        }
      }
    },
    "student": {
      "id": 5,
      "name": "Mahasiswa Demo",
      "email": "mahasiswa@kampuslms.test",
      "nim_nip": "MHS0001",
      "role": "mahasiswa"
    },
    "grade": {
      "id": 4,
      "submission_id": 12,
      "graded_by": 2,
      "score": "90.00",
      "feedback": "Pekerjaan baik.",
      "graded_at": "2026-10-06T07:30:00.000000Z",
      "grader": {
        "id": 2,
        "name": "Dosen Demo",
        "email": "dosen@kampuslms.test",
        "nim_nip": "NIP001",
        "role": "dosen"
      }
    }
  }
}
```

## Format error dan status

| Status | Arti |
|---|---|
| `401` | Token tidak ada/tidak valid |
| `403` | Sudah terautentikasi, tetapi role atau kepemilikan tidak sesuai |
| `404` | Resource tidak ditemukan |
| `422` | Validasi request gagal |
| `429` | Batas request per menit terlampaui |

Contoh error validasi:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### Notifikasi

| Method | URI | Akses | Hasil utama |
|---|---|---|---|
| `GET` | `/api/v1/notifications` | Pengguna terautentikasi | `200`, koleksi notifikasi milik pengguna |
| `POST` | `/api/v1/notifications/{id}/read` | Pemilik notifikasi | `200`, Resource notifikasi yang telah dibaca; `403` jika bukan milik pengguna |

```bash
curl 'http://localhost:8000/api/v1/notifications?page=1' \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"

curl -X POST http://localhost:8000/api/v1/notifications/NOTIFICATION_UUID/read \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Koleksi notifikasi memakai metadata kontrak yang sama. Respons penandaan dibaca berbentuk `{ "data": { ... } }`.

## Pengujian otorisasi dengan curl

Skrip `scripts/test-api.sh` menguji autentikasi, akses tanpa token, ownership course/tugas, route koleksi nested, pengumpulan submission nested, grade baru dan penilaian ulang, serta daftar notifikasi. Skrip membuat assignment dan submission sementara di database, lalu menghapus assignment tersebut beserta berkas submission-nya. Siapkan token Sanctum untuk mahasiswa yang mengikuti course, mahasiswa yang **tidak** mengikuti course tersebut, dua dosen berbeda, serta ID course yang dimiliki masing-masing dosen:

```bash
export STUDENT_TOKEN='<token-mahasiswa>'
export OTHER_STUDENT_TOKEN='<token-mahasiswa-tidak-terdaftar-di-course-A>'
export DOSEN_A_TOKEN='<token-dosen-pemilik>'
export DOSEN_B_TOKEN='<token-dosen-lain>'
export COURSE_ID='<id-course-milik-dosen-A>'
export OTHER_COURSE_ID='<id-course-milik-dosen-B>'
bash scripts/test-api.sh
```

Seeder demo menyediakan `mahasiswa2@kampuslms.test` sebagai mahasiswa yang tidak didaftarkan pada course demo; skrip memakai akun ini secara default dan menerima `OTHER_STUDENT_TOKEN` sebagai override. Request tanpa token dan variasi role/ownership yang tidak sesuai diharapkan menghasilkan `401`/`403`; hasil lulus menunjukkan status aktualnya. Jalankan `php artisan route:list --path=api` untuk melihat seluruh route API yang aktif.

## Jalur frontend Minggu 7–16

**Pilihan yang tercatat: Blade + CSS biasa + Vanilla JavaScript.** Catatan ini bukan bukti bahwa pilihan sudah disampaikan/didaftarkan kepada dosen. Untuk implementasi frontend berikutnya, Blade dipakai sebagai template/struktur HTML, styling ditempatkan di `resources/css/app.css`, dan interaksi UI ditulis di `resources/js/app.js` memakai API JavaScript browser. Laravel tetap menjadi backend dan API; Vite hanya memuat serta membangun berkas aset, bukan framework frontend. API tetap dapat diuji dengan `curl`.