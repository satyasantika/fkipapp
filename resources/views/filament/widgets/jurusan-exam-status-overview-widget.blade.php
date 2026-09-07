{{--
    Donat CSS conic-gradient murni (tanpa Chart.js) - warna pakai hex literal
    langsung supaya dijamin tampil apa adanya di browser manapun.

    PENTING: SEMUA properti struktural/dimensi di sini (grid 2-kolom, ukuran
    lingkaran donat) sengaja pakai inline style, BUKAN kelas utilitas
    Tailwind seperti lg:grid-cols-2/h-24/w-24/h-16/w-16 - kelas semacam itu
    kelihatan masuk akal tapi TIDAK TERKOMPILASI di panel ini kalau Filament
    sendiri tidak pernah memakainya di komponennya (panel ini tidak punya
    build Tailwind sendiri, cuma CSS Filament yang sudah di-tree-shake).
    Terbukti langsung: versi awal pakai class-class itu, hasilnya 2 kartu
    malah numpuk selebar halaman (bukan 2 kolom) dan lingkaran donatnya
    menyusut ikut ukuran lubangnya (tidak ada lebar cincin sama sekali,
    conic-gradient jadi kelihatan kosong/nyaris tak terlihat).
--}}
<x-filament-widgets::widget>
    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
        {{-- Kartu 1: Total ujian + donat sudah dilaporkan --}}
        <div
            style="flex: 1 1 320px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 1rem; padding: 1.25rem; background-color: #eff6ff;"
        >
            <div>
                <div style="font-size: 2.25rem; line-height: 1; font-weight: 700; color: #111827;">{{ $total }}</div>
                <div style="margin-top: 0.25rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.025em; color: #6b7280;">TOTAL UJIAN</div>
                <div style="margin-top: 0.5rem; font-size: 0.875rem; font-weight: 600; color: #16a34a;">
                    {{ $sudah }} Sudah Dibayarkan
                </div>
            </div>

            <div
                style="flex-shrink: 0; width: 96px; height: 96px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: conic-gradient(#16a34a {{ $sudahPct }}%, #e2e8f0 0);"
            >
                <div style="width: 64px; height: 64px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #eff6ff;">
                    <span style="font-size: 1rem; font-weight: 700; color: #111827;">{{ $sudahPct }}%</span>
                    <span style="font-size: 9px; font-weight: 600; letter-spacing: 0.025em; color: #9ca3af;">DIBAYARKAN</span>
                </div>
            </div>
        </div>

        {{-- Kartu 2: Belum dilaporkan + donat proporsi jenis ujian --}}
        <div
            style="flex: 1 1 320px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 1rem; padding: 1.25rem; background-color: #fffbeb;"
        >
            <div>
                <div style="font-size: 2.25rem; line-height: 1; font-weight: 700; color: #111827;">{{ $belum }}</div>
                <div style="margin-top: 0.25rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.025em; color: #6b7280;">
                    BELUM DIBAYARKAN
                    <span style="font-weight: 700; color: #d97706;">{{ $belumPct }}%</span>
                </div>
            </div>

            <div
                style="flex-shrink: 0; width: 96px; height: 96px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: conic-gradient(#f59e0b 0%, #f59e0b {{ $stopSempro }}%, #2563eb {{ $stopSempro }}%, #2563eb {{ $stopSemhas }}%, #10b981 {{ $stopSemhas }}%, #10b981 100%);"
            >
                <div style="width: 64px; height: 64px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #fffbeb;">
                    <span style="font-size: 1rem; font-weight: 700; color: #111827;">{{ $belum }}</span>
                    <span style="font-size: 9px; font-weight: 600; letter-spacing: 0.025em; color: #9ca3af;">UJIAN</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.375rem; font-size: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <span style="flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b;"></span>
                    <span style="color: #4b5563;">Sempro</span>
                    <span style="font-weight: 600; color: #111827;">{{ $belumSempro }}</span>
                    <span style="color: #9ca3af;">({{ $pctSempro }}%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <span style="flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%; background-color: #2563eb;"></span>
                    <span style="color: #4b5563;">Semhas</span>
                    <span style="font-weight: 600; color: #111827;">{{ $belumSemhas }}</span>
                    <span style="color: #9ca3af;">({{ $pctSemhas }}%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <span style="flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                    <span style="color: #4b5563;">Sidang</span>
                    <span style="font-weight: 600; color: #111827;">{{ $belumSidang }}</span>
                    <span style="color: #9ca3af;">({{ $pctSidang }}%)</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
