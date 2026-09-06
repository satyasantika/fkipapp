@include('errors._page', [
    'code' => 429,
    'title' => 'Terlalu banyak percobaan',
    'description' => 'Anda mengirim permintaan terlalu sering dalam waktu singkat. Tunggu sebentar lalu coba lagi.',
])
