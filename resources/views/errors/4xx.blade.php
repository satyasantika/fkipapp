@include('errors._page', [
    'code' => $exception->getStatusCode(),
    'title' => 'Permintaan tidak dapat diproses',
    'description' => 'Terjadi kesalahan pada permintaan Anda. Coba kembali ke dashboard, atau hubungi admin fakultas bila terus terjadi.',
])
