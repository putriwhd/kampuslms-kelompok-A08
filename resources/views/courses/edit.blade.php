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
