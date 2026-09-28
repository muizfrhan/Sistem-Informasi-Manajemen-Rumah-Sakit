<?php

return [

    'hospital_name' => env('HOSPITAL_NAME', 'RS Citra Husada'),

    'hospital_short_name' => env('HOSPITAL_SHORT_NAME', 'SIMRS'),

    'hospital_address' => env('HOSPITAL_ADDRESS', 'Jl. Raya Rumah Sakit No. 1'),

    'hospital_phone' => env('HOSPITAL_PHONE', '(022) 1234567'),

    'audit_logging_enabled' => env('AUDIT_LOGGING_ENABLED', true),

    'mr_number_padding' => 6,

    /* Durasi tampilan toast notifikasi dalam milidetik. */
    'toast_duration' => env('TOAST_DURATION', 6000),

    /* Jumlah hari sebelum obat diberi peringatan kedaluwarsa. */
    'expiry_warning_days' => (int) env('EXPIRY_WARNING_DAYS', 90),

    /* Jumlah baris per halaman untuk tabel daftar. */
    'per_page' => (int) env('PER_PAGE', 20),

];
