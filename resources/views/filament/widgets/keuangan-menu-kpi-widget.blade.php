{{--
    Semua properti struktural/dimensi pakai inline style (bukan kelas
    Tailwind semacam h-11/w-11/gap-4) - pelajaran dari JurusanExamStatusOverviewWidget:
    kelas yang tidak pernah dipakai komponen Filament sendiri tidak
    terkompilasi di panel ini (tidak ada build Tailwind sendiri), jadi
    dijamin aman dengan inline style langsung.
--}}
<x-filament-widgets::widget>
    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
        @foreach ($menus as $menu)
            <a
                href="{{ $menu['url'] }}"
                style="display: inline-flex; align-items: center; gap: 1rem; border-radius: 0.75rem; background-color: #ffffff; padding: 1rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); text-decoration: none;"
            >
                <span
                    style="flex-shrink: 0; width: 44px; height: 44px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background-color: #ccfbf1;"
                >
                    <x-filament::icon :icon="$menu['icon']" style="width: 24px; height: 24px; color: #0f766e;" />
                </span>
                <span style="display: flex; flex-direction: column;">
                    <span style="font-size: 1.5rem; line-height: 1.2; font-weight: 700; color: #111827;">{{ $menu['count'] }}</span>
                    <span style="font-size: 0.875rem; color: #6b7280;">{{ $menu['label'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
