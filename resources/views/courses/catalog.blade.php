<x-layout>
    <x-slot:title>Katalog Mata Kuliah</x-slot:title>

    <style>
        .catalog-header {
            margin-bottom: 24px;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
            gap: 20px;
        }

        .catalog-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            padding: 22px;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .catalog-card h2 {
            font-size: 1.15rem;
            line-height: 1.4;
            margin: 0 0 8px;
        }

        .catalog-quota {
            border: none;
            background: transparent;
            color: #2563eb;
            font-weight: 600;
            cursor: pointer;
            padding: 5px;
            border-radius: 6px;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .catalog-quota:hover {
            background: #eff6ff;
            text-decoration: underline;
        }

        .catalog-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-approved {
            background: #ecfdf5;
            color: #059669;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }

        .rejection-info {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.85rem;
            margin-bottom: 12px;
            overflow-wrap: anywhere;
        }

        .catalog-search {
            display: flex;
            gap: 10px;
            max-width: 550px;
            margin-bottom: 24px;
        }

        .catalog-search input {
            min-width: 0;
            flex: 1;
        }

        /* Modal daftar peserta */
        .students-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 12px;
            background: rgba(15, 23, 42, 0.6);
        }

        .students-modal.active {
            display: flex;
        }

        .students-modal-content {
            width: 100%;
            max-width: 550px;
            max-height: 85vh;
            overflow-y: auto;
            background: white;
            border-radius: 14px;
            padding: 20px;
            min-width: 0;
        }

        .students-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
        }

        .students-modal-header h2 {
            font-size: 1.15rem;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .students-close {
            border: none;
            background: #f1f5f9;
            color: #334155;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .students-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .students-table th,
        .students-table td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            overflow-wrap: anywhere;
        }

        .students-table th {
            background: #f8fafc;
            color: #475569;
        }

        .students-message {
            text-align: center;
            padding: 24px 10px;
            color: #64748b;
            font-size: 0.9rem;
        }

        [hidden] {
            display: none !important;
        }

        @media (max-width: 480px) {
            .catalog-card {
                padding: 16px;
            }

            .catalog-search {
                flex-wrap: wrap;
            }

            .catalog-search input {
                flex-basis: 100%;
            }

            .catalog-search button,
            .catalog-search a {
                flex: 1;
                text-align: center;
            }

            .students-modal-content {
                padding: 14px;
            }

            .catalog-card h2 {
                font-size: 1.05rem;
            }
        }

        @media (max-width: 319px) {
            .catalog-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .catalog-card {
                padding: 12px;
            }

            .catalog-card > div > div {
                flex-wrap: wrap;
            }

            .catalog-quota {
                font-size: 0.75rem;
            }

            .students-modal {
                padding: 6px;
            }
        }
    </style>

    {{-- Header --}}
    <div class="page-header catalog-header">
        <div>
            <h1 class="page-title">Katalog Mata Kuliah</h1>
            <p class="page-lead">
                Pilih dan ajukan pendaftaran mandiri pada mata kuliah semester berjalan.
            </p>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="notifications-alert notifications-alert-success"
             role="status"
             style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert"
             style="background:#fee2e2; color:#991b1b; padding:12px 18px; border-radius:10px; margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Ringkasan SKS --}}
    <section class="content-card"
             style="margin-bottom:24px; padding:20px 24px;">
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
            <div>
                <span style="font-size:0.85rem; font-weight:600; text-transform:uppercase; color:#64748b;">
                    Status Beban Studi
                </span>

                <h2 style="margin:4px 0 0; font-size:1.35rem; color:#0f172a;">
                    Beban SKS Anda:
                    <strong style="color:#2563eb;">
                        {{ $currentSks }} / {{ $maxSks }} SKS
                    </strong>
                </h2>

                <p style="margin:4px 0 0; font-size:0.9rem; color:#64748b;">
                    Sisa kuota:
                    <strong>{{ max(0, $maxSks - $currentSks) }} SKS</strong>
                </p>
            </div>

            <div style="min-width:0; max-width:280px; width:100%;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:6px;">
                    <span>Penggunaan SKS</span>
                    <span>
                        {{ $maxSks > 0 ? round(($currentSks / $maxSks) * 100) : 0 }}%
                    </span>
                </div>

                <div style="height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
                    <div style="
                        height:100%;
                        width:{{ $maxSks > 0 ? min(100, round(($currentSks / $maxSks) * 100)) : 0 }}%;
                        background:{{ $currentSks >= $maxSks ? '#ef4444' : '#2563eb' }};
                        border-radius:999px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pencarian --}}
    <form method="GET"
          action="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}"
          class="catalog-search">

        <input type="search"
               name="search"
               value="{{ request('search') }}"
               placeholder="Cari kode atau nama mata kuliah..."
               class="form-input">

        <button type="submit" class="primary-btn">Cari</button>

        @if (request('search'))
            <a href="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}"
               class="secondary-btn">
                Reset
            </a>
        @endif
    </form>

    {{-- Grid Katalog --}}
    <div class="catalog-grid">
        @forelse ($courses as $course)

            @php
                $isEnrolled = in_array($course->id, $enrolledCourseIds);
                $isPending = in_array($course->id, $pendingCourseIds);
                $isRejected = in_array($course->id, $rejectedCourseIds);

                $isFull = $course->students_count >= $maxCapacity;
                $isSksExceeded = ($currentSks + $course->sks) > $maxSks;

                // Mengambil alasan penolakan dari controller.
                $rejectionReason = $enrollmentRequests[$course->id]
                    ?? 'Alasan penolakan belum tersedia.';
            @endphp

            <article class="content-card catalog-card">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:12px;">
                        <span style="font-weight:700; font-size:0.85rem; padding:4px 8px; border-radius:6px; background:#f1f5f9; color:#334155;">
                            {{ $course->code }}
                        </span>

                        <span style="font-size:0.85rem; font-weight:600; padding:4px 10px; border-radius:999px; background:#e0f2fe; color:#0369a1;">
                            {{ $course->sks }} SKS
                        </span>
                    </div>

                    <h2>{{ $course->name }}</h2>

                    <p style="font-size:0.85rem; color:#64748b; margin:0 0 12px;">
                        Dosen:
                        <strong>{{ $course->lecturer?->name ?? 'Belum ditentukan' }}</strong>
                    </p>

                    <p style="font-size:0.875rem; color:#475569; margin:0 0 16px; line-height:1.5;">
                        {{ $course->description ?: 'Belum ada deskripsi untuk mata kuliah ini.' }}
                    </p>
                </div>

                <div style="border-top:1px solid #f1f5f9; padding-top:16px; margin-top:8px;">
                    {{-- Tombol kuota untuk membuka modal --}}
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:14px; font-size:0.825rem;">
                        <span style="color:#64748b;">Kuota Kelas:</span>

                        <button
                            type="button"
                            class="catalog-quota"
                            data-students-url="{{ route('courses.students.index', $course->id) }}"
                            data-course-name="{{ $course->name }}"
                            aria-label="Lihat peserta {{ $course->name }}">

                            {{ $course->students_count }} / {{ $maxCapacity }} Mahasiswa

                            @if ($isFull)
                                (Penuh)
                            @endif
                        </button>
                    </div>

                    {{-- Status dan aksi pendaftaran --}}
                    <div>
                        @if ($isEnrolled)
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                                <span class="catalog-status status-approved">
                                    ✓ Terdaftar
                                </span>

                                <a href="{{ route('mahasiswa.courses.show', ['course' => $course, 'as' => 'mahasiswa']) }}"
                                   class="primary-btn"
                                   style="padding:6px 14px; font-size:0.875rem;">
                                    Buka Kelas
                                </a>
                            </div>

                        @elseif ($isPending)
                            <div class="catalog-status status-pending"
                                 style="width:100%; text-align:center;">
                                ⏳ Menunggu Persetujuan
                            </div>

                        @elseif ($isRejected)
                            <div class="catalog-status status-rejected"
                                 style="margin-bottom:12px;">
                                ✕ Ditolak
                            </div>

                            {{-- Alert Alasan Penolakan --}}
                            <div class="rejection-info">
                                <strong>Alasan Penolakan:</strong>
                                <p style="margin:6px 0 0;">
                                    {{ $rejectionReason }}
                                </p>
                            </div>

                            {{-- Form Daftar Ulang --}}
                            <form action="{{ route('mahasiswa.courses.enroll', $course) }}"
                                  method="POST">
                                @csrf

                                <button type="submit"
                                        class="primary-btn"
                                        style="width:100%; padding:8px 12px; font-size:0.875rem;">
                                    Daftar Ulang
                                </button>
                            </form>

                        @elseif ($isFull)
                            <button type="button"
                                    disabled
                                    style="width:100%; padding:8px 12px; border-radius:8px; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;">
                                Kelas Penuh (Maks. {{ $maxCapacity }})
                            </button>

                        @elseif ($isSksExceeded)
                            <button type="button"
                                    disabled
                                    style="width:100%; padding:8px 12px; border-radius:8px; background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0;">
                                SKS Tidak Mencukupi
                            </button>

                        @else
                            <form action="{{ route('mahasiswa.courses.enroll', $course) }}"
                                  method="POST">
                                @csrf

                                <button type="submit"
                                        class="primary-btn"
                                        style="width:100%; padding:8px 12px; font-size:0.875rem;">
                                    + Daftar Mata Kuliah
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>

        @empty
            <div class="content-card"
                 style="grid-column:1 / -1; text-align:center; padding:48px 24px;">

                <p style="font-size:1.1rem; color:#64748b;">
                    Tidak ada mata kuliah aktif yang ditemukan.
                </p>

                @if (request('search'))
                    <a href="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}"
                       class="primary-btn">
                        Lihat Semua Mata Kuliah
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if (method_exists($courses, 'links'))
        <div style="margin-top:32px;">
            {{ $courses->links('vendor.pagination.kampuslms') }}
        </div>
    @endif

    {{-- Modal daftar peserta --}}
    <div id="studentsModal"
         class="students-modal"
         aria-hidden="true">

        <div class="students-modal-content"
             role="dialog"
             aria-modal="true"
             aria-labelledby="studentsModalTitle">

            <div class="students-modal-header">
                <div>
                    <h2 id="studentsModalTitle">Daftar Peserta Kelas</h2>

                    <p id="studentsCourseName"
                       style="font-size:0.85rem; color:#64748b; margin:5px 0 0;">
                    </p>
                </div>

                <button type="button"
                        id="closeStudentsModal"
                        class="students-close"
                        aria-label="Tutup modal">
                    ✕
                </button>
            </div>

            <div id="studentsLoading"
                 class="students-message"
                 role="status"
                 hidden>
                Memuat data peserta...
            </div>

            <div id="studentsError"
                 class="students-message"
                 role="alert"
                 style="color:#dc2626;"
                 hidden>
                Gagal mengambil data peserta. Silakan coba lagi.
            </div>

            <div id="studentsEmpty"
                 class="students-message"
                 hidden>
                Belum ada mahasiswa yang terdaftar pada kelas ini.
            </div>

            <div id="studentsTableWrapper"
                 class="students-table-wrapper"
                 hidden>

                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIM/NIP</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JavaScript Fetch API --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('studentsModal');
            const closeButton = document.getElementById('closeStudentsModal');

            const courseName = document.getElementById('studentsCourseName');

            const loading = document.getElementById('studentsLoading');
            const error = document.getElementById('studentsError');
            const empty = document.getElementById('studentsEmpty');

            const tableWrapper = document.getElementById('studentsTableWrapper');
            const tableBody = document.getElementById('studentsTableBody');

            const quotaButtons = document.querySelectorAll('.catalog-quota');

            function hideAllMessages() {
                loading.hidden = true;
                error.hidden = true;
                empty.hidden = true;
                tableWrapper.hidden = true;
            }

            function openModal() {
                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                closeButton.focus();
            }

            function closeModal() {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            async function fetchStudents(url) {
                hideAllMessages();
                tableBody.innerHTML = '';
                loading.hidden = false;

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('HTTP Error: ' + response.status);
                    }

                    const students = await response.json();

                    if (!Array.isArray(students)) {
                        throw new Error('Format data peserta tidak valid.');
                    }

                    loading.hidden = true;

                    if (students.length === 0) {
                        empty.hidden = false;
                        return;
                    }

                    students.forEach(function (student) {
                        const row = document.createElement('tr');

                        const nameCell = document.createElement('td');
                        const nimCell = document.createElement('td');

                        nameCell.textContent = student.name || '-';
                        nimCell.textContent = student.nim_nip || '-';

                        row.appendChild(nameCell);
                        row.appendChild(nimCell);

                        tableBody.appendChild(row);
                    });

                    tableWrapper.hidden = false;

                } catch (err) {
                    console.error('Gagal mengambil peserta:', err);

                    loading.hidden = true;
                    error.hidden = false;
                }
            }

            quotaButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const url = button.dataset.studentsUrl;
                    const name = button.dataset.courseName;

                    courseName.textContent = name;

                    openModal();
                    fetchStudents(url);
                });
            });

            closeButton.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        });
    </script>
</x-layout>
