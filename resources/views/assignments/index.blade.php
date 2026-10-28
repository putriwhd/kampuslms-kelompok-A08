<x-layout>
    <x-slot:title>Tugas - {{ $course->name }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Daftar Tugas</h1>
        <p class="page-lead">
            Kelola tugas untuk mata kuliah
            <strong>{{ $course->name }}</strong>.
        </p>
    </div>

    @can('create', [\App\Models\Assignment::class, $course])
        <div class="page-actions">
            <a
                href="{{ route($role . '.courses.assignments.create', [
                    'course' => $course,
                    'as' => $role,
                ]) }}"
                class="primary-btn"
            >
                + Tambah Tugas
            </a>
        </div>
    @endcan

    <section class="content-card assignment-list-card">
        <div class="assignment-list-header">
            <div>
                <h2>Daftar Tugas</h2>
                <p>Informasi tugas dan batas waktu pengumpulan.</p>
            </div>

            <span class="assignment-count">
                {{ $assignments->total() }} tugas
            </span>
        </div>

        <div class="assignment-list">
            @forelse ($assignments as $assignment)
                <article class="assignment-item">
                    <div class="assignment-item-main">
                        <div class="assignment-item-title">
                            @can('view', $assignment)
                                <a
                                    href="{{ route($role . '.courses.assignments.scoped-show', [
                                        'course' => $course,
                                        'assignment' => $assignment,
                                        'as' => $role,
                                    ]) }}"
                                    class="assignment-title-link"
                                >
                                    {{ $assignment->title }}
                                </a>
                            @else
                                <h3>{{ $assignment->title }}</h3>
                            @endcan

                            @can('update', $assignment)
                                <span class="assignment-status {{ $assignment->status === 'published' ? 'is-published' : 'is-draft' }}">
                                    {{ $assignment->status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                                </span>
                            @endcan
                        </div>

                        <p class="assignment-due-date">
                            <span>Batas pengumpulan</span>
                            <strong>
                                {{ $assignment->due_at?->format('d M Y, H:i') ?? '-' }}
                            </strong>
                        </p>
                    </div>

                    <div class="assignment-item-actions">
                        @can('update', $assignment)
                            <a
                                href="{{ route($role . '.assignments.edit', [
                                    'assignment' => $assignment,
                                    'as' => $role,
                                ]) }}"
                                class="secondary-btn"
                            >
                                Edit
                            </a>
                        @endcan

                        @can('delete', $assignment)
                            <form
                                action="{{ route($role . '.assignments.destroy', [
                                    'assignment' => $assignment,
                                    'as' => $role,
                                ]) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus tugas ini?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="danger-btn">
                                    Hapus
                                </button>
                            </form>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="assignment-empty">
                    <h3>Belum Ada Tugas</h3>
                    <p>
                        Belum ada tugas untuk mata kuliah
                        {{ $course->name }}.
                    </p>

                    @can('create', [\App\Models\Assignment::class, $course])
                        <a
                            href="{{ route($role . '.courses.assignments.create', [
                                'course' => $course,
                                'as' => $role,
                            ]) }}"
                            class="primary-btn"
                        >
                            + Tambah Tugas
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if ($assignments->hasPages())
            <div class="assignment-pagination">
                {{ $assignments->links('vendor.pagination.kampuslms') }}
            </div>
        @endif
    </section>

    <div class="assignment-back-link">
        <a
            href="{{ route($role . '.courses.show', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            class="secondary-btn"
        >
            Kembali ke Mata Kuliah
        </a>
    </div>
</x-layout>