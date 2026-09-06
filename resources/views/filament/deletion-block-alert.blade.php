@if ($reason)
    <div class="flex items-start gap-x-3 rounded-lg bg-warning-50 px-4 py-3 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-500/10 dark:ring-warning-400/30">
        <x-filament::icon
            icon="heroicon-o-lock-closed"
            class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400"
        />
        <p class="text-sm text-warning-700 dark:text-warning-300">
            {{ $reason }}
        </p>
    </div>
@endif
