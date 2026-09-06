<style>
    /* Font display/heading yang sama dengan halaman login (resources/views/filament/pages/auth/login.blade.php)
       - Fraunces untuk judul, dipertahankan konsisten di seluruh panel supaya identitas
       visualnya tidak berhenti di pintu masuk saja. */
    @import url('https://fonts.bunny.net/css?family=fraunces:600,700');

    .fi-header-heading,
    .fi-simple-header-heading,
    .fi-modal-heading,
    .fi-section-header-heading,
    .fi-logo {
        font-family: 'Fraunces', ui-serif, Georgia, serif;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    /* Sidebar & topbar: chrome persisten yang "membungkus" konten, dibuat
       teal gelap sama seperti --teal-950 di halaman login, TIDAK bergantung
       mode gelap/terang Filament (elemen brand, bukan konten). Semua warna
       teks di dalamnya dihitung manual supaya rasio kontrasnya lolos WCAG
       AA (>=4.5:1 teks, >=3:1 ikon/komponen UI) - tidak bisa di-screenshot
       langsung di lingkungan ini, jadi dipastikan lewat perhitungan luminansi:
       label (mint-50 70% di atas teal-950) ~7.4:1, ikon (mint-50 55%) ~5.2:1,
       aksen aktif/hover (emerald-400) ~7.6:1. */
    .fi-sidebar,
    .fi-sidebar-header,
    .fi-topbar nav {
        background-color: #042f2e !important;
    }

    .fi-sidebar-item-label {
        color: rgba(236, 253, 245, 0.75);
    }

    .fi-sidebar-item-icon,
    .fi-sidebar-group-icon {
        color: rgba(236, 253, 245, 0.55);
    }

    .fi-sidebar-group-label {
        color: rgba(236, 253, 245, 0.5);
    }

    .fi-sidebar-item:hover .fi-sidebar-item-button {
        background-color: rgba(255, 255, 255, 0.06);
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
        background-color: rgba(52, 211, 153, 0.14);
    }

    .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar-item:hover .fi-sidebar-item-label {
        color: #ecfdf5;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar-item:hover .fi-sidebar-item-icon {
        color: #34d399;
    }

    .fi-topbar .fi-icon-btn {
        color: rgba(236, 253, 245, 0.7);
    }

    .fi-topbar .fi-icon-btn:hover {
        color: #ecfdf5;
    }

    /* Halaman utama (kanvas di sekitar kartu/tabel putih): tint mint sangat
       tipis alih-alih abu-abu polos, hanya di mode terang - mode gelap
       Filament sudah punya kanvas gelapnya sendiri, tidak diganggu. */
    html:not(.dark) body {
        background-color: #f4faf8;
    }
</style>
