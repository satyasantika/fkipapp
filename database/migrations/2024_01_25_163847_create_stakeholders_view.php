<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // view dosen
        $query = "SELECT lectures.*,departements.name AS prodi FROM lectures JOIN departements ON departements.id=lectures.departement_id";
        Schema::createOrReplaceView('view_lectures', $query);
        // view mahasiswa
        $query = "SELECT students.*,
                        departements.name AS prodi,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.pembimbing1) AS pembimbing_1,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.pembimbing2) AS pembimbing_2,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.penguji1) AS penguji_1,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.penguji2) AS penguji_2,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.penguji3) AS penguji_3,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=students.ketuapenguji) AS ketua
                FROM students
                JOIN departements ON departements.id=students.departement_id";
        Schema::createOrReplaceView('view_students', $query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropViewIfExists('view_students');
        Schema::dropViewIfExists('view_lectures');
    }
};
