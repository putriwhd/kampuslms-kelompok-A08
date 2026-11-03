<x-layout>
    <x-slot:title>Daftar Kelas</x-slot:title>

    <div class="page-header course-list-header">
        <div>
            <h1 class="page-title">Daftar Kelas</h1>
            <p class="page-lead">
                Pilih dan ajukan pendaftaran mandiri pada mata kuliah semester berjalan.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="notifications-alert notifications-alert-success" role="status" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" style="background:#fee2e2; color:#991b1b; padding:12px 18px; border-radius:10px; margin-bottom:20px; font-weight:500; border:1px solid #f87171;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Widget Ringkasan SKS Mahasiswa --}}
    <section class="content-card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
                <span style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">Status Beban Studi</span>
                <h2 style="margin: 4px 0 0; font-size: 1.35rem; color: #0f172a;">
                    Beban SKS Anda: <strong style="color: #2563eb;">{{ $currentSks }} / {{ $maxSks }} SKS</strong>
                </h2>
                <p style="margin: 4px 0 0; font-size: 0.9rem; color: #64748b;">
                    Sisa kuota yang dapat Anda ambil: <strong>{{ max(0, $maxSks - $currentSks) }} SKS</strong> lagi.
                </p>
            </div>

            <div style="min-width: 200px; max-width: 280px; width: 100%;">
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 600; margin-bottom: 6px; color: #475569;">
                    <span>Penggunaan SKS</span>
                    <span>{{ round(($currentSks / $maxSks) * 100) }}%</span>
                </div>
                <div style="height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                    <div style="height: 100%; width: {{ min(100, round(($currentSks / $maxSks) * 100)) }}%; background: {{ $currentSks >= $maxSks ? '#ef4444' : '#2563eb' }}; border-radius: 999px; transition: width 0.3s ease;"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}" style="margin-bottom: 24px; display: flex; gap: 12px; max-width: 500px;">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kode atau nama mata kuliah..."
            class="form-input"
            style="flex: 1;"
        >
        <button type="submit" class="primary-btn">Cari</button>
        @if (request('search'))
            <a href="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}" class="secondary-btn">Reset</a>
        @endif
    </form>

    {{-- Grid Kartu Mata Kuliah --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @forelse ($courses as $course)
            @php
                $isEnrolled = in_array($course->id, $enrolledCourseIds);
                $isPending = in_array($course->id, $pendingCourseIds);
                $isFull = $course->students_count >= $maxCapacity;
                $isSksExceeded = ($currentSks + $course->sks) > $maxSks;
            @endphp

            <article class="content-card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 22px; border-radius: 12px; transition: box-shadow 0.2s ease;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px;">
                        <span class="course-code" style="font-weight: 700; font-size: 0.85rem; padding: 4px 8px; border-radius: 6px; background: #f1f5f9; color: #334155;">
                            {{ $course->code }}
                        </span>
                        <span style="font-size: 0.85rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1;">
                            {{ $course->sks }} SKS
                        </span>
                    </div>

                    <h2 style="font-size: 1.15rem; margin: 0 0 8px; line-height: 1.4; color: #0f172a;">
                        {{ $course->name }}
                    </h2>

                    <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 12px; display: flex; align-items: center; gap: 6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Dosen: <strong>{{ $course->lecturer?->name ?? 'Belum ditentukan' }}</strong></span>
                    </p>

                    <p style="font-size: 0.875rem; color: #475569; margin: 0 0 16px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $course->description ?: 'Belum ada deskripsi untuk mata kuliah ini.' }}
                    </p>
                </div>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; font-size: 0.825rem;">
                        <span style="color: #64748b;">Kuota Kelas:</span>
                        <span style="font-weight: 600; color: {{ $isFull ? '#ef4444' : '#10b981' }};">
                            {{ $course->students_count }} / {{ $maxCapacity }} Mahasiswa
                            @if ($isFull) (Penuh) @endif
                        </span>
                    </div>

                    <div>
                        @if ($isEnrolled)
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: #059669; background: #ecfdf5; padding: 6px 12px; border-radius: 8px;">
                                    ✓ Terdaftar
                                </span>
                                <a href="{{ route('mahasiswa.courses.show', ['course' => $course, 'as' => 'mahasiswa']) }}" class="primary-btn" style="padding: 6px 14px; font-size: 0.875rem;">
                                    Buka Kelas
                                </a>
                            </div>
                        @elseif ($isPending)
                            <button type="button" disabled style="width: 100%; padding: 8px 12px; font-size: 0.875rem; font-weight: 600; border-radius: 8px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; cursor: not-allowed; text-align: center;">
                                ⏳ Menunggu Persetujuan
                            </button>
                        @elseif ($isFull)
                            <button type="button" disabled style="width: 100%; padding: 8px 12px; font-size: 0.875rem; font-weight: 600; border-radius: 8px; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; cursor: not-allowed; text-align: center;">
                                Kelas Penuh (Maks. {{ $maxCapacity }})
                            </button>
                        @elseif ($course->students_count > 0)
                            <p role="status" style="margin: 0; padding: 8px 12px; font-size: 0.875rem; font-weight: 600; border-radius: 8px; background: #f1f5f9; color: #475569; text-align: center;">
                                Pendaftaran langsung melalui admin/dosen
                            </p>
                        @elseif ($isSksExceeded)
                            <button type="button" disabled title="Mendaftar mata kuliah ini akan melebihi batas {{ $maxSks }} SKS Anda" style="width: 100%; padding: 8px 12px; font-size: 0.875rem; font-weight: 600; border-radius: 8px; background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; cursor: not-allowed; text-align: center;">
                                SKS Tidak Mencukupi
                            </button>
                        @else
                            <form action="{{ route('mahasiswa.courses.enroll', $course) }}" method="POST">
                                @csrf
                                <button type="submit" class="primary-btn" style="width: 100%; padding: 8px 12px; font-size: 0.875rem; text-align: center;">
                                    + Daftar Kelas
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="content-card" style="grid-column: 1 / -1; text-align: center; padding: 48px 24px;">
                <p style="font-size: 1.1rem; color: #64748b; margin: 0 0 12px;">Tidak ada mata kuliah aktif yang ditemukan.</p>
                @if (request('search'))
                    <a href="{{ route('mahasiswa.catalog.index', ['as' => 'mahasiswa']) }}" class="primary-btn">Lihat Semua Mata Kuliah</a>
                @endif
            </div>
        @endforelse
    </div>

    @if (method_exists($courses, 'links'))
        <div style="margin-top: 32px;">
            {{ $courses->links('vendor.pagination.kampuslms') }}
        </div>
    @endif
</x-layout>
