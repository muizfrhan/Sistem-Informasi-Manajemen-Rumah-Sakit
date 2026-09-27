@extends('layouts.landing')

@section('title', 'SIMRS Rekam Medis Elektronik Terintegrasi untuk Rumah Sakit')
@section('description', 'SIMRS berbasis Rekam Medis Elektronik (RME): aman, sesuai standar SATUSEHAT, dan efisien. Mempercepat antrean, mengurangi human error, serta meningkatkan akurasi klaim BPJS.')

@php
    $painPoints = [
        [
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'Antrean panjang, pasien menumpuk',
            'desc' => 'Pendaftaran manual membuat waktu tunggu naik terus. Petugas kewalahan, pasien kelelahan, dan produktivitas turun drastis.',
            'impact' => 'Waktu tunggu > 45 menit',
        ],
        [
            'icon' => 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H8l-4-3z',
            'title' => 'Data tersebar di banyak tempat',
            'desc' => 'Rekam medis di lemari, hasil laboratorium di komputer lain, stok obat di buku. Satu pasien, lima sistem yang tidak saling bicara.',
            'impact' => 'Data silo antar unit',
        ],
        [
            'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'Salah tulis dan klaim ditolak',
            'desc' => 'Salah tulis diagnosis, salah kode ICD-10, atau tidak ada persetujuan medis berarti klaim BPJS ditolak tanpa bisa dibantah.',
            'impact' => 'Klaim tertahan berhari-hari',
        ],
        [
            'icon' => 'M9 12l2 2 4-4m5.6 3A9 9 0 113.6 7.6M16 8h5v5',
            'title' => 'Audit dan regulasi menyulitkan',
            'desc' => 'Saat pemeriksaan datang, tidak ada jejak digital yang jelas. Sulit membuktikan kapan dan siapa yang mengubah data pasien.',
            'impact' => 'Jejak audit tidak tersedia',
        ],
    ];

    $features = [
        [
            'icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z',
            'title' => 'Rekam Medis Elektronik Terintegrasi',
            'desc' => 'Satu rekam medis untuk seluruh unit: poli, IGD, VK, inap, dan klinik. Diakses real-time dari mana saja dengan kendali akses per peran.',
        ],
        [
            'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h7',
            'title' => 'Antrean Digital Real-Time',
            'desc' => 'Nomor antrean otomatis, layar panggil, dan notifikasi WhatsApp ke pasien. Petugas resepsionis tidak lagi berteriak menyebut nomor.',
        ],
        [
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'title' => 'E-Prescription dan Apotek',
            'desc' => 'Resep elektronik langsung terhubung ke instalasi farmasi. Stok obat diperbarui otomatis setiap resep dibuat atau NeighborhoodACK diselesaikan.',
        ],
        [
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2v-1a2 2 0 00-2-2H9',
            'title' => 'Billing dan Integrasi BPJS',
            'desc' => 'Tagihan terhitung otomatis dari tindakan, cek eligibility, serta ekspor data klaim sesuai format Antreg dan P-Care.',
        ],
        [
            'icon' => 'M9 19V6a2 2 0 00-2-2H5a2 2 0 00-2 2v13h2M9 19h6m-6 0v2m6-2v2m0-16h2a2 2 0 012 2v12a2 2 0 01-2 2h-2',
            'title' => 'Dashboard Analitik',
            'desc' => 'BOR, ALOS, tingkat hunian tempat tidur, mortalitas, pendapatan, dan produktivitas dokter dalam satu dashboard yang bisa diunduh sewaktu-waktu.',
        ],
        [
            'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm8-10V7a4 4 0 00-8 0v4h8z',
            'title' => 'Role-Based Access dan Audit Trail',
            'desc' => 'Setiap akses dan perubahan data tercatat lengkap dengan waktu, pelaku, serta nilai sebelum dan sesudah. Tidak ada yang bisa diubah diam-diam.',
        ],
    ];

    $benefits = [
        [
            'metric' => '60% lebih cepat',
            'title' => 'Proses administrasi',
            'desc' => 'Pendaftaran, pencatatan SOAP, dan laporan harian selesai lebih cepat karena tidak ada lagi input ganda di banyak tempat.',
        ],
        [
            'metric' => '25% naik',
            'title' => 'Tingkat klaim diterima',
            'desc' => 'Validasi diagnosis dan kode prosedur membantu mengurangi klaim ditolak karena kurang dokumen pendukung.',
        ],
        [
            'metric' => 'Hampir nol',
            'title' => 'Berkas hilang',
            'desc' => 'Rekam medis tersimpan otomatis di server dengan backup harian, tidak ada lagi map yang hilang di ruang arsip.',
        ],
        [
            'metric' => '3 bulan',
            'title' => 'Perkiraan balik atas investasi',
            'desc' => 'Hemat biaya cetak, penyimpanan arsip, lembur admisi, dan waktu karyawan yang selama ini bekerja untuk input manual.',
        ],
    ];

    $steps = [
        ['Konsultasi 30 menit', 'Kami pelajari alur kerja, jumlah poli, dan masalah administrasi yang sedang Anda hadapi.'],
        ['Demo dengan skenario nyata', 'Anda melihat sistem berjalan dengan kasus yang mirip kondisi rumah sakit Anda, bukan video generik.'],
        ['Surat penawaran dan rencana migrasi', 'Rencana implementasi, jadwal migrasi data, dan estimasi biaya yang transparan.'],
        ['Implementasi dan pelatihan', 'Migrasi data, pelatihan per peran pengguna, serta pendampingan sampai sistem dipakai setiap hari.'],
    ];

    $securityPoints = [
        ['Enkripsi end-to-end', 'Data terenkripsi saat disimpan (AES-256) dan saat ditransmisikan (TLS 1.3).'],
        ['Audit trail lengkap', 'Setiap akses, perubahan, dan penghapusan data tercatat dan tidak bisa dihapus.'],
        ['Role-based access control', 'Akses dibatasi per peran pengguna, hingga tingkat data tertentu sesuai kebijakan instalasi Anda.'],
        ['Backup harian otomatis', 'Backup terenkripsi setiap hari, lengkap dengan uji pemulihan berkala.'],
        ['Self-hosted atau cloud', 'Pakai server rumah sakit sendiri untuk data sensitif, atau cloud terenkripsi untuk fleksibilitas.'],
        ['Tahan koneksi putus', 'Proses tetap berjalan saat jaringan terganggu, data tersinkron otomatis saat koneksi pulih.'],
    ];

    $faq = [
        [
            'q' => 'Apakah data pasien kami aman di tangan tim Anda?',
            'a' => 'Data disimpan di server milik rumah sakit atau cloud terenkripsi, bukan di perangkat kami. Akses berbasis peran dan seluruh aktivitas tercatat pada audit trail. Kami juga menandatangani kerahasiaan data sebelum akses diberikan.',
        ],
        [
            'q' => 'Apakah sudah sesuai dengan standar SATUSEHAT dan ketentuan Kemenkes?',
            'a' => 'Struktur data mengikuti standar SATUSEHAT Kemenkes, termasuk penulisan SOAP, kode ICD-10, dan kode prosedur, serta mendukung ekspor data untuk pelaporan. Untuk klaim BPJS, kami mendukung format Antreg dan P-Care.',
        ],
        [
            'q' => 'Berapa lama proses implementasinya?',
            'a' => 'Untuk instalasi 50 sampai 200 tempat tidur, implementasi umumnya berlangsung 4 sampai 8 minggu, termasuk migrasi data dan pelatihan. Jadwal dapat dipecah bertahap per unit agar operasional harian tidak terganggu.',
        ],
        [
            'q' => 'Bisakah sistem ini berjalan berdampingan dengan sistem lama kami?',
            'a' => 'Bisa. Kami menyediakan antarmuka integrasi (API) untuk menghubungkan dengan LIS, SIMRS lama, mesin laboratorium, dan kasir. Pendekatan yang kami rekomendasikan adalah transisi bertahap, bukan mengganti semuanya sekaligus.',
        ],
        [
            'q' => 'Berapa biayanya dan bagaimana cara pembayarannya?',
            'a' => 'Harga berbasis jumlah pengguna, modul yang dipilih, dan level dukungan, dengan skema langganan tahunan. Biaya migrasi data dan pelatihan sudah termasuk, sehingga tidak ada biaya tersembunyi setelah sistem berjalan.',
        ],
        [
            'q' => 'Apakah ada dukungan setelah sistem berjalan?',
            'a' => 'Ada. Setiap pelanggan mendapat dukungan teknis pada jam kerja, akses ke pusat pengetahuan, dan pembaruan keamanan rutin. Paket premium menyediakan pendampingan langsung di lokasi dan prioritas layanan.',
        ],
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": {{ json_encode(config('app.name')) }},
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "description": "Sistem Informasi Manajemen Rumah Sakit berbasis Rekam Medis Elektronik (RME)."
        }
    </script>
@endpush

{{-- HERO --}}
<section class="relative overflow-hidden bg-slate-900 text-white">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-950 via-slate-900 to-slate-900"></div>
    <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
    <div class="absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:py-24">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                Sesuai standar SATUSEHAT Kemenkes dan UU PDP No. 27/2022
            </p>

            <h1 class="mt-6 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                Rekam medis yang rapi, klaim yang lancar, rumah sakit yang tenang.
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                {{ config('app.name') }} menyatukan pendaftaran, rekam medis elektronik, apotek, dan penagihan dalam satu
                sistem. Lebih sedikit input ganda, waktu tunggu pasien jauh turun, dan setiap klaim BPJS dapat diaudit.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <button type="button" @click="demo = true"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-950/40 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Jadwalkan Demo Gratis
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
                <a href="#solusi"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-600 px-6 py-3.5 text-sm font-semibold text-slate-100 transition hover:border-slate-400 hover:bg-slate-800">
                    Pelajari Fitur
                </a>
            </div>

            <p class="mt-4 text-xs text-slate-400">Demo 30 menit, tanpa biaya dan tanpa komitmen.</p>

            <div class="mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-slate-700 pt-6">
                <div>
                    <p class="text-2xl font-bold">4&ndash;8 minggu</p>
                    <p class="mt-0.5 text-xs text-slate-400">waktu implementasi</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">60%</p>
                    <p class="mt-0.5 text-xs text-slate-400">hemat waktu administrasi</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">100%</p>
                    <p class="mt-0.5 text-xs text-slate-400">aktivitas tercatat</p>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="rounded-2xl border border-slate-700 bg-slate-800/70 p-4 shadow-2xl shadow-black/40 backdrop-blur">
                <div class="flex items-center gap-2 border-b border-slate-700 pb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400/80"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400/80"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/80"></span>
                    <span class="ml-2 text-xs text-slate-400">Dashboard RS &middot; {{ now()->translatedFormat('d M Y') }}</span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    @foreach ([
                        ['Antrean hari ini', '142', 'text-emerald-400'],
                        ['Rata-rata tunggu', '12 mnt', 'text-blue-400'],
                        ['Klaim berhasil', '97%', 'text-sky-400'],
                    ] as [$label, $value, $tone])
                        <div class="rounded-lg bg-slate-900/70 p-3">
                            <p class="text-[10px] uppercase tracking-wide text-slate-400">{{ $label }}</p>
                            <p class="mt-1 text-lg font-bold {{ $tone }}">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 rounded-lg bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Kunjungan 7 hari terakhir</p>
                    <div class="mt-3 flex h-24 items-end gap-2">
                        @foreach ([42, 58, 51, 72, 66, 88, 76] as $height)
                            <div class="flex-1 rounded-t bg-blue-500/70" style="height: {{ $height }}%"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-3 space-y-2">
                    @foreach ([
                        ['Poli Umum', 'Dr. A. Pratama', 'Selesai', 'bg-emerald-500/15 text-emerald-300'],
                        ['Poli Anak', 'Dr. S. Lestari', 'Diperiksa', 'bg-blue-500/15 text-blue-300'],
                        ['IGD', 'Dr. B. Nugroho', 'Menunggu', 'bg-amber-500/15 text-amber-300'],
                    ] as [$poly, $doctor, $status, $statusClass])
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-900/70 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-slate-100">{{ $poly }}</p>
                                <p class="truncate text-[11px] text-slate-400">{{ $doctor }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusClass }}">{{ $status }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="absolute -bottom-6 -left-4 hidden items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 shadow-xl sm:flex">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-white">Klaim terverifikasi</p>
                    <p class="text-[11px] text-slate-400">Siap dikirim ke Antreg</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- TRUST SIGNALS --}}
<section class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
            Dipercaya rumah sakit dan klinik di Indonesia
        </p>

        <div class="mt-8 grid grid-cols-2 items-center gap-x-6 gap-y-8 sm:grid-cols-3 lg:grid-cols-6">
            @foreach (['RS Karya Bersama', 'RS Amanah Sehat', 'RSMetro Medika', 'Klinik Pratama Nugraha', 'RS Bhakti Husada', 'Grand Medika'] as $client)
                <div class="flex items-center justify-center">
                    <span class="text-center text-sm font-bold tracking-wide text-slate-400">{{ $client }}</span>
                </div>
            @endforeach
        </div>

        <p class="mt-6 text-center text-[11px] text-slate-400">
            Placeholder logo klien. Ganti dengan logo rumah sakit nyata beserta izin penggunaan sebelum publikasi.
        </p>

        <div class="mt-10 grid gap-4 border-t border-slate-100 pt-10 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['ISO 27001', 'Sistem manajemen keamanan informasi', 'M4 4h16v16H4zM8 12h8M12 8v8'],
                ['Standar SATUSEHAT', 'Kemenkes RI', 'M12 3v18M5 8l7-5 7 5M5 16l7 5 7-5'],
                ['UU PDP No. 27/2022', 'Pelindungan data pribadi', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm8-10V7a4 4 0 00-8 0v4h8z'],
                ['ICD-10 WHO', 'Kode diagnosis internasional', 'M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z'],
            ] as [$title, $subtitle, $icon])
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
                        <p class="text-xs text-slate-500">{{ $subtitle }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-6 text-[11px] text-slate-400">
            Placeholder sertifikasi. Logo dan klaim sertifikasi hanya boleh ditampilkan setelah sertifikat resmi berlaku.
        </p>
    </div>
</section>

{{-- PROBLEM --}}
<section id="masalah" class="bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-600">Masalah yang sering muncul</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                Sistem lama bukan lagi menghemat biaya. Itu mahal.
            </h2>
            <p class="mt-5 text-base leading-relaxed text-slate-600">
                Setiap menit yang dipakai mengelola data secara manual adalah menit yang tidak bisa dipakai untuk pasien.
                Empat masalah di bawah ini adalah alasan paling umum kenapa digitalisasi tertunda.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2">
            @foreach ($painPoints as $point)
                <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $point['icon'] }}"/>
                            </svg>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">{{ $point['impact'] }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ $point['title'] }}</h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-slate-600">{{ $point['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- SOLUTION --}}
<section id="solusi" class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Solusi</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                Satu sistem, dari pendaftaran sampai laporan keuangan.
            </h2>
            <p class="mt-5 text-base leading-relaxed text-slate-600">
                {{ config('app.name') }} menyatukan alur yang biasanya tersebar di lima aplikasi berbeda.
                Satu kali input, data langsung tersedia di unit mana pun yang membutuhkannya.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $feature)
                <div class="group rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition group-hover:bg-blue-700 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-slate-600">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-14 grid gap-8 rounded-2xl bg-slate-900 p-8 text-white lg:grid-cols-3 lg:p-10">
            <div class="lg:col-span-2">
                <h3 class="text-xl font-bold">Semua unit bicara dalam satu bahasa data.</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Pendaftaran di resepsionis langsung terlihat oleh dokter. Hasil laboratorium masuk ke rekam medis tanpa
                    rekap manual. Resep dokter sampai ke apotek. Tagihan terhitung otomatis. Direksi melihat semuanya
                    dalam satu dashboard.
                </p>
            </div>
            <ul class="space-y-3 text-sm text-slate-200">
                @foreach (['Pendaftaran dan antrean', 'Rekam medis dan SOAP', 'Laboratorium dan radiologi', 'Apotek dan stok obat', 'Billing dan klaim BPJS', 'Dashboard direksi'] as $unit)
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $unit }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- BENEFITS --}}
<section class="bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Dampak untuk bisnis Anda</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                Bukan sekadar fitur. Ini angka di laporan Anda.
            </h2>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($benefits as $benefit)
                <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-2xl font-extrabold tracking-tight text-blue-700">{{ $benefit['metric'] }}</p>
                    <h3 class="mt-3 text-base font-semibold text-slate-900">{{ $benefit['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $benefit['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <p class="mt-6 text-xs text-slate-500">
            Angka ilustratif untuk menunjukkan potensi dampak. Ganti dengan data hasil studi klien Anda sebelum publikasi.
        </p>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Cara mulai</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                Dari konsultasi ke sistem jalan tanpa drama.
            </h2>
        </div>

        <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $index => $step)
                <li class="relative">
                    <span class="text-4xl font-extrabold text-slate-200">0{{ $index + 1 }}</span>
                    <h3 class="mt-3 text-base font-semibold text-slate-900">{{ $step[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step[1] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- SECURITY & COMPLIANCE --}}
<section id="keamanan" class="bg-slate-900 text-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-14 lg:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400">Keamanan dan Kepatuhan</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">
                    Data pasien adalah tanggung jawab Anda, dan kami memperlakukannya demikian.
                </h2>
                <p class="mt-5 text-base leading-relaxed text-slate-300">
                    Kami paham bahwa bagi rumah sakit, satu insiden data berarti risiko operasional dan reputasi.
                    Karena itu keamanan dibangun sejak arsitektur pertama, bukan ditambahkan belakangan.
                </p>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        'Data terenkripsi saat disimpan dan saat dikirim',
                        'Akses berbasis peran, dicatat lengkap pada audit trail',
                        'Kesepakatan kerahasiaan data ditandatangani sebelum akses diberikan',
                        'Backup harian terenkripsi dengan uji pemulihan rutin',
                        'Pilihan self-hosted untuk rumah sakit dengan kebijakan data ketat',
                    ] as $point)
                        <li class="flex items-start gap-3 text-sm text-slate-200">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($securityPoints as $index => $point)
                    <div class="rounded-xl border border-slate-700 bg-slate-800/50 p-5">
                        <span class="text-xs font-bold text-emerald-400">0{{ $index + 1 }}</span>
                        <h3 class="mt-2 text-sm font-semibold text-white">{{ $point[0] }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-400">{{ $point[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- TESTIMONIAL --}}
<section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <figure class="h-full rounded-2xl border border-slate-200 bg-slate-50 p-8 lg:p-10">
                    <svg class="h-8 w-8 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7.17 6A5.17 5.17 0 002 11.17V18h6.83v-6.83H6.5A1.67 1.67 0 018.17 9.5V6H7.17zm10 0A5.17 5.17 0 0012 11.17V18h6.83v-6.83H16.5a1.67 1.67 0 001.67-1.67V6h-1z"/>
                    </svg>

                    <blockquote class="mt-6 text-lg font-medium leading-relaxed text-slate-800 sm:text-xl">
                        &ldquo;Kami sempat punya tiga sistem berbeda untuk pendaftaran, rekam medis, dan tagihan. Sekarang
                        semuanya jadi satu. Waktu tunggu pasien turun drastis, dan untuk pertama kalinya saat audit kami
                        bisa menunjukkan kapan setiap data pasien diubah serta oleh siapa.&rdquo;
                    </blockquote>

                    <figcaption class="mt-8 flex items-center gap-4 border-t border-slate-200 pt-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white">RH</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">dr. Rina Hartati, M.M.</p>
                            <p class="text-sm text-slate-600">Direktur RS Teman Sehat, 180 tempat tidur</p>
                        </div>
                    </figcaption>

                    <p class="mt-6 text-xs text-slate-400">
                        Testimoni contoh. Ganti dengan kutipan klien nyata yang telah mendapat persetujuan tertulis.
                    </p>
                </figure>
            </div>

            <div class="grid gap-6">
                @foreach ([
                    ['Klaim ditolak', '31% lebih rendah'],
                    ['Waktu tunggu rata-rata', '48 menit menjadi 14 menit'],
                    ['Tenaga admin tambahan', 'Tidak diperlukan'],
                ] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                        <p class="text-sm font-medium text-slate-600">{{ $label }}</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- PRICING / NEXT STEP --}}
<section id="harga" class="bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Langkah berikutnya</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                Lihat sendiri dulu, tanpa komitmen.
            </h2>
            <p class="mt-5 text-base leading-relaxed text-slate-600">
                Jadwalkan demo gratis 30 menit. Kami tunjukkan sistem dengan skenario yang mirip kondisi rumah sakit Anda,
                lalu Anda putuskan sendiri apakah layak dilanjutkan.
            </p>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-7 lg:col-span-2">
                <h3 class="text-base font-semibold text-slate-900">Yang Anda dapatkan di demo</h3>
                <ul class="mt-5 space-y-3 text-sm text-slate-600">
                    @foreach ([
                        'Demo langsung dengan skenario kasus nyata',
                        'Simulasi alur pendaftaran sampai penagihan',
                        'Pembahasan kebutuhan integrasi LIS dan apotek',
                        'Estimasi waktu implementasi dan migrasi data',
                        'Penawaran harga transparan tanpa biaya tersembunyi',
                    ] as $item)
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-7 rounded-xl bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Integrasi yang didukung</p>
                    <p class="mt-2 text-sm text-slate-600">
                        BPJS P-Care, Antreg, LIS dan laboratorium, mesin farmasi, kasir, serta sistem apotek pihak ketiga.
                    </p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-7 shadow-xl shadow-blue-900/10 ring-1 ring-blue-200 lg:col-span-3">
                <h3 class="text-lg font-bold text-slate-900">Jadwalkan demo gratis Anda</h3>
                <p class="mt-1.5 text-sm text-slate-600">Isi formulir, tim kami menghubungi Anda dalam 1x24 jam kerja.</p>

                @include('landing.partials.demo-form')
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-white">
    <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Pertanyaan Umum</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Yang paling sering ditanyakan direksi</h2>
        </div>

        <div class="mt-12 divide-y divide-slate-200 border-y border-slate-200">
            @foreach ($faq as $item)
                <details class="group py-6">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-semibold text-slate-900">
                        {{ $item['q'] }}
                        <svg class="h-5 w-5 shrink-0 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="bg-blue-700">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:px-8 lg:py-20">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                Satu langkah kecil hari ini lebih murah daripada satu tahun tanpa jejak data.
            </h2>
            <p class="mt-3 text-sm text-blue-100">
                Jadwalkan demo gratis. Tanpa biaya, tanpa komitmen, tanpa risiko untuk operasional Anda.
            </p>
        </div>

        <div class="mt-8 flex shrink-0 flex-col gap-3 sm:flex-row lg:mt-0">
            <button type="button" @click="demo = true"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3.5 text-sm font-bold text-blue-700 shadow-lg transition hover:bg-blue-50">
                Jadwalkan Demo Gratis
            </button>
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center rounded-lg border border-white/40 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
                Masuk ke Sistem
            </a>
        </div>
    </div>
</section>
