<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    {{-- HEADER --}}
    <div class="course-header">
        <div>
            <h1>Daftar Mata Kuliah</h1>

            <p>
                @if ($role === 'admin')
                    Sebagai Admin, Anda dapat mengelola data mata kuliah.
                @elseif ($role === 'dosen')
                    Sebagai Dosen, Anda dapat melihat daftar mata kuliah.
                @else
                    Sebagai Mahasiswa, Anda dapat melihat daftar mata kuliah yang tersedia.
                @endif
            </p>
        </div>

        @if ($role === 'admin')
            <a href="{{ route('courses.create', ['as' => $role]) }}" class="btn-add">
                <span>+</span>
                Tambah Mata Kuliah
            </a>
        @endif
    </div>


    {{-- PESAN BERHASIL --}}
    @if (session('success'))
        <div class="alert-success">
            <span>✓</span>
            {{ session('success') }}
        </div>
    @endif


    {{-- CARD TABEL --}}
    <div class="course-card">

        {{-- CARD HEADER --}}
        <div class="course-card-header">
            <div>
                <h2>Data Mata Kuliah</h2>
                <p>Daftar mata kuliah yang tersedia di KampusLMS.</p>
            </div>

            <div class="course-count">
                {{ $courses->total() }} Mata Kuliah
            </div>
        </div>


        {{-- TABLE --}}
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

                            {{-- KODE --}}
                            <td>
                                <span class="course-code">
                                    {{ $course->code }}
                                </span>
                            </td>


                            {{-- NAMA --}}
                            <td>
                                <a
                                    href="{{ route('courses.show', [$course->id, 'as' => $role]) }}"
                                    class="course-name"
                                >
                                    {{ $course->name ?? $course->title }}
                                </a>
                            </td>


                            {{-- DOSEN --}}
                            <td>
                                <span class="lecturer-name">
                                    {{ $course->lecturer->name ?? '-' }}
                                </span>
                            </td>


                            {{-- STATUS --}}
                            <td class="text-center">

                                @php
                                    $status = strtolower($course->status ?? 'draft');
                                @endphp

                                @if ($status === 'active')

                                    <span class="status-badge status-active">
                                        Active
                                    </span>

                                @elseif ($status === 'draft')

                                    <span class="status-badge status-draft">
                                        Draft
                                    </span>

                                @elseif ($status === 'archive')

                                    <span class="status-badge status-archive">
                                        Archive
                                    </span>

                                @else

                                    <span class="status-badge status-unknown">
                                        {{ ucfirst($status) }}
                                    </span>

                                @endif

                            </td>


                            {{-- AKSI --}}
                            <td>
                                <div class="action-buttons">

                                    <a
                                        href="{{ route('courses.show', [$course->id, 'as' => $role]) }}"
                                        class="btn-view"
                                    >
                                        Lihat
                                    </a>

                                    @if ($role === 'admin')

                                        <a
                                            href="{{ route('courses.edit', [$course->id, 'as' => $role]) }}"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('courses.destroy', [$course->id, 'as' => $role]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn-delete"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty-data">

                                <div class="empty-icon">📚</div>

                                <strong>
                                    Belum ada data mata kuliah
                                </strong>

                                <p>
                                    Data mata kuliah belum tersedia.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

{{-- PAGINATION --}}
<div class="pagination">
  {{ $courses->withQueryString()->links() }}
</div>

    {{-- STYLE --}}
    <style>

        /* =========================
           HEADER
        ========================= */

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 28px;
        }

        .course-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .course-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================
           BUTTON TAMBAH
        ========================= */

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 10px 16px;

            background: var(--blue);
            color: var(--white);

            border-radius: 8px;
            text-decoration: none;

            font-size: 13px;
            font-weight: 600;

            white-space: nowrap;

            transition: var(--transition);
        }

        .btn-add span {
            font-size: 18px;
            line-height: 1;
            font-weight: 400;
        }

        .btn-add:hover {
            background: var(--blue-dark);
            transform: translateY(-1px);
        }


        /* =========================
           ALERT
        ========================= */

        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;
            padding: 12px 16px;

            background: #f0fdf4;
            color: #15803d;

            border: 1px solid #bbf7d0;
            border-radius: 8px;

            font-size: 14px;
        }

        .alert-success span {
            font-weight: 700;
        }


        /* =========================
           CARD
        ========================= */

        .course-card {
            overflow: hidden;

            background: var(--white);

            border: 1px solid var(--border);
            border-radius: var(--radius);

            box-shadow: var(--shadow);
        }


        /* =========================
           CARD HEADER
        ========================= */

        .course-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;

            padding: 20px 24px;

            border-bottom: 1px solid var(--border);
        }

        .course-card-header h2 {
            margin: 0 0 5px;

            color: var(--blue-dark);

            font-size: 18px;
            font-weight: 700;
        }

        .course-card-header p {
            margin: 0;

            color: var(--muted);

            font-size: 13px;
        }

        .course-count {
            padding: 6px 11px;

            background: var(--blue-soft);
            color: var(--blue-dark);

            border-radius: 6px;

            font-size: 12px;
            font-weight: 600;

            white-space: nowrap;
        }


        /* =========================
           TABLE
        ========================= */

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .course-table {
            width: 100%;

            border-collapse: collapse;

            font-size: 14px;
        }


        /* HEADER TABLE */

        .course-table thead {
            background: var(--blue-soft);
        }

        .course-table th {
            padding: 13px 20px;

            color: var(--blue-dark);

            font-size: 12px;
            font-weight: 700;

            text-align: left;

            border-bottom: 1px solid var(--border);

            white-space: nowrap;
        }

        .course-table th.text-center {
            text-align: center;
        }


        /* ISI TABLE */

        .course-table td {
            padding: 15px 20px;

            color: var(--text);

            border-bottom: 1px solid var(--border);

            vertical-align: middle;
        }

        .course-table tbody tr:last-child td {
            border-bottom: none;
        }

        .course-table tbody tr {
            transition: background 0.15s ease;
        }

        .course-table tbody tr:hover {
            background: #f8fafc;
        }


        /* =========================
           KODE
        ========================= */

        .course-code {
            display: inline-block;

            padding: 5px 9px;

            background: var(--blue-soft);
            color: var(--blue-dark);

            border-radius: 5px;

            font-size: 12px;
            font-weight: 700;
        }


        /* =========================
           NAMA MATA KULIAH
        ========================= */

        .course-name {
            color: var(--blue-dark);

            font-weight: 600;

            text-decoration: none;

            transition: var(--transition);
        }

        .course-name:hover {
            color: var(--blue);
        }


        /* =========================
           DOSEN
        ========================= */

        .lecturer-name {
            color: var(--muted);
        }


        /* =========================
           STATUS
        ========================= */

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-width: 70px;

            padding: 5px 10px;

            border-radius: 6px;

            font-size: 11px;
            font-weight: 700;

            white-space: nowrap;
        }


        /* ACTIVE */

        .status-active {
            background: #dcfce7;
            color: #15803d;
        }


        /* DRAFT */

        .status-draft {
            background: #fef3c7;
            color: #b45309;
        }


        /* ARCHIVE */

        .status-archive {
            background: #e5e7eb;
            color: #4b5563;
        }


        /* STATUS LAIN */

        .status-unknown {
            background: #f1f5f9;
            color: #475569;
        }


        /* =========================
           ACTION
        ========================= */

        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;

            gap: 6px;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-view,
        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-width: 50px;

            padding: 6px 10px;

            border-radius: 6px;

            font-size: 12px;
            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition: var(--transition);
        }


        /* LIHAT */

        .btn-view {
            background: var(--blue-soft);
            color: var(--blue-dark);
        }

        .btn-view:hover {
            background: var(--blue-light);
        }


        /* EDIT */

        .btn-edit {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-edit:hover {
            background: #e2e8f0;
        }


        /* HAPUS */

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;

            border: none;
        }

        .btn-delete:hover {
            background: #fee2e2;
        }


        /* =========================
           EMPTY DATA
        ========================= */

        .empty-data {
            padding: 50px 20px !important;

            text-align: center;

            color: var(--muted) !important;
        }

        .empty-icon {
            margin-bottom: 10px;

            font-size: 30px;
            opacity: 0.7;
        }

        .empty-data strong {
            display: block;

            margin-bottom: 4px;

            color: var(--text);

            font-size: 14px;
        }

        .empty-data p {
            margin: 0;

            font-size: 13px;
        }


        /* =========================
           PAGINATION
        ========================= */

        .pagination {
            margin-top: 20px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .course-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }

            .course-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .course-table th,
            .course-table td {
                padding: 12px 14px;
            }

            .action-buttons {
                justify-content: flex-start;
            }

        }

    </style>

</x-layout>