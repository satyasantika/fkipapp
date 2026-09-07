<div style="margin-top: 0.25rem; display: flex; flex-direction: column; gap: 0.125rem;">
    <div class="flex items-center text-gray-500 dark:text-gray-400" style="gap: 0.25rem; font-size: 0.75rem; line-height: 1rem;">
        <x-filament::icon icon="heroicon-o-arrow-down-tray" style="height: 0.75rem; width: 0.75rem; flex-shrink: 0;" />
        <span>Terakhir ditarik: {{ $lastPulled }}</span>
    </div>
    <div class="flex items-center text-gray-500 dark:text-gray-400" style="gap: 0.25rem; font-size: 0.75rem; line-height: 1rem;">
        <x-filament::icon :icon="$lockIcon" style="height: 0.75rem; width: 0.75rem; flex-shrink: 0;" />
        <span>Kunci/buka terakhir: {{ $lockInfo }}</span>
    </div>
</div>
