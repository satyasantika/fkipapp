@include('errors._page', [
    'code' => $exception->getStatusCode(),
    'title' => 'Terjadi kesalahan server',
    'description' => 'Ada yang tidak berjalan semestinya di server kami. Tim teknis sudah diberi tahu - silakan coba lagi sebentar lagi.',
])
