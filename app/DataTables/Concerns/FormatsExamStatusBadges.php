<?php

namespace App\DataTables\Concerns;

trait FormatsExamStatusBadges
{
    protected function dilaporkanIcon(bool $dilaporkan): string
    {
        return $dilaporkan
            ? '<i class="bi bi-check-circle-fill text-success" title="sudah dilaporkan"></i>'
            : '<i class="bi bi-x-circle-fill text-danger" title="belum dilaporkan"></i>';
    }

    protected function examTypeBadge(?string $ujian): string
    {
        $colors = [
            'sempro' => 'bg-secondary',
            'semhas' => 'bg-info text-dark',
            'sidang' => 'bg-primary',
        ];

        $class = $colors[$ujian] ?? 'bg-light text-dark';

        return '<span class="badge '.$class.'">'.e($ujian ?? '').'</span>';
    }
}
