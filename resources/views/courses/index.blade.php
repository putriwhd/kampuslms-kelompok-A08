<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div class="page-header course-list-header">
        <div>
            <h1 class="page-title">Daftar Mata Kuliah</h1>

            <p class="page-lead">
                @if ($role === 'admin')
                    Kelola data mata kuliah yang tersedia di KampusLMS.
                @elseif ($role === 'dosen')
                    Berikut mata kuliah yang ditugaskan kepada Anda sebagai dosen pengampu.
                @else
                    Berikut mata kuliah yang Anda ikuti. Daftar ini hanya memuat mata kuliah tempat Admin mendaftarkan akun Anda.
                @endif
            </p>
        </div>

        @can('create', \App\Models\Course::class)
            <a
                href="{{ route($role . '.courses.create', ['as' => $role]) }}"
                class="primary-btn course-add-btn"
            >
                <span aria-hidden="true">+</span>
                Tambah Mata Kuliah
            </a>
        @endcan
    </div>

    <section class="content-card course-list-card">
        <div class="course-list-card-header">
            <div>
                <h2>Data Mata Kuliah</h2>
                <p>Daftar mata kuliah yang tersedia di KampusLMS.</p>
            </div>

            <span class="course-count">
                {{ $courses->total() }} Mata Kuliah
            </span>
        </div>

        <form
            method="GET"
            action="{{ route($role . '.courses.index', ['as' => $role]) }}"
            class="course-filters"
        >
            <input type="hidden" name="as" value="{{ $role }}">

            <div class="course-filter-field course-search-field">
                <label for="search" class="form-label">
                    Cari Mata Kuliah
                </label>

                <input
                    id="search"
                    type="search"
                    name="search"
                    class="form-input"
                    value="{{ is_string(request('search')) ? request('search') : '' }}"
                    placeholder="Kode atau nama mata kuliah"
                >
            </div>

            <div class="course-filter-field">
                <label for="status-filter" class="form-label">
                    Filter Status
                </label>

                <select id="status-filter" name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>
                        Draf
                    </option>
                    <option value="active" @selected(request('status') === 'active')>
                        Aktif
                    </option>
                    <option value="archived" @selected(request('status') === 'archived')>
                        Arsip
                    </option>
                </select>
            </div>

            <div class="course-filter-actions">
                <button type="submit" class="primary-btn">
                    Terapkan
                </button>

                <a
                    href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                    class="secondary-btn"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="table-container course-table-container">
            <table class="course-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Dosen Pengampu</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>
                                <span class="course-code">
                                    {{ $course->code }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route($role . '.courses.show', [
                                        'course' => $course,
                                        'as' => $role,
                                    ]) }}"
                                    class="course-name"
                                >
                                    {{ $course->name }}
                                </a>
                            </td>

                            <td>
                                <span class="lecturer-name">
                                    {{ $course->lecturer?->name ?? '-' }}
                                </span>
                            </td>

                            <td class="text-center">
                                @php
                                    $status = strtolower($course->status ?? 'draft');
                                @endphp

                                @if ($status === 'active')
                                    <span class="status-badge status-active">
                                        Aktif
                                    </span>
                                @elseif ($status === 'draft')
                                    <span class="status-badge status-draft">
                                        Draf
                                    </span>
                                @elseif ($status === 'archived')
                                    <span class="status-badge status-archive">
                                        Arsip
                                    </span>
                                @else
                                    <span class="status-badge status-unknown">
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <a
                                        href="{{ route($role . '.courses.show', [
                                            'course' => $course,
                                            'as' => $role,
                                        ]) }}"
                                        class="btn-view"
                                    >
                                        Lihat
                                    </a>

                                    @can('update', $course)
                                        <a
                                            href="{{ route($role . '.courses.edit', [
                                                'course' => $course,
                                                'as' => $role,
                                            ]) }}"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>
                                    @endcan

                                    @can('delete', $course)
                                        <form
                                            action="{{ route($role . '.courses.destroy', [
                                                'course' => $course,
                                                'as' => $role,
                                            ]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="course-empty-state">
                                    <span class="course-empty-icon" aria-hidden="true">
                                        📚
                                    </span>

                                    <strong>Belum Ada Data Mata Kuliah</strong>

                                    <p>
                                        Belum ada mata kuliah yang sesuai dengan
                                        pencarian atau filter yang dipilih.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($courses->hasPages())
            <div class="course-pagination">
                {{ $courses->withQueryString()->links('vendor.pagination.kampuslms') }}
            </div>
        @endif
    </section>
</x-layout>