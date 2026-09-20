<<<<<<< HEAD
<x-layout>
=======
<<<<<<< HEAD
<x-layout>
=======
 <x-layout>
>>>>>>> 29371875b2befd733b6ba2494aaa2552776fc8b0
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637

    <x-slot:title>
        Tentang
    </x-slot:title>

    {{-- HEADER --}}
    <div class="about-header">
        <div>
            <h1>Tentang Kelompok</h1>
            <p>
                Informasi mengenai kelompok dan anggota pengembang KampusLMS.
            </p>
        </div>
    </div>

<<<<<<< HEAD
=======
        <h1>Tentang Kelompok</h1>
        <p>
            Halaman ini dapat dilihat oleh semua peran. Peran yang sedang dipilih:
            <strong>{{ ucfirst(in_array(request('as'), ['mahasiswa', 'dosen', 'admin'], true) ? request('as') : 'mahasiswa') }}</strong>.
        </p>
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637

    {{-- INFORMASI KELOMPOK --}}
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


        {{-- ROLE --}}
        <div class="role-info">
            <span class="role-label">Peran yang sedang dipilih</span>

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


        {{-- TABLE --}}
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


    {{-- STYLE --}}
    <style>

        /* =========================
           HEADER
        ========================= */

        .about-header {
            margin-bottom: 28px;
        }

        .about-header h1 {
            margin: 0 0 8px;

            color: var(--blue-dark);

            font-size: 27px;
            font-weight: 700;

            letter-spacing: -0.3px;
        }

        .about-header p {
            margin: 0;

            color: var(--muted);

            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================
           CARD
        ========================= */

        .about-card {
            overflow: hidden;

            background: var(--white);

            border: 1px solid var(--border);
            border-radius: var(--radius);

            box-shadow: var(--shadow);
        }


        /* =========================
           CARD HEADER
        ========================= */

        .about-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;

            padding: 20px 24px;

            border-bottom: 1px solid var(--border);
        }

        .about-card-header h2 {
            margin: 0 0 5px;

            color: var(--blue-dark);

            font-size: 18px;
            font-weight: 700;
        }

        .about-card-header p {
            margin: 0;

            color: var(--muted);

            font-size: 13px;
        }


        /* =========================
           GROUP BADGE
        ========================= */

        .group-badge {
            padding: 7px 12px;

            background: var(--blue-soft);
            color: var(--blue-dark);

            border-radius: 6px;

            font-size: 12px;
            font-weight: 700;
        }


        /* =========================
           ROLE INFO
        ========================= */

        .role-info {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 16px 24px;

            background: #f8fafc;

            border-bottom: 1px solid var(--border);
        }

        .role-label {
            color: var(--muted);

            font-size: 13px;
        }

        .role-badge {
            padding: 5px 10px;

            background: var(--blue-soft);
            color: var(--blue-dark);

            border-radius: 5px;

            font-size: 12px;
            font-weight: 600;
        }


        /* =========================
           TABLE
        ========================= */

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .about-table {
            width: 100%;

            border-collapse: collapse;

            font-size: 14px;
        }


        /* HEADER TABLE */

        .about-table thead {
            background: var(--blue-soft);
        }

        .about-table th {
            padding: 13px 20px;

            color: var(--blue-dark);

            font-size: 12px;
            font-weight: 700;

            text-align: left;

            border-bottom: 1px solid var(--border);

            white-space: nowrap;
        }


        /* ISI TABLE */

        .about-table td {
            padding: 15px 20px;

            color: var(--text);

            border-bottom: 1px solid var(--border);

            vertical-align: middle;
        }

        .about-table tbody tr:last-child td {
            border-bottom: none;
        }

        .about-table tbody tr {
            transition: background 0.15s ease;
        }

        .about-table tbody tr:hover {
            background: #f8fafc;
        }


        /* NOMOR */

        .about-table td:first-child {
            color: var(--muted);

            font-weight: 600;

            text-align: center;
        }


        /* NAMA */

        .member-name {
            color: var(--blue-dark);

            font-weight: 600;
        }


        /* NIM */

        .nim {
            color: var(--muted);

            font-size: 13px;

            letter-spacing: 0.2px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .about-card-header {
                align-items: flex-start;
            }

            .role-info {
                align-items: flex-start;
                flex-direction: column;
            }

            .about-table th,
            .about-table td {
                padding: 12px 14px;
            }

        }

    </style>

</x-layout>