<x-layout title="Rekap Nilai Dosen">
    <section class="grade-recap-page">
        <header class="page-header">
            <div>
                <h1 class="page-title">Rekap Nilai Mata Kuliah</h1>
                <p class="page-lead">
                    Ringkasan nilai mahasiswa untuk setiap tugas pada mata kuliah yang Anda ampu.
                </p>
            </div>
        </header>

        <div class="grade-recap-summary">
            <div class="grade-recap-summary-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="25" height="25" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19V5"/>
                    <path d="M4 19h17"/>
                    <path d="m7 15 4-4 3 2 5-6"/>
                </svg>
            </div>
            <div>
                <span class="grade-recap-summary-label">Total Mata Kuliah</span>
                <strong class="grade-recap-summary-value">{{ count($courses) }}</strong>
                <span class="grade-recap-summary-description">
                    Mata kuliah yang Anda ampu
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
                        <p>Rekap nilai mahasiswa berdasarkan tugas</p>
                    </div>

                    <span class="grade-recap-student-count">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="10" cy="7" r="4"/>
                            <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        {{ count($course['students']) }} mahasiswa
                    </span>
                </header>

                @if (count($course['assignments']) && count($course['students']))
                    <div class="grade-recap-table-wrap">
                        <table class="grade-recap-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="grade-recap-student-column">
                                        Mahasiswa
                                    </th>

                                    @foreach ($course['assignments'] as $assignment)
                                        <th scope="col" class="grade-recap-score-column">
                                            {{ $assignment['title'] }}
                                        </th>
                                    @endforeach

                                    <th scope="col" class="grade-recap-average-column">
                                        Rata-rata
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($course['students'] as $student)
                                    <tr>
                                        <th scope="row" class="grade-recap-student-name">
                                            {{ $student['student_name'] }}
                                        </th>

                                        @foreach ($course['assignments'] as $assignment)
                                            @php
                                                $grade = $student['grades'][$assignment['id']] ?? null;
                                            @endphp

                                            <td class="grade-recap-score">
                                                @if ($grade && $grade['score'] !== null)
                                                    {{ number_format((float) $grade['score'], 2) }}
                                                @else
                                                    <span class="grade-recap-no-score">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="grade-recap-average">
                                            @if ($student['average_score'] !== null)
                                                <span class="grade-recap-average-value">
                                                    {{ number_format((float) $student['average_score'], 2) }}
                                                </span>
                                            @else
                                                <span class="grade-recap-no-score">—</span>
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
                @elseif (! count($course['students']))
                    <div class="grade-recap-empty">
                        <strong>Belum Ada Mahasiswa</strong>
                        <p>Belum ada mahasiswa yang terdaftar pada mata kuliah ini.</p>
                    </div>
                @else
                    <div class="grade-recap-empty">
                        <strong>Belum Ada Tugas</strong>
                        <p>Belum ada tugas yang dapat ditampilkan dalam rekap nilai.</p>
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
                <p>Anda belum memiliki mata kuliah yang diampu.</p>
            </div>
        @endforelse
    </section>
</x-layout>