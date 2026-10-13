<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div class="course-header">
        <div>
            <h1>Daftar Mata Kuliah</h1>

            <p>
                @if ($role === 'admin')
                    Sebagai Admin, Anda dapat mengelola data mata kuliah.
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
                class="btn-add"
            >
                <span>+</span>
                Tambah Mata Kuliah
            </a>
        @endcan
    </div>

    <div class="course-card">
        <div class="course-card-header">
            <div>
                <h2>Data Mata Kuliah</h2>
                <p>Daftar mata kuliah yang tersedia di KampusLMS.</p>
            </div>

            <div class="course-count">
                {{ $courses->total() }} Mata Kuliah
            </div>
        </div>

        <form
            method="GET"
            action="{{ route($role . '.courses.index', ['as' => $role]) }}"
            class="course-filters"
        >
            <input type="hidden" name="as" value="{{ $role }}">

            <label>
                <span>Cari kode atau nama</span>
                <input
                    type="search"
                    name="search"
                    value="{{ is_string(request('search')) ? request('search') : '' }}"
                    placeholder="Contoh: IF101 atau Pemrograman Web"
                >
            </label>

            <label>
                <span>Filter status</span>
                <select name="status">
                    <option value="">Semua status</option>
                    <option value="draft" @selected(request('status') === 'draft')>
                        Draft
                    </option>
                    <option value="active" @selected(request('status') === 'active')>
                        Aktif
                    </option>
                    <option value="archived" @selected(request('status') === 'archived')>
                        Arsip
                    </option>
                </select>
            </label>

            <button type="submit" class="btn-add">Terapkan</button>

            <a
                href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                class="btn-edit"
            >
                Reset
            </a>
        </form>

        <div class="table-container">
            <table class="course-table">
                <thead>
                    <tr>
                        <th width="15%">Kode</th>
                        <th width="27%">Nama Mata Kuliah</th>
                        <th width="23%">Dosen Pengampu</th>
                        <th width="15%" class="text-center">Status</th>
                        <th width="190px" class="text-center">Aksi</th>
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
                                        Draft
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
                            <td colspan="5" class="empty-data">
                                <div class="empty-icon">📚</div>

                                <strong>Belum ada data mata kuliah</strong>

                                <p>Data mata kuliah belum tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $courses->links() }}
    </div>
</x-layout>