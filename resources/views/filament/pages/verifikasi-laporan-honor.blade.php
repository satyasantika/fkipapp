<x-filament-panels::page>
    <div style="max-width: 24rem;">
        <label for="report-date-select" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">
            Periode Penarikan Laporan
        </label>
        <select
            id="report-date-select"
            wire:model.live="reportDateId"
            style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; font-size: 0.875rem;"
        >
            <option value="">Pilih periode...</option>
            @foreach ($this->getReportDateOptions() as $id => $label)
                <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
