<?php

/*
|--------------------------------------------------------------------------
| Pesan validasi (Bahasa Indonesia)
|--------------------------------------------------------------------------
|
| Dipakai karena APP_LOCALE=id. Tanpa berkas ini, Laravel jatuh ke bawaan
| berbahasa Inggris sehingga form berbahasa Indonesia menampilkan pesan seperti
| "The nama lengkap field is required."
|
*/

return [

    'accepted' => 'Kolom :attribute harus disetujui.',
    'accepted_if' => 'Kolom :attribute harus disetujui jika :other bernilai :value.',
    'active_url' => 'Kolom :attribute bukan URL yang aktif.',
    'after' => 'Kolom :attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => 'Kolom :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Kolom :attribute harus berupa larik.',
    'ascii' => 'Kolom :attribute harus hanya berisi karakter single-byte.',
    'before' => 'Kolom :attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Kolom :attribute harus antara :min sampai :max item.',
        'file' => 'Ukuran kolom :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => 'Kolom :attribute harus antara :min sampai :max.',
        'string' => 'Kolom :attribute harus antara :min sampai :max karakter.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'can' => 'Kolom :attribute mengandung nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi kolom :attribute tidak cocok.',
    'contains' => 'Kolom :attribute tidak memiliki nilai yang diminta.',
    'current_password' => 'Kata sandi yang dimasukkan salah.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'date_equals' => 'Kolom :attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => 'Kolom :attribute tidak sesuai format :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal digit desimal.',
    'declined' => 'Kolom :attribute harus ditolak.',
    'declined_if' => 'Kolom :attribute harus ditolak jika :other bernilai :value.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits digit.',
    'digits_between' => 'Kolom :attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => 'Kolom :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Kolom :attribute memiliki nilai yang duplikat.',
    'doesnt_end_with' => 'Kolom :attribute tidak boleh diakhiri dengan: :values.',
    'doesnt_start_with' => 'Kolom :attribute tidak boleh diawali: :values.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'ends_with' => 'Kolom :attribute harus diakhiri dengan: :values.',
    'enum' => 'Nilai :attribute yang dipilih tidak valid.',
    'exists' => 'Nilai :attribute yang dipilih tidak valid.',
    'extensions' => 'Kolom :attribute harus salah satu dari: :values.',
    'file' => 'Kolom :attribute harus berupa berkas.',
    'filled' => 'Kolom :attribute wajib diisi.',
    'gt' => [
        'array' => 'Kolom :attribute harus lebih dari :value item.',
        'file' => 'Ukuran kolom :attribute harus lebih dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih dari :value.',
        'string' => 'Kolom :attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Kolom :attribute harus :value item atau lebih.',
        'file' => 'Ukuran kolom :attribute harus :value kilobyte atau lebih.',
        'numeric' => 'Kolom :attribute harus :value atau lebih.',
        'string' => 'Kolom :attribute harus :value karakter atau lebih.',
    ],
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Kolom :attribute yang dipilih tidak valid.',
    'in_array' => 'Kolom :attribute tidak ada di dalam :other.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Kolom :attribute harus berupa string JSON yang valid.',
    'list' => 'Kolom :attribute harus berupa list.',
    'lowercase' => 'Kolom :attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => 'Kolom :attribute harus kurang dari :value item.',
        'file' => 'Ukuran kolom :attribute harus kurang dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus kurang dari :value.',
        'string' => 'Kolom :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Kolom :attribute tidak boleh lebih dari :value item.',
        'file' => 'Ukuran kolom :attribute tidak boleh lebih dari :value kilobyte.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :value.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :value karakter.',
    ],
    'mac_address' => 'Kolom :attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => 'Kolom :attribute tidak boleh lebih dari :max item.',
        'file' => 'Ukuran kolom :attribute tidak boleh lebih dari :max kilobyte.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => 'Kolom :attribute tidak boleh lebih dari :max digit.',
    'mimes' => 'Kolom :attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => 'Kolom :attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'array' => 'Kolom :attribute harus memiliki minimal :min item.',
        'file' => 'Ukuran kolom :attribute harus minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute harus minimal :min.',
        'string' => 'Kolom :attribute harus minimal :min karakter.',
    ],
    'min_digits' => 'Kolom :attribute harus memiliki minimal :min digit.',
    'missing' => 'Kolom :attribute tidak ada.',
    'missing_if' => 'Kolom :attribute tidak ada jika :other bernilai :value.',
    'missing_unless' => 'Kolom :attribute tidak ada kecuali :other bernilai :value.',
    'missing_with' => 'Kolom :attribute tidak ada jika terdapat :values.',
    'missing_with_all' => 'Kolom :attribute tidak ada jika terdapat :values.',
    'multiple_of' => 'Kolom :attribute harus merupakan kelipatan dari :value.',
    'not_in' => 'Kolom :attribute yang dipilih tidak valid.',
    'not_regex' => 'Format kolom :attribute tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Kolom :attribute harus mengandung minimal satu huruf.',
        'mixed' => 'Kolom :attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => 'Kolom :attribute harus mengandung minimal satu angka.',
        'symbols' => 'Kolom :attribute harus mengandung minimal satu simbol.',
        'uncompromised' => 'Kolom :attribute pernah muncul dalam kebocoran data. Pilih kata sandi yang lain.',
    ],
    'present' => 'Kolom :attribute harus ada.',
    'present_if' => 'Kolom :attribute harus ada jika :other bernilai :value.',
    'present_unless' => 'Kolom :attribute harus ada kecuali :other bernilai :value.',
    'present_with' => 'Kolom :attribute harus ada jika terdapat :values.',
    'present_with_all' => 'Kolom :attribute harus ada jika terdapat :values.',
    'prohibited' => 'Kolom :attribute dilarang diisi.',
    'prohibited_if' => 'Kolom :attribute dilarang diisi jika :other bernilai :value.',
    'prohibited_if_accepted' => 'Kolom :attribute dilarang diisi jika :other disetujui.',
    'prohibited_if_declined' => 'Kolom :attribute dilarang diisi jika :other ditolak.',
    'prohibited_unless' => 'Kolom :attribute dilarang diisi kecuali :other bernilai :value.',
    'prohibits' => 'Kolom :attribute melarang isi kolom :other.',
    'regex' => 'Format kolom :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_array_keys' => 'Kolom :attribute harus memuat entri untuk: :values.',
    'required_if' => 'Kolom :attribute wajib diisi jika :other bernilai :value.',
    'required_if_accepted' => 'Kolom :attribute wajib diisi jika :other disetujui.',
    'required_if_declined' => 'Kolom :attribute wajib diisi jika :other ditolak.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other bernilai :value.',
    'required_with' => 'Kolom :attribute wajib diisi jika terdapat :values.',
    'required_with_all' => 'Kolom :attribute wajib diisi jika terdapat :values.',
    'required_without' => 'Kolom :attribute wajib diisi jika :values tidak ada.',
    'required_without_all' => 'Kolom :attribute wajib diisi jika :values tidak ada.',
    'same' => 'Nilai kolom :attribute harus sama dengan :other.',
    'size' => [
        'array' => 'Kolom :attribute harus berisi :size item.',
        'file' => 'Ukuran kolom :attribute harus :size kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => 'Kolom :attribute harus diawali: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => 'Kolom :attribute sudah digunakan.',
    'uploaded' => 'Kolom :attribute gagal diunggah.',
    'uppercase' => 'Kolom :attribute harus berupa huruf besar.',
    'url' => 'Kolom :attribute harus berupa URL yang valid.',
    'ulid' => 'Kolom :attribute harus berupa ULID yang valid.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan khusus per atribut
    |--------------------------------------------------------------------------
    |
    | Ini yang dipakai form permintaan demo. Nama atribut di sini menggantikan
    | nama kolom yang akan tampil di pesan.
    |
    */

    'custom' => [
        'name' => [
            'required' => 'Nama lengkap wajib diisi.',
        ],
        'position' => [
            'max' => [
                'string' => 'Jabatan tidak boleh lebih dari :max karakter.',
            ],
        ],
        'institution' => [
            'required' => 'Nama rumah sakit atau klinik wajib diisi.',
            'max' => [
                'string' => 'Nama institusi tidak boleh lebih dari :max karakter.',
            ],
        ],
        'email' => [
            'required' => 'Email kantor wajib diisi.',
            'email' => 'Format email tidak valid.',
            'max' => [
                'string' => 'Email tidak boleh lebih dari :max karakter.',
            ],
        ],
        'phone' => [
            'required' => 'Nomor WhatsApp atau telepon wajib diisi.',
            'max' => [
                'string' => 'Nomor telepon tidak boleh lebih dari :max karakter.',
            ],
            'regex' => 'Nomor telepon hanya boleh berisi angka, tanda hubung, spasi, dan tanda kurung.',
        ],
        'facility_type' => [
            'required' => 'Jenis fasilitas wajib dipilih.',
            'in' => 'Jenis fasilitas yang dipilih tidak valid.',
        ],
        'bed_count' => [
            'integer' => 'Jumlah tempat tidur harus berupa angka bulat.',
            'min' => [
                'numeric' => 'Jumlah tempat tidur tidak boleh kurang dari :min.',
            ],
            'max' => [
                'numeric' => 'Jumlah tempat tidur tidak boleh lebih dari :max.',
            ],
        ],
        'message' => [
            'max' => [
                'string' => 'Kebutuhan Anda tidak boleh lebih dari :max karakter.',
            ],
        ],
        'username' => [
            'required' => 'Username wajib diisi.',
            'max' => [
                'string' => 'Username tidak boleh lebih dari :max karakter.',
            ],
        ],
        'password' => [
            'required' => 'Kata sandi wajib diisi.',
            'min' => [
                'string' => 'Kata sandi minimal :min karakter.',
            ],
            'confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesan khusus atribut
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'nama lengkap',
        'position' => 'jabatan',
        'institution' => 'nama rumah sakit / klinik',
        'email' => 'email kantor',
        'phone' => 'nomor telepon',
        'facility_type' => 'jenis fasilitas',
        'bed_count' => 'jumlah tempat tidur',
        'message' => 'kebutuhan Anda',
        'company_website' => 'website perusahaan',
        'username' => 'username',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'current_password' => 'kata sandi saat ini',
        'email_confirmation' => 'konfirmasi email',
    ],

];
