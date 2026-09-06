{{-- Layout kosong - dipakai halaman yang view-nya sudah dokumen HTML utuh
     sendiri (mis. login kustom), supaya tidak ikut dibungkus layout
     bawaan Filament (filament-panels::components.layout.simple, yang
     menyuntikkan <html> lain + kelas fi-simple-main-ctn/latar #FAFAFA di
     luar konten kita - menyebabkan dua dokumen HTML bersarang). --}}
{{ $slot }}
