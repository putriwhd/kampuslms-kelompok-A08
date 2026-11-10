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

        @if ($role === 'admin')
            <section class="content-card" style="margin-bottom: 24px;">
                <h2>Dosen Pengampu</h2>
                <p>Pilih dosen yang bertanggung jawab atas kelas ini.</p>
                <form action="{{ route('admin.courses.show', ['course' => $course, 'as' => 'admin']) }}" method="GET">
                    <input type="hidden" name="as" value="admin">
                    <label for="lecturer-search" class="form-label">Cari dosen</label>
                    <div class="form-actions">
                        <input
                            id="lecturer-search"
                            name="lecturer_search"
                            type="search"
                            class="form-input"
                            value="{{ request('lecturer_search') }}"
                            placeholder="Cari nama atau email dosen"
                        >
                        <button type="submit" class="secondary-btn">Cari</button>
                    </div>
                    {{ $lecturers->links('vendor.pagination.kampuslms') }}
                </form>
                <form action="{{ route('admin.courses.lecturer.update', ['course' => $course, 'as' => 'admin']) }}"
                      method="POST" class="course-create-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="lecturer_id" class="form-label">Dosen pengampu</label>
                        <select id="lecturer_id" name="lecturer_id" class="form-select" required>
                            @foreach ($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" @selected((int) old('lecturer_id', $course->lecturer_id) === $lecturer->id)>
                                    {{ $lecturer->name }} ({{ $lecturer->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('lecturer_id')
                            <small class="course-field-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="primary-btn">Simpan Dosen Pengampu</button>
                </form>
            </section>
        @endif

        @if (in_array($role, ['admin', 'dosen'], true))
            <section class="content-card" style="margin-bottom: 24px;">
                <h2>Pendaftaran Mahasiswa</h2>
                <p>Pilih mahasiswa yang akan terdaftar. Hilangkan pilihan untuk mengeluarkan mahasiswa dari kelas.</p>
                <form action="{{ route($role.'.courses.show', ['course' => $course, 'as' => $role]) }}"
                      method="GET" class="course-create-form">
                    <input type="hidden" name="as" value="{{ $role }}">
                    <div class="form-group">
                        <label for="student-search" class="form-label">Cari mahasiswa</label>
                        <input
                            id="student-search"
                            name="student_search"
                            type="search"
                            class="form-input"
                            value="{{ request('student_search') }}"
                            placeholder="Cari nama, NIM, atau email..."
                            autocomplete="off"
                            aria-controls="student-checkbox-list"
                        >
                        <button type="submit" class="secondary-btn">Cari</button>
                    </div>
                </form>
                <form action="{{ route($role.'.courses.students.update', ['course' => $course, 'as' => $role]) }}"
                      method="POST" class="course-create-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <span id="student-search-empty" class="form-help" role="status" hidden>
                            Tidak ada mahasiswa yang cocok dengan pencarian.
                        </span>
                        <div
                            id="student-checkbox-list"
                            class="student-checkbox-list"
                            style="max-height: 300px; overflow-y: auto; margin-top: 12px; padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 8px;"
                        >
                            @foreach ($selectedStudents as $studentOption)
                                <label
                                    class="student-checkbox-option"
                                    data-search="{{ \Illuminate\Support\Str::lower($studentOption->name.' '.$studentOption->email.' '.$studentOption->nim_nip) }}"
                                    for="student-{{ $studentOption->id }}"
                                    style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 4px; border-bottom: 1px solid #e2e8f0; cursor: pointer;"
                                >
                                    <input
                                        id="student-{{ $studentOption->id }}"
                                        type="checkbox"
                                        name="student_ids[]"
                                        value="{{ $studentOption->id }}"
                                        checked
                                    >
                                    <span>
                                        <strong style="display: block;">{{ $studentOption->name }}</strong>
                                        <small style="display: block; color: #64748b;">
                                            Email: {{ $studentOption->email }} · NIM: {{ $studentOption->nim_nip ?: '-' }}
                                        </small>
                                    </span>
                                </label>
                            @endforeach

                            @foreach ($availableStudents as $studentOption)
                                <label
                                    class="student-checkbox-option"
                                    data-search="{{ \Illuminate\Support\Str::lower($studentOption->name.' '.$studentOption->email.' '.$studentOption->nim_nip) }}"
                                    for="student-{{ $studentOption->id }}"
                                    style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 4px; border-bottom: 1px solid #e2e8f0; cursor: pointer;"
                                >
                                    <input
                                        id="student-{{ $studentOption->id }}"
                                        type="checkbox"
                                        name="student_ids[]"
                                        value="{{ $studentOption->id }}"
                                    >
                                    <span>
                                        <strong style="display: block;">{{ $studentOption->name }}</strong>
                                        <small style="display: block; color: #64748b;">
                                            Email: {{ $studentOption->email }} · NIM: {{ $studentOption->nim_nip ?: '-' }}
                                        </small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        {{ $availableStudents->links('vendor.pagination.kampuslms') }}
                        @error('student_ids')
                            <small class="course-field-error">{{ $message }}</small>
                        @enderror
                        @error('student_ids.*')
                            <small class="course-field-error">{{ $message }}</small>
                        @enderror
                        <small class="form-help">Mahasiswa terdaftar selalu ditampilkan di atas dan dicentang. Pilih mahasiswa tambahan dari hasil pencarian/halaman; mahasiswa terdaftar yang tidak dicentang akan dikeluarkan.</small>
                    </div>
                    <button type="submit" class="primary-btn">Simpan Pendaftaran Mahasiswa</button>
                </form>
            </section>

            <script>
                (() => {
                    const search = document.getElementById('student-search');
                    const options = Array.from(document.querySelectorAll('.student-checkbox-option'));
                    const emptyMessage = document.getElementById('student-search-empty');

                    if (!search || !emptyMessage) return;

                    search.addEventListener('input', () => {
                        const term = search.value.trim().toLocaleLowerCase();
                        let visibleCount = 0;

                        options.forEach((option) => {
                            const matches = option.dataset.search.includes(term);
                            option.hidden = !matches;
                            if (matches) visibleCount++;
                        });

                        emptyMessage.hidden = visibleCount > 0;
                    });
                })();
            </script>
        @endif

        {{-- ================= KARTU PERMINTAAN PENDAFTARAN MANDIRI ================= --}}
        @if ($role === 'dosen' && isset($pendingRequests))
            <section class="user-table-card" aria-labelledby="pending-enrollment-heading" style="margin-bottom: 24px;">
                <div class="user-table-header">
                    <div class="user-table-header-icon" aria-hidden="true" style="background: #e0e7ff; color: #4338ca;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <h2 id="pending-enrollment-heading">
                            Permintaan Pendaftaran Mandiri
                            @if ($pendingRequests->total() > 0)
                                <span style="background: #ef4444; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; margin-left: 8px;">
                                    {{ $pendingRequests->total() }} Permintaan
                                </span>
                            @endif
                        </h2>
                        <p>Mahasiswa yang mengajukan bergabung ke kelas ini melalui Daftar Kelas.</p>
                    </div>
                </div>

                @if ($pendingRequests->isEmpty())
                    <div style="padding: 24px; text-align: center; color: #64748b;">
                        Tidak ada permintaan pendaftaran mahasiswa yang tertunda saat ini.
                    </div>
                @else
                    <div class="user-table-wrapper">
                        <table class="user-table enrollment-table">
                            <thead>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>Email</th>
                                    <th>Waktu Pengajuan</th>
                                    <th class="text-center" style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingRequests as $req)
                                    <tr>
                                        <td class="member-name">{{ $req->student?->name }}</td>
                                        <td class="user-nim">{{ $req->student?->nim_nip ?: '-' }}</td>
                                        <td class="user-email">{{ $req->student?->email }}</td>
                                        <td style="font-size: 0.85rem; color: #64748b;">
                                            {{ $req->created_at?->diffForHumans() }}
                                        </td>
                                        <td class="text-center">
                                            <div style="display: flex; gap: 8px; justify-content: center;">
                                                <form action="{{ route('dosen.enrollment-requests.approve', $req) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="primary-btn" style="padding: 6px 12px; font-size: 0.825rem; background: #059669; border-color: #059669;">
                                                        ✓ Setujui
                                                    </button>
                                                </form>
                                                <form action="{{ route('dosen.enrollment-requests.reject', $req) }}" method="POST" onsubmit="return confirm('Tolak pendaftaran mahasiswa ini?')">
                                                    @csrf
                                                    <label for="rejection-reason-{{ $req->id }}" class="sr-only">Alasan penolakan</label>
                                                    <textarea
                                                        id="rejection-reason-{{ $req->id }}"
                                                        name="rejection_reason"
                                                        rows="2"
                                                        maxlength="1000"
                                                        required
                                                        placeholder="Alasan penolakan"
                                                        aria-label="Alasan penolakan"
                                                        style="width: 180px; max-width: 100%; margin-bottom: 6px;"
                                                    >{{ old('rejection_reason') }}</textarea>
                                                    @error('rejection_reason')
                                                        <p role="alert" style="color: #b91c1c; font-size: 0.8rem; margin: 0 0 6px;">{{ $message }}</p>
                                                    @enderror
                                                    <button type="submit" class="danger-btn" style="padding: 6px 12px; font-size: 0.825rem;">
                                                        ✕ Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="course-pagination">
                        {{ $pendingRequests->links('vendor.pagination.kampuslms') }}
                    </div>
                @endif
            </section>
        @endif

        <section class="user-table-card" aria-labelledby="enrolled-students-heading">
            <div class="user-table-header">
                <div class="user-table-header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <h2 id="enrolled-students-heading">Mahasiswa Terdaftar</h2>
                    <p>{{ $students->total() }} mahasiswa terdaftar di kelas ini.</p>
                </div>
            </div>

            @if ($students->isEmpty())
                <div class="user-empty">
                    <div class="empty-icon">🎓</div>
                    <strong>Belum Ada Mahasiswa Terdaftar</strong>
                    <p>Mahasiswa dapat memilih kelas ini melalui Daftar Kelas dan menunggu persetujuan dosen.</p>
                </div>
            @else
                <div class="user-table-wrapper">
                    <table class="user-table enrollment-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                @if ($canViewStudentDetails)
                                    <th>Email</th>
                                    <th>NIM</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td class="member-name">{{ $student->name }}</td>
                                    @if ($canViewStudentDetails)
                                        <td class="user-email">{{ $student->email }}</td>
                                        <td class="user-nim">{{ $student->nim_nip ?: '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="course-pagination">
                    {{ $students->links('vendor.pagination.kampuslms') }}
                </div>
            @endif
        </section>
    </div>
</x-layout>