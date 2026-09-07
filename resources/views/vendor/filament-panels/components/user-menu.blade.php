@php
    $user = filament()->auth()->user();
    $items = filament()->getUserMenuItems();

    $profileItem = $items['profile'] ?? $items['account'] ?? null;
    $profileItemUrl = $profileItem?->getUrl();
    $profilePage = filament()->getProfilePage();
    $hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

    $logoutItem = $items['logout'] ?? null;

    $items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown
    placement="bottom-end"
    teleport
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    "
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0"
        >
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

        @if ($hasProfileItem)
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    :color="$profileItem?->getColor()"
                    :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
                    :href="$profileItemUrl ?? filament()->getProfileUrl()"
                    :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null"
                    tag="a"
                >
                    {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user) }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        @else
            <x-filament::dropdown.header
                :color="$profileItem?->getColor()"
                :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            >
                {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
            </x-filament::dropdown.header>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
            @php
                $itemPostAction = $item->getPostAction();
            @endphp

            <x-filament::dropdown.list.item
                :action="$itemPostAction"
                :color="$item->getColor()"
                :href="$item->getUrl()"
                :icon="$item->getIcon()"
                :method="filled($itemPostAction) ? 'post' : null"
                :tag="filled($itemPostAction) ? 'form' : 'a'"
                :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null"
            >
                {{ $item->getLabel() }}
            </x-filament::dropdown.list.item>
        @endforeach

        <x-filament::dropdown.list.item
            :color="$logoutItem?->getColor()"
            :icon="$logoutItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ?? 'heroicon-m-arrow-left-on-rectangle'"
            tag="button"
            x-on:click="$dispatch('open-modal', { id: 'confirm-logout' })"
        >
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>

{{-- Modal konfirmasi logout (bukan browser confirm() biasa) - id dicocokkan
    lewat event 'open-modal' yang di-dispatch tombol logout di atas, lihat
    vendor/filament/support/resources/views/components/modal/index.blade.php
    (listener x-on:{{ $openEventName }}.window mencocokkan $event.detail.id). --}}
<x-filament::modal id="confirm-logout" width="sm" icon="heroicon-o-arrow-left-on-rectangle" icon-color="danger">
    <x-slot name="heading">
        Keluar dari akun ini?
    </x-slot>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        @if (session()->has('impersonator_id'))
            Anda sedang masuk sebagai pengguna lain (impersonate). Anda bisa kembali ke akun admin saja, atau keluar sepenuhnya dari kedua akun.
        @else
            Anda perlu login kembali untuk mengakses panel ini.
        @endif
    </p>

    <x-slot name="footerActions">
        <x-filament::button color="gray" x-on:click="close">
            Batal
        </x-filament::button>

        @if (session()->has('impersonator_id'))
            <form action="{{ route('impersonate.leave') }}" method="post">
                @csrf

                <x-filament::button type="submit" color="warning">
                    Kembali ke akun admin
                </x-filament::button>
            </form>
        @endif

        <form action="{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}" method="post">
            @csrf

            <x-filament::button type="submit" color="danger">
                Ya, keluar
            </x-filament::button>
        </form>
    </x-slot>
</x-filament::modal>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}
