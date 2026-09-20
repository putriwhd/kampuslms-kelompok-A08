<<<<<<< HEAD
```php
<x-layout>

    <x-slot:title>Edit Mata Kuliah</x-slot:title>

    <div class="course-edit-header">

        <h1>Edit Mata Kuliah</h1>
        <p>Perbarui informasi mata kuliah yang tersedia di KampusLMS.</p>

    </div>

    <div class="course-edit-card">

        <div class="course-edit-card-header">
            <h2>Informasi Mata Kuliah</h2>
            <p>Silakan ubah data mata kuliah sesuai kebutuhan.</p>
        </div>

        <form
            action="{{ route('courses.update', $course) }}"
            method="POST"
            class="course-edit-form"
        >
            @csrf
            @method('PUT')

            {{-- Kode Mata Kuliah --}}
            <div class="form-group">
                <label for="code">Kode Mata Kuliah</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $course->code) }}"
                    required
                >

                @error('code')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Nama Mata Kuliah --}}
            <div class="form-group">
                <label for="name">Nama Mata Kuliah</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $course->name) }}"
                    required
                >

                @error('name')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Dosen Pengampu --}}
            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>

                <select
                    id="lecturer_id"
                    name="lecturer_id"
                    required
                >
                    <option value="">-- Pilih Dosen Pengampu --</option>

                    @foreach ($lecturers as $lecturer)
                        <option
                            value="{{ $lecturer->id }}"
                            {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}
                        >
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- SKS --}}
            <div class="form-group">
                <label for="sks">SKS</label>

                <input
                    type="number"
                    id="sks"
                    name="sks"
                    min="1"
                    max="6"
                    value="{{ old('sks', $course->sks) }}"
                    required
                >

                @error('sks')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label for="description">Deskripsi</label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                >{{ old('description', $course->description) }}</textarea>

                @error('description')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Status Mata Kuliah --}}
            <div class="form-group">
                <label for="status">Status Mata Kuliah</label>

                <select
                    id="status"
                    name="status"
                    required
                >
                    <option
                        value="active"
                        {{ old('status', $course->status) === 'active' ? 'selected' : '' }}
                    >
```
=======
<x-layout>
    <x-slot:title>Edit Mata Kuliah</x-slot:title>

    <div class="content-card">
        <h1>Edit Mata Kuliah</h1>

        <form action="{{ route('courses.update', [$course, 'as' => $role]) }}" method="POST">
            @csrf
            @method('PUT')

            <p>
                <label for="code">Kode Mata Kuliah</label><br>
                <input id="code" name="code" type="text" value="{{ old('code', $course->code) }}" required>
                @error('code')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <p>
                <label for="name">Nama Mata Kuliah</label><br>
                <input id="name" name="name" type="text" value="{{ old('name', $course->name) }}" required>
                @error('name')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <p>
                <label for="description">Deskripsi</label><br>
                <textarea id="description" name="description" rows="4" required>{{ old('description', $course->description) }}</textarea>
                @error('description')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <p>
                <label for="sks">SKS</label><br>
                <input id="sks" name="sks" type="number" min="1" max="255" value="{{ old('sks', $course->sks) }}" required>
                @error('sks')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <p>
                <label for="status">Status</label><br>
                <select id="status" name="status" required>
                    @foreach(['draft' => 'Draft', 'active' => 'Aktif', 'archived' => 'Arsip'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $course->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <p>
                <label for="lecturer">Dosen Pengampu</label><br>
                <input id="lecturer" name="lecturer" type="text"
                       value="{{ old('lecturer', $course->lecturer->name ?? '') }}"
                       placeholder="Masukkan nama dosen" required>
                @error('lecturer')<br><small style="color: #b91c1c;">{{ $message }}</small>@enderror
            </p>

            <button type="submit">Perbarui</button>
            <a href="{{ route('courses.index', ['as' => $role]) }}">Batal</a>
        </form>
    </div>
</x-layout>
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
