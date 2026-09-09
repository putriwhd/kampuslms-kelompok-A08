<x-layout title="{{ $course['name'] }}">

    <h1>{{ $course['name'] }}</h1>

    <p>
        <strong>Kode:</strong>
        {{ $course['code'] }}
    </p>

    <p>
        <strong>SKS:</strong>
        {{ $course['sks'] }}
    </p>

    <p>
        <strong>Dosen:</strong>
        {{ $course['lecturer'] }}
    </p>

    <p>
        <strong>Deskripsi:</strong>
        {{ $course['description'] }}
    </p>

    <br>

    <a href="{{ route('courses.index') }}">
        ← Kembali ke Daftar Mata Kuliah
    </a>

</x-layout>