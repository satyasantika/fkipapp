<div
    class="flex flex-col gap-3"
    x-data="{
        // Jumlah kartu per halaman dihitung dari ukuran layar sungguhan
        // (bukan angka tetap) - .fi-ta-content-grid pakai auto-fill
        // (theme-overrides.blade.php), jadi getComputedStyle().gridTemplateColumns
        // memberi jumlah kolom yang BENAR-BENAR dipakai browser di lebar
        // kontainer saat ini (auto-fill selalu membuat sebanyak track yang
        // muat, terlepas dari jumlah kartu yang ada - beda dengan auto-fit
        // yang mengecilkan track kosong, jadi pengukuran ini tetap akurat
        // walau kartu yang ada sedikit).
        recalcPerPage() {
            const grid = this.$el.querySelector('.fi-ta-content-grid');
            if (! grid) return;

            const columns = window.getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length || 1;

            const firstCard = grid.firstElementChild;
            const gapPx = parseFloat(window.getComputedStyle(grid).rowGap) || 16;
            const cardHeight = firstCard ? firstCard.getBoundingClientRect().height : 140;

            // 160px dikurangi untuk perkiraan tinggi header+footer slide-over
            // (judul, tombol Tutup, padding) yang bukan bagian grid kartu.
            const top = grid.getBoundingClientRect().top;
            const available = Math.max(window.innerHeight - top - 160, cardHeight);
            const rows = Math.max(1, Math.floor(available / (cardHeight + gapPx)));

            const needed = columns * rows;
            const presets = [12, 24, 48, 96];
            const chosen = presets.find((p) => p >= needed) ?? 'all';

            if (String(this.$wire.tableRecordsPerPage) !== String(chosen)) {
                this.$wire.set('tableRecordsPerPage', chosen);
            }
        },
    }"
    x-init="
        recalcPerPage();
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => recalcPerPage(), 200);
        });
    "
>
    <div class="flex items-center gap-3">
        {{-- Tombol biasa (bukan <x-filament::badge>, itu terlalu kecil) -
             warna lewat kelas .fi-filter-toggle-on (theme-overrides.blade.php,
             pakai rgb(var(--success-600))) supaya tidak mengulang kesalahan
             bg-success-600 lama yang tidak pernah benar-benar terkompilasi.
             h-9 dipasang di sini dan di input pencarian supaya tingginya
             persis sama. --}}
        <button
            type="button"
            wire:click="toggleSudahSidang"
            @class([
                'inline-flex h-9 shrink-0 items-center whitespace-nowrap rounded-full px-3 text-sm font-medium transition',
                'fi-filter-toggle-on' => $sudahSidangOnly,
                'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10' => ! $sudahSidangOnly,
            ])
        >
            {{ $sudahSidangOnly ? 'Buang Filter' : 'Filter Sidang' }}
        </button>

        <input
            type="text"
            wire:model.live.debounce.300ms="studentSearch"
            placeholder="Cari NIM atau nama..."
            class="block h-9 w-full min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
        />
    </div>

    {{ $this->table }}
</div>
