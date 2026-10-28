<x-layout>
    <x-slot:title>{{ $course->name }}</x-slot:title>

    @php
        $status = strtolower($course->status ?? 'draft');
        $statusMap = [
            'active'   => ['Aktif', 'status-active'],
            'draft'    => ['Draf', 'status-draft'],
            'archived' => ['Arsip', 'status-archive'],
        ];
        [$statusLabel, $statusClass] = $statusMap[$status] ?? [ucfirst($status), 'status-unknown'];
    @endphp

    <div class="course-page">

        <div class="page-header">
            <h1 class="page-title">Detail Mata Kuliah</h1>
            <p class="page-lead">Detail informasi dan pengelolaan mata kuliah KampusLMS.</p>
        </div>

        {{-- ================= KARTU 1: DETAIL ================= --}}
        <section class="user-detail-card">
            <div class="user-detail-header">
                <div class="user-detail-identity">
                    <div class="user-detail-avatar">{{ strtoupper(substr($course->code, 0, 2)) }}</div>
                    <div class="user-detail-heading">
                        <h2>{{ $course->name }}</h2>
                        <p>Informasi mata kuliah</p>
                    </div>
                </div>
                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="user-detail-section-heading">
                <h3>Informasi Mata Kuliah</h3>
                <p>Rincian kode, beban studi, dan dosen pengampu.</p>
            </div>

            <dl class="user-detail-list">
                <div class="user-detail-row">
                    <dt class="user-detail-label">
                        <span class="user-detail-field-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </span>
                        Kode Mata Kuliah
                    </dt>
                    <dd class="user-detail-value">{{ $course->code }}</dd>
                </div>

                <div class="user-detail-row">
                    <dt class="user-detail-label">
                        <span class="user-detail-field-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </span>
                        Jumlah SKS
                    </dt>
                    <dd class="user-detail-value">{{ $course->sks }} SKS</dd>
                </div>

                <div class="user-detail-row">
                    <dt class="user-detail-label">
                        <span class="user-detail-field-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        Dosen Pengampu
                    </dt>
                    <dd class="user-detail-value">{{ $course->lecturer?->name ?? '-' }}</dd>
                </div>

                <div class="user-detail-row">
                    <dt class="user-detail-label">
                        <span class="user-detail-field-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg>
                        </span>
                        Deskripsi
                    </dt>
                    <dd class="user-detail-value">
                        {{ $course->description ?: 'Belum ada deskripsi untuk mata kuliah ini.' }}
                    </dd>
                </div>
            </dl>

            <div class="user-detail-section-heading">
                <h3>Materi dan Tugas</h3>
                <p>Akses materi pembelajaran dan daftar tugas pada mata kuliah ini.</p>
            </div>

            <div class="course-action-grid">
                @can('viewAny', \App\Models\Material::class)
                    <a href="{{ route($role . '.courses.materials.index', ['course' => $course, 'as' => $role]) }}"
                       class="course-action-card is-material">
                        <span class="course-action-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </span>
                        <span class="course-action-text">
                            <strong>Lihat Materi</strong>
                            <small>Buka materi pembelajaran</small>
                        </span>
                        <span class="course-action-arrow" aria-hidden="true">→</span>
                    </a>
                @endcan

                @can('viewAny', \App\Models\Assignment::class)
                    <a href="{{ route($role . '.courses.assignments.index', ['course' => $course, 'as' => $role]) }}"
                       class="course-action-card is-assignment">
                        <span class="course-action-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </span>
                        <span class="course-action-text">
                            <strong>Lihat Tugas</strong>
                            <small>Buka daftar tugas</small>
                        </span>
                        <span class="course-action-arrow" aria-hidden="true">→</span>
                    </a>
                @endcan
            </div>

            <div class="user-detail-actions">
                <a href="{{ route($role . '.courses.index', ['as' => $role]) }}" class="btn-kembali user-detail-back">
                    ← Kembali ke Daftar Mata Kuliah
                </a>
            </div>
        </section>

        {{-- ================= KARTU 2: PENDAFTARAN MAHASISWA ================= --}}
        @if (in_array($role, ['admin', 'dosen'], true))
            <section class="user-table-card" aria-labelledby="enrollment-heading">
                <div class="user-table-header">
                    <div class="user-table-header-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M19 8v6"/><path d="M16 11h6"/></svg>
                    </div>
                    <div>
                        <h2 id="enrollment-heading">Pendaftaran Mahasiswa</h2>
                        <p>Centang mahasiswa yang boleh mengikuti mata kuliah ini. Menyimpan akan menggantikan daftar pendaftaran yang tersimpan.</p>
                    </div>
                </div>

                <form action="{{ route($role . '.courses.students.update', ['course' => $course, 'as' => $role]) }}"
                      method="POST" id="enrollmentForm">
                    @csrf
                    @method('PUT')

                    @if ($students->isEmpty())
                        <div class="user-empty">
                            <div class="empty-icon">🎓</div>
                            <strong>Belum Ada Akun Mahasiswa</strong>
                            <p>Tambahkan akun mahasiswa terlebih dahulu sebelum mendaftarkannya ke mata kuliah ini.</p>
                        </div>
                    @else
                        @error('student_ids')
                            <div class="global-alert global-alert-error" role="alert" style="margin: 20px 24px 0;">
                                <span class="global-alert-icon">!</span> {{ $message }}
                            </div>
                        @enderror

                        <div class="user-filter-form">
                            <input type="search" id="studentSearch" class="form-input" placeholder="Cari nama, email, atau NIM...">
                            <span class="enrollment-counter">
                                <strong id="selectedCount">0</strong> dari {{ $students->count() }} dipilih
                            </span>
                        </div>

                        <div class="user-table-wrapper">
                            <table class="user-table enrollment-table">
                                <thead>
                                    <tr>
                                        <th class="col-check">
                                            <input type="checkbox" id="checkAll" aria-label="Pilih semua mahasiswa">
                                        </th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>NIM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr data-search="{{ strtolower($student->name . ' ' . $student->email . ' ' . $student->nim_nip) }}">
                                            <td class="col-check">
                                                <input type="checkbox"
                                                       name="student_ids[]"
                                                       value="{{ $student->id }}"
                                                       class="student-check"
                                                       aria-label="Pilih {{ $student->name }}"
                                                       @checked(in_array($student->id, old('student_ids', $enrolledStudentIds)))>
                                            </td>
                                            <td class="member-name">{{ $student->name }}</td>
                                            <td class="user-email">{{ $student->email }}</td>
                                            <td class="user-nim">{{ $student->nim_nip ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                    <tr id="noResultRow" hidden>
                                        <td colspan="4" class="user-empty">Tidak ada mahasiswa yang cocok.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="user-form-actions enrollment-actions">
                            <button type="submit" class="btn-submit">Simpan Pendaftaran</button>
                        </div>
                    @endif
                </form>
            </section>
        @endif
    </div>

    <script>
        (function () {
            const form = document.getElementById('enrollmentForm');
            if (!form) return;

            const checks   = Array.from(form.querySelectorAll('.student-check'));
            const checkAll = document.getElementById('checkAll');
            const counter  = document.getElementById('selectedCount');
            const search   = document.getElementById('studentSearch');
            const rows     = Array.from(form.querySelectorAll('tbody tr[data-search]'));
            const noResult = document.getElementById('noResultRow');

            function refresh() {
                const checked = checks.filter(c => c.checked).length;
                counter.textContent = checked;
                checkAll.checked = checked === checks.length && checks.length > 0;
                checkAll.indeterminate = checked > 0 && checked < checks.length;
                checks.forEach(c => c.closest('tr').classList.toggle('is-selected', c.checked));
            }

            checkAll.addEventListener('change', function () {
                // hanya baris yang sedang terlihat (hasil pencarian)
                checks.forEach(c => { if (!c.closest('tr').hidden) c.checked = checkAll.checked; });
                refresh();
            });

            checks.forEach(c => c.addEventListener('change', refresh));

            // klik di mana saja pada baris = centang
            rows.forEach(function (row) {
                row.addEventListener('click', function (e) {
                    if (e.target.tagName === 'INPUT') return;
                    const box = row.querySelector('.student-check');
                    box.checked = !box.checked;
                    refresh();
                });
            });

            search.addEventListener('input', function () {
                const q = search.value.trim().toLowerCase();
                let visible = 0;
                rows.forEach(function (row) {
                    const show = q === '' || row.dataset.search.includes(q);
                    row.hidden = !show;
                    if (show) visible++;
                });
                noResult.hidden = visible !== 0;
            });

            refresh();
        })();
    </script>
</x-layout>