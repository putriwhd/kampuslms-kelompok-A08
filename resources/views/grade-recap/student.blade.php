<x-layout title="Rekap Nilai Saya">
    <section class="grade-recap-page">
        <header class="page-header">
            <div>
                <h1 class="page-title">Rekap Nilai Saya</h1>
                <p class="page-lead">
                    Lihat nilai, status pengumpulan, dan umpan balik dosen
                    untuk tugas pada mata kuliah yang Anda ikuti.
                </p>
            </div>
        </header>

        <div class="grade-student-summary">
            <div class="grade-student-summary-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="26" height="26" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 6h13"/>
                    <path d="M8 12h13"/>
                    <path d="M8 18h13"/>
                    <path d="M3 6h.01"/>
                    <path d="M3 12h.01"/>
                    <path d="M3 18h.01"/>
                </svg>
            </div>
            <div>
                <span class="grade-student-summary-label">Mata Kuliah Diikuti</span>
                <strong class="grade-student-summary-value">{{ count($courses) }}</strong>
                <span class="grade-student-summary-description">
                    Mata kuliah dalam rekap nilai Anda
                </span>
            </div>
        </div>

        @forelse ($courses as $course)
            <article class="grade-recap-course">
                <header class="grade-recap-course-header">
                    <div class="grade-recap-course-info">
                        <span class="grade-recap-course-code">
                            {{ $course['course_code'] }}
                        </span>
                        <h2>{{ $course['course_name'] }}</h2>
                        <p>Nilai dan umpan balik tugas</p>
                    </div>
                </header>

                @if (count($course['assignments']))
                    <div class="grade-recap-table-wrap">
                        <table class="grade-recap-table grade-student-table">
                            <thead>
                                <tr>
                                    <th scope="col">Tugas</th>
                                    <th scope="col">Nilai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Umpan Balik Dosen</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($course['assignments'] as $assignment)
                                    <tr>
                                        <th scope="row" class="grade-student-assignment">
                                            <span class="grade-student-assignment-title">
                                                {{ $assignment['title'] }}
                                            </span>
                                            <small class="grade-recap-max-score">
                                                Nilai maksimal:
                                                {{ $assignment['max_score'] }}
                                            </small>
                                        </th>

                                        <td class="grade-student-score-cell">
                                            @if ($assignment['score'] !== null)
                                                <strong class="grade-student-score">
                                                    {{ number_format((float) $assignment['score'], 2) }}
                                                </strong>
                                                <small class="grade-recap-date">
                                                    @if ($assignment['graded_at'])
                                                        Dinilai {{ $assignment['graded_at']->format('d M Y') }}
                                                    @else
                                                        Sudah dinilai
                                                    @endif
                                                </small>
                                            @else
                                                <span class="grade-recap-no-score">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($assignment['score'] !== null)
                                                <span class="grade-recap-status is-graded">
                                                    <span class="grade-status-dot"></span>
                                                    Sudah dinilai
                                                </span>
                                            @elseif ($assignment['submitted_at'])
                                                <span class="grade-recap-status is-pending">
                                                    <span class="grade-status-dot"></span>
                                                    Menunggu penilaian
                                                </span>
                                            @else
                                                <span class="grade-recap-status is-not-submitted">
                                                    <span class="grade-status-dot"></span>
                                                    Belum mengumpulkan
                                                </span>
                                            @endif
                                        </td>

                                        <td class="grade-student-feedback">
                                            @if ($assignment['feedback'])
                                                {{ $assignment['feedback'] }}
                                            @else
                                                <span class="grade-recap-no-score">Belum ada umpan balik</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="grade-recap-table-note">
                        Tanda — menunjukkan nilai yang belum tersedia.
                    </p>
                @else
                    <div class="grade-recap-empty">
                        <strong>Belum Ada Tugas</strong>
                        <p>Belum ada tugas yang dipublikasikan pada mata kuliah ini.</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="grade-recap-empty grade-recap-empty-main">
                <div class="grade-recap-empty-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="32" height="32" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.6"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                    </svg>
                </div>
                <strong>Belum Ada Mata Kuliah</strong>
                <p>Anda belum terdaftar pada mata kuliah aktif.</p>
            </div>
        @endforelse
    </section>
</x-layout>