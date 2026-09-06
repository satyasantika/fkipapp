<?php

namespace App\Models\Concerns;

/**
 * Kolom relasi di skema ini (departement_id, pembimbing1_id, exam_type_id,
 * report_date_id, dst.) tidak punya foreign key constraint di level database
 * - semua migration memakai foreignIdFor()/unsignedBigInteger tanpa
 * ->constrained() (lihat mis. 2024_01_25_063829_create_stakeholders_table.php).
 * Jadi "tidak bisa dihapus kalau masih dipakai di tabel lain" harus dijaga di
 * sini (event Eloquent, bukan DB), supaya berlaku di SEMUA jalur hapus -
 * Filament, controller lama (LectureController dkk), tinker, seeder - bukan
 * cuma satu tempat yang bisa lupa dipasangi pengecekan.
 */
trait GuardsDeletionWhenReferenced
{
    protected static function bootGuardsDeletionWhenReferenced(): void
    {
        static::deleting(function (self $model) {
            if ($reason = $model->deletionBlockReason()) {
                throw new \RuntimeException($reason);
            }
        });
    }

    /**
     * Null kalau aman dihapus, atau pesan bahasa Indonesia yang menjelaskan
     * data lain apa saja yang masih memakainya.
     */
    abstract public function deletionBlockReason(): ?string;
}
