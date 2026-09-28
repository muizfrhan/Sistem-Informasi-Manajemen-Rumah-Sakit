/**
 * Konten landing page.
 *
 * Aturan: setiap klaim di file ini harus bisa ditelusuri ke kode yang benar-benar
 * ada di backend. Tidak ada angka performa yang tidak terukur, tidak ada
 * integrasi eksternal yang belum diimplementasikan, dan tidak ada klaim keamanan
 * yang lebih besar dari implementasi sebenarnya.
 *
 * Angka di sini hanya fakta struktural yang bisa diverifikasi dari kode:
 * jumlah peran, jumlah izin, jumlah kelas kamar, dan jumlah status antrean.
 */

export const NAV_LINKS = [
    // "Beranda" mengarah ke hero, bukan ke section terpisah. Id-nya `atas`
    // supaya ikut dipantau useActiveSection dan menyala saat user di atas.
    { id: 'atas', label: 'Beranda' },
    { id: 'fitur', label: 'Fitur' },
    { id: 'preview', label: 'Tampilan Sistem' },
    { id: 'alur', label: 'Alur Kerja' },
    { id: 'peran', label: 'Peran' },
    { id: 'keamanan', label: 'Keamanan' },
    { id: 'kontak', label: 'Kontak' },
];

export const HERO = {
    badge: 'Hospital Management System',
    title: 'Satu Platform untuk Seluruh Operasional',
    titleAccent: 'Rumah Sakit',
    lede: 'Pendaftaran, antrean, rekam medis, farmasi, penagihan, dan rawat inap'
        + ' berjalan di atas satu basis data yang sama, dengan hak akses'
        + ' yang berbeda untuk setiap peran.',
    primary: 'Masuk ke Sistem',
    secondary: 'Jelajahi Fitur',
    facts: [
        { label: 'Peran akses', value: '7' },
        { label: 'Izin granular', value: '22' },
        { label: 'Kelas kamar', value: '6' },
        { label: 'Status antrean', value: '7' },
    ],
    note: 'Fakta struktural dihitung dari kode aplikasi, bukan klaim performa.',
};

export const TRUST_ITEMS = [
    {
        icon: 'lock',
        label: 'NIK terenkripsi di database',
    },
    {
        icon: 'shield',
        label: '22 izin diperiksa di 70 titik backend',
    },
    {
        icon: 'users',
        label: 'Tujuh peran dengan cakupan berbeda',
    },
    {
        icon: 'clipboard',
        label: 'Nomor rekam medis otomatis',
    },
    {
        icon: 'box',
        label: 'Stok obat tidak bisa minus',
    },
    {
        icon: 'bed',
        label: 'Okupansi kamar dijaga database',
    },
];

/** Fitur yang benar-benar punya controller, route, dan halaman. */
export const FEATURES = [
    {
        icon: 'users',
        title: 'Data Pasien',
        body: 'Data identitas lengkap, nomor rekam medis berurutan, pencarian berdasarkan MRN, nama, atau nomor jaminan.',
        points: ['MRN otomatis 000001, 000002, …', 'NIK terenkripsi + pencarian tetap jalan'],
    },
    {
        icon: 'clipboard',
        title: 'Pendaftaran & Antrean',
        body: 'Nomor pendaftaran harian, nomor antrean per poli, dan pemanggilan otomatis dengan prioritas kasus gawat.',
        points: ['Nomor antrean A-001 per poli per hari', 'Panggilan berikutnya: darurat didahulukan'],
    },
    {
        icon: 'stethoscope',
        title: 'Rekam Medis Elektronik',
        body: 'Catatan SOAP, enam tanda vital, diagnosis ICD-10 bertipe, serta tindakan yang dicatat per kunjungan.',
        points: ['Draft lalu finalisasi', 'Setelah final, rekam medis tidak bisa diubah'],
    },
    {
        icon: 'pill',
        title: 'Farmasi & Resep',
        body: 'Resep elektronik dibuat dari rekam medis, divalidasi, lalu obat keluar dengan pengurangan stok atomik.',
        points: ['Stok menolak turun di bawah nol', 'Riwayat mutasi stok perypes'],
    },
    {
        icon: 'receipt',
        title: 'Penagihan',
        body: 'Invoice dibuat otomatis dari rekam medis yang sudah final, dengan pembayaran sebagian dan enam metode bayar.',
        points: ['Item dari konsul, tindakan, dan obat', 'Status tagihan diturunkan otomatis'],
    },
    {
        icon: 'bed',
        title: 'Rawat Inap & Kamar',
        body: 'Adopsi pasien dengan validasi kapasitas, kebijakan gender kamar, dan status perawatan sebelum masuk.',
        points: ['Okupansi dijaga unique index database', 'Ringkasan tempat tidur tersedia'],
    },
    {
        icon: 'calendar',
        title: 'Jadwal Dokter',
        body: 'Jadwal mingguan berulang per dokter dengan deteksi bentrok dan perhitungan kapasitas per hari.',
        points: ['Deteksi bentrok jam praktik', 'Dokter praktik hari ini terdeteksi otomatis'],
    },
    {
        icon: 'chart',
        title: 'Dashboard & Laporan',
        body: 'Ringkasan operasional harian, tujuh peringatan otomatis, dan laporan periode yang bisa difilter tanggal.',
        points: ['Peringatan stok menipis & kedaluwarsa', 'Analisis diagnosis dan obat terlaris'],
    },
    {
        icon: 'settings',
        title: 'Master Data & Administrasi',
        body: 'Poli, dokter, obat, dan kode ICD-10 bisa dikelola, bersama manajemen pengguna dan audit trail.',
        points: ['Audit perubahan data + peristiwa login', 'Hanya Super Admin yang bisa kelola pengguna'],
    },
];

/**
 * Data visual untuk mockup antarmuka.
 * Sengaja diberi label mockup di UI — bukan data pasien nyata.
 */
export const PREVIEW = {
    stats: [
        { label: 'Pendaftaran hari ini', value: '—', caption: 'dihitung dari database' },
        { label: 'Pasien aktif', value: '—', caption: 'dihitung dari database' },
        { label: 'Dokter praktik', value: '—', caption: 'dihitung dari database' },
    ],
    trendCaption: 'Panjang batang di mockup hanya ilustrasi — bukan data kunjungan nyata.',
    bars: [42, 61, 38, 74, 55, 82, 48],
    rows: [
        { id: 'RJ-…0001', poly: 'Poli Umum', status: 'Selesai', tone: 'green' },
        { id: 'RJ-…0002', poly: 'Poli Anak', status: 'Diperiksa', tone: 'teal' },
        { id: 'RJ-…0003', poly: 'IGD', status: 'Menunggu', tone: 'amber' },
        { id: 'RJ-…0004', poly: 'Poli Gigi', status: 'Selesai', tone: 'green' },
    ],
    activity: [
        { title: 'Rekam medis difinalisasi', meta: 'POLI UMUM' },
        { title: 'Resep dibuat', meta: 'APOTEK' },
        { title: 'Tagihan dibuat', meta: 'POLI ANAK' },
    ],
};

export const HOW_IT_WORKS = [
    {
        title: 'Masuk dengan akun yang diberikan',
        body: 'Setiap pengguna masuk memakai username milik administrator rumah sakit. Sistem mencatat waktu masuk dan alamat IP setiap kali berhasil masuk.',
        tags: ['Rate limit 5x/60 detik', 'Sesi di-regenerate saat login'],
    },
    {
        title: 'Kelola data dan pendaftaran',
        body: 'Data pasien didaftarkan sekali dengan nomor rekam medis otomatis. Pendaftaran dan nomor antrean dibuat dari data yang sama, tanpa input ganda.',
        tags: ['MRN berurutan', 'Nomor antrean per poli'],
    },
    {
        title: 'Jalankan operasional harian',
        body: 'Antrean dipanggil, rekam medis diisi, resep divalidasi, obat diserahkan, dan tagihan diselesaikan — semua saling terhubung.',
        tags: ['SOAP + ICD-10', 'Stok & invoice sinkron'],
    },
    {
        title: 'Pantau dan tinjau',
        body: 'Dashboard menampilkan kondisi hari ini, termasuk peringatan stok menipis, obat hampir kedaluwarsa, dan kamar dalam perawatan.',
        tags: ['7 peringatan otomatis', 'Laporan periode'],
    },
];

/**
 * Peran mengikuti app/Enums/RoleCode.php.
 * Deskripsi ditulis ulang berdasarkan izin yang benar-benar dimiliki, bukan
 * berdasarkan teks deskripsi enum (yang sebagian sudah usang).
 */
export const ROLES = [
    {
        initials: 'SA',
        name: 'Super Admin / IT',
        desc: 'Seluruh 22 izin, termasuk manajemen pengguna dan master data.',
        count: '22 izin',
    },
    {
        initials: 'RF',
        name: 'Resepsionis / Pendaftaran',
        desc: 'Pencarian dan pendaftaran pasien, papan antrean, melihat tagihan.',
        count: '8 izin',
    },
    {
        initials: 'DR',
        name: 'Dokter',
        desc: 'Input rekam medis, diagnosis ICD-10, resep elektronik, dan maju antrean.',
        count: '10 izin',
    },
    {
        initials: 'PR',
        name: 'Perawat',
        desc: 'Melihat pasien dan pendaftaran, menjalankan antrean, melihat rekam medis dan rawat inap.',
        count: '8 izin',
    },
    {
        initials: 'AP',
        name: 'Apoteker',
        desc: 'Validasi resep, penyerahan obat, pengelolaan stok, dan master obat.',
        count: '6 izin',
    },
    {
        initials: 'KS',
        name: 'Kasir / Keuangan',
        desc: 'Membuat invoice dari rekam medis final dan mencatat pembayaran.',
        count: '5 izin',
    },
    {
        initials: 'MN',
        name: 'Manajemen / Direksi',
        desc: 'Membaca seluruh modul, laporan periode, dan audit trail.',
        count: '11 izin',
    },
];

/** Klaim keamanan hanya untuk hal yang benar-benar diimplementasikan. */
export const SECURITY = [
    {
        icon: 'lock',
        title: 'NIK terenkripsi saat disimpan',
        body: 'Kolom NIK memakai enkripsi field-level (AES-256-CBC). Pencarian tetap bisa jalan lewat'
            + ' blind index HMAC-SHA256, jadi NIK terenkripsi tidak perlu didekripsi satu per satu.',
        proof: 'app/Casts/EncryptedNik.php',
    },
    {
        icon: 'shield',
        title: 'Otorisasi 22 izin di 70 titik',
        body: 'Setiap aksi sensitif memanggil pemeriksaan izin di sisi server. Menyembunyikan menu di'
            + ' antarmuka bukan satu-satunya lapisan — backend menolak aksi yang tidak diizinkan.',
        proof: 'app/Http/Controllers/* → denyUnless()',
    },
    {
        icon: 'users',
        title: 'Tujuh peran, cakupan berbeda',
        body: 'Kasir tidak dapat mengisi diagnosis, dokter tidak dapat membuat pengguna, dan manajemen'
            + ' hanya membaca. Hak akses berasal dari peran yang ditetapkan, bukan dari pilihan di antarmuka.',
        proof: 'app/Enums/RoleCode.php',
    },
    {
        icon: 'clock',
        title: 'Login dibatasi 5x percobaan',
        body: 'Rate limiter menolak percobaan keenam pada kombinasi username + IP yang sama dalam'
            + ' 60 detik. Lapisan kedua membatasi 10 permintaan per menit per IP.',
        proof: 'app/Http/Controllers/Auth/LoginController.php',
    },
    {
        icon: 'refresh',
        title: 'Sesi di-regenerate dan diputus',
        body: 'Session ID dibuat ulang setiap kali login berhasil. Saat logout, sesi dihancurkan'
            + ' beserta token CSRF-nya.',
        proof: 'app/Http/Controllers/Auth/LoginController.php',
    },
    {
        icon: 'clipboard',
        title: 'Audit trail untuk perubahan data',
        body: 'Perubahan pada data pasien, poli, pengguna, dan pendaftaran dicatat lengkap dengan nilai'
            + ' sebelum dan sesudah. Seluruh peristiwa login, login gagal, dan logout ikut tercatat.',
        proof: 'app/Observers/AuditableObserver.php',
    },
];

export const ABOUT = {
    eyebrow: 'Tujuan Sistem',
    title: 'Satu Platform. Operasional yang saling terhubung.',
    body: 'Sistem ini dirancang untuk menghilangkan pemisahan data antar unit. Rekam medis tidak'
        + ' lagi hidup di lemari, hasil pemeriksaan tidak lagi berada di komputer lain, dan stok obat'
        + ' tidak lagi tercatat di buku — semuanya dengan satu identitas pasien.',
    points: [
        {
            icon: 'link',
            title: 'Satu identitas pasien',
            body: 'Nomor rekam medis yang sama muncul di pendaftaran, rekam medis, resep, tagihan, dan rawat inap.',
        },
        {
            icon: 'clock',
            title: 'Proses yang tidak bisa dilompati',
            body: 'Rekam medis wajib punya diagnosis sebelum bisa difinalisasi. Stok dicek sebelum obat diserahkan.',
        },
        {
            icon: 'eye',
            title: 'Jejak perubahan terlihat',
            body: 'Setiap perubahan data utama menyimpan nilai sebelumnya, sehingga bisa ditelusuri siapa dan kapan.',
        },
    ],
    flow: [
        'Pendaftaran & antrean',
        'Pemeriksaan & rekam medis',
        'Resep & obat',
        'Tagihan',
        'Rawat inap',
    ],
};

export const CTA = {
    title: 'Siap melihat sistem ini berjalan dengan alur kerja Anda sendiri?',
    body: 'Tidak perlu akun khusus untuk menjelajahi sistem. Masuk memakai akun yang diberikan'
        + ' administrator rumah sakit Anda.',
    primary: 'Masuk ke Sistem',
    secondary: 'Jadwalkan Demo',
    note: 'Formulir demo diteruskan ke administrator sistem dan dicatat bersama waktu dan alamat IP pemohon.',
};

export const FAQ = [
    {
        q: 'Apakah data pasien dienkripsi?',
        a: 'Kolom NIK terenkripsi saat disimpan memakai enkripsi field-level, dan tetap bisa dicari lewat'
            + ' blind index. Field lain seperti alamat dan nomor telepon disimpan apa adanya pada'
            + ' konfigurasi bawaan sistem.',
    },
    {
        q: 'Bagaimana resep yang sudah jadi sampai ke Apoteker?',
        a: 'Resep dibuat dari rekam medis yang masih berstatus draft, lalu divalidasi Apoteker sebelum'
            + ' obat diserahkan. Pengurangan stok terjadi di dalam satu transaksi, dan sistem menolak'
            + ' jika stok tidak cukup.',
    },
    {
        q: 'Apakah semua peran bisa mengubah data?',
        a: 'Tidak. Setiap aksi memeriksa izin peran yang sedang login. Contohnya, peran Kasir dapat'
            + ' membuat tagihan dan mencatat pembayaran, tetapi tidak dapat membuka formulir diagnosis.',
    },
    {
        q: 'Nomor rekam medis dibuat otomatis?',
        a: 'Ya. Nomor rekam medis dibuat berurutan oleh sistem ketika pasien didaftarkan, jadi tidak'
            + ' ada duplikasi atau bentrokan nomor antar poli.',
    },
    {
        q: 'Apakah rekam medis yang sudah final bisa diubah?',
        a: 'Tidak. Setelah difinalisasi, rekam medis tidak dapat diedit. Finalisasi juga mensyaratkan'
            + ' adanya diagnosis ICD-10, sehingga rekam medis tidak bisa ditutup tanpa diagnosis.',
    },
    {
        q: 'Bagaimana mencoba sistem ini?',
        a: 'Masuk memakai akun yang diberikan administrator rumah sakit Anda. Tidak ada pendaftaran'
            + ' mandiri — akun dibuat oleh pihak rumah sakit.',
    },
];
