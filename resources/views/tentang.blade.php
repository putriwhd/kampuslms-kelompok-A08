<<<<<<< HEAD
<x-layout>
=======
 <x-layout>
>>>>>>> 29371875b2befd733b6ba2494aaa2552776fc8b0

    <x-slot:title>
        Tentang
    </x-slot:title>

    <div class="content-card">

        <h1>Tentang Kelompok</h1>
        <p>
            Halaman ini dapat dilihat oleh semua peran. Peran yang sedang dipilih:
            <strong>{{ ucfirst(in_array(request('as'), ['mahasiswa', 'dosen', 'admin'], true) ? request('as') : 'mahasiswa') }}</strong>.
        </p>

        <p>
            Nama Kelompok:
            <strong>A-08</strong>
        </p>

        <table class="about-table">

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Anggota</th>
                    <th>NIM</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>1</td>
                    <td>Putri Nurwahid</td>
                    <td>10241063</td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Sarah Adelia W</td>
                    <td>10241065</td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Siti Fatimah</td>
                    <td>10241067</td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>Syarifah Nazwa Aulia H 4</td>
                    <td>10241069</td>
                </tr>

            </tbody>

        </table>

    </div>

</x-layout>