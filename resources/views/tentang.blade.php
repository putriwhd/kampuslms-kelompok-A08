<x-layout>

    <x-slot:title>
        Tentang
    </x-slot:title>

    <div class="about-header">
        <div>
            <h1>Tentang Kelompok</h1>

            <p>
                Informasi mengenai kelompok dan anggota pengembang KampusLMS.
            </p>
        </div>
    </div>

    <p class="about-role-description">
        Halaman ini dapat dilihat oleh semua peran. Peran yang sedang dipilih:
        <strong>
            {{ ucfirst(in_array(request('as'), ['mahasiswa', 'dosen', 'admin'], true) ? request('as') : 'mahasiswa') }}
        </strong>.
    </p>

    <div class="about-card">
        <div class="about-card-header">
            <div>
                <h2>Kelompok A-08</h2>
                <p>Informasi anggota kelompok</p>
            </div>

            <div class="group-badge">
                A-08
            </div>
        </div>

        <div class="role-info">
            <span class="role-label">
                Peran yang sedang dipilih
            </span>

            <span class="role-badge">
                {{ ucfirst(
                    in_array(
                        request('as'),
                        ['mahasiswa', 'dosen', 'admin'],
                        true
                    )
                    ? request('as')
                    : 'mahasiswa'
                ) }}
            </span>
        </div>

        <div class="table-container">
            <table class="about-table">
                <thead>
                    <tr>
                        <th width="80px">No.</th>
                        <th>Nama Anggota</th>
                        <th>NIM</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <span class="member-name">
                                Putri Nurwahid
                            </span>
                        </td>
                        <td>
                            <span class="nim">
                                10241063
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>
                            <span class="member-name">
                                Sarah Adelia W
                            </span>
                        </td>
                        <td>
                            <span class="nim">
                                10241065
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>3</td>
                        <td>
                            <span class="member-name">
                                Siti Fatimah
                            </span>
                        </td>
                        <td>
                            <span class="nim">
                                10241067
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>4</td>
                        <td>
                            <span class="member-name">
                                Syarifah Nazwa Aulia H 4
                            </span>
                        </td>
                        <td>
                            <span class="nim">
                                10241069
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</x-layout>