{{-- Inline style dipakai untuk warna/ukuran (bukan kelas Tailwind semacam
     text-emerald-400) - panel ini tidak punya build Tailwind sendiri, jadi
     kelas warna yang tidak pernah dipakai komponen Filament sendiri tidak
     terkompilasi. Warna disamakan dengan skema sidebar (theme-overrides.blade.php:
     ikon aksen #34d399, teks #ecfdf5 70%). --}}
<div style="display: flex; align-items: center; gap: 0.5rem; color: #ecfdf5;">
    <x-filament::icon
        icon="heroicon-o-academic-cap"
        style="width: 1.75rem; height: 1.75rem; flex-shrink: 0; color: #34d399;"
    />
    <span style="font-weight: 700; font-size: 1.0625rem; line-height: 1.1; letter-spacing: -0.01em;">
        FKIP UNSIL
    </span>
</div>
