# Bukti Keamanan Produksi

Bagian ini mencatat pemeriksaan keamanan produksi yang telah dilakukan untuk
milestone M4. Pemeriksaan dilakukan pada 11 November 2026 terhadap:

`https://kampuslms-kelompok-a08-production-66912567.up.railway.app`

## Pemeriksaan path berkas sensitif

Pemeriksaan dilakukan menggunakan `curl -I` dari Git Bash. Ketiga path
mengembalikan HTTP `404`.

| Path | Hasil `curl -I` | Status |
|---|---|---|
| `/.env` | `HTTP/1.1 404 Not Found` | Sesuai harapan |
| `/storage/logs/laravel.log` | `HTTP/1.1 404 Not Found` | Sesuai harapan |
| `/composer.json` | `HTTP/1.1 404 Not Found` | Sesuai harapan |

### Keluaran curl aktual

`/.env`:

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:43:39 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: DNfQdQUKRuSFnqI9lt7tkg
x-hikari-trace: sin1.nzn2
x-railway-edge: sin1
Connection: keep-alive
```

`/storage/logs/laravel.log`:

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:43:55 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: B1u2eDPkQbOQdp_OnpoFkQ
x-hikari-trace: sin1.462z
x-railway-edge: sin1
Connection: keep-alive
```

`/composer.json`:

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:45:38 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: vIILKc07RiaMiw8T9I3ezw
x-hikari-trace: sin1.d1nj
x-railway-edge: sin1
Connection: keep-alive
```

## Uji otorisasi API

Skrip `scripts/test-authz.sh` lulus seluruh skenario saat dijalankan terhadap
`http://localhost:8000/api/v1`. Ini adalah hasil lokal, bukan hasil produksi.
Satu percobaan terhadap server produksi berhenti pada login awal Admin dengan
HTTP `422` karena kredensial `admin@kampuslms.test` ditolak:

```text
Gagal login sebagai admin@kampuslms.test (HTTP 422).
{"message":"Data yang diberikan tidak valid.","errors":{"email":["Email atau kata sandi yang Anda masukkan salah."]}}
```

Tidak ada skenario otorisasi yang dijalankan dan skrip berhenti sebelum membuat
atau menghapus tugas/materi. Kredensial demo bawaan tidak dapat diasumsikan
berlaku di produksi. Pengujian produksi masih menunggu akun uji khusus dengan
kredensial valid.

## Batas cakupan

Pemeriksaan tiga path di atas tidak membuktikan bahwa seluruh checklist
keamanan produksi telah terpenuhi. `docs/keamanan.md` masih mencatat bahwa
endpoint pengumpulan submission belum memeriksa status tugas `published`;
perilaku endpoint tersebut perlu ditangani atau diverifikasi terpisah.
