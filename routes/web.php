<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(\Filament\Facades\Filament::getUrl());
    }

    return view('landing');
});

// Login sungguhan ada di sini (bukan cuma redirect ke /admin/login) - dipakai
// langsung lewat halaman kustom App\Filament\Auth\Login (tetap tampilan/layout
// Filament). Route bernama "login" ini juga dibutuhkan supaya route('login')
// di app/Http/Middleware/Authenticate.php (dipakai rute lama yang belum
// pindah ke Filament, mis. /students) tidak error "Route [login] not defined".
//
// HANYA middleware KHUSUS Filament yang ditambah di sini (bukan
// $panel->getMiddleware() penuh) - EncryptCookies/StartSession/
// VerifyCsrfToken/dkk SUDAH dipasang otomatis oleh grup 'web' (lihat
// RouteServiceProvider) karena file ini memang didaftarkan lewat grup itu.
// Menambahkannya lagi berarti dijalankan DUA KALI per request - EncryptCookies
// yang kedua mencoba mendekripsi cookie sesi yang sudah didekripsi oleh yang
// pertama, sesi jadi rusak di tengah request, dan hasilnya token CSRF di HTML
// tidak lagi cocok dengan sesi yang benar-benar tersimpan -> error 419 saat
// submit form (baru ketahuan pas login sungguhan dicoba, bukan pas GET biasa).
Route::get('/login', \App\Filament\Auth\Login::class)
    ->middleware([
        'panel:admin',
        \Filament\Http\Middleware\DisableBladeIconComponents::class,
        \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
    ])
    ->name('login');

// 'logout' TIDAK ikut dimatikan otomatis oleh opsi di atas (beda dari
// 'login'/'register') - lihat vendor/laravel/ui/src/AuthRouteMethods.php,
// defaultnya selalu true dan diarahkan ke Auth\LoginController@logout, yang
// sudah dihapus bareng LoginController lama. Kalau tidak dimatikan di sini,
// tombol "Logout" di layouts.app (dipakai /users, /students, /lectures, dst)
// akan error "Target class [...LoginController] does not exist" saat diklik.
Auth::routes(['register' => false, 'login' => false, 'logout' => false]);

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('users', App\Http\Controllers\UserController::class)->except('show');
        Route::post('users/{user}/impersonate', [App\Http\Controllers\ImpersonateController::class, 'take'])->name('impersonate.take');
    });
    Route::post('impersonate/leave', [App\Http\Controllers\ImpersonateController::class, 'leave'])->name('impersonate.leave');
    Route::group(['middleware' => ['role:jurusan']], function () {
        Route::get('exam/registrations/{student_id}/create', [App\Http\Controllers\ExamRegistrationController::class,'createByStudent'])->name('registrations.student');
        Route::get('exam/registrations/{student_id}/show', [App\Http\Controllers\ExamRegistrationController::class,'showByStudent'])->name('registrations.show.student');
        Route::get('exam/reports/departement', [App\Http\Controllers\ReportController::class,'showExamReport'])->name('reports.by.departement');
        Route::get('exam/reports/date/{date}', [App\Http\Controllers\ReportController::class,'showExamReportBydate'])->name('reports.by.date');
        Route::get('exam/reports/examiner/{date}', [App\Http\Controllers\ReportController::class,'showExamReportByExaminer'])->name('reports.by.examiner');
    });
    Route::group(['middleware' => ['role:keuangan']], function () {
        Route::get('exam/reports/{pns}/{report_date_id}', [App\Http\Controllers\ExamPaymentReportController::class,'reportBySection'])->name('reports.section');
        Route::get('reports/empty-zero', [App\Http\Controllers\ExamPaymentReportController::class,'emptyZeroHonor'])->name('reports.empty-zero');
        Route::get('reports/periode-fresh/{periode}', [App\Http\Controllers\ExamPaymentReportController::class,'reportFreshByPeriode'])->name('reports.fresh.periode');
        Route::post('exam/massreports/{periode}/{date}', [App\Http\Controllers\ExamPaymentReportController::class,'massReportByDate'])->name('reports.mass');
        Route::get('exam/reportdates/reported-list/{report_date_id}', [App\Http\Controllers\ReportDateController::class,'reportedList'])->name('reportdates.reportedlist');
        Route::put('exam/reportdates/set-report-date/{examregistration}', [App\Http\Controllers\ReportDateController::class,'setReportDate'])->name('reportdates.setreportdate');
        Route::get('exam/reportdates/not-reported-list/{report_date_id}', [App\Http\Controllers\ReportDateController::class,'notReportedList'])->name('reportdates.notreportedlist');
        Route::get('exam/reportdates/sidang-confirmed/{report_date_id}', [App\Http\Controllers\ReportDateController::class,'sidangConfirmedList'])->name('reportdates.sidangconfirmedlist');
        Route::put('exam/reportdates/confirm-sidang-cascade/{examregistration}', [App\Http\Controllers\ReportDateController::class,'confirmSidangCascade'])->name('reportdates.confirmsidangcascade');
        Route::resource('exam/reportdates', App\Http\Controllers\ReportDateController::class);
        Route::resource('exam/paymentreports', App\Http\Controllers\ExamPaymentReportController::class);
    });
    Route::resource('exam/registrations', App\Http\Controllers\ExamRegistrationController::class)->except('create');
    Route::resource('students', App\Http\Controllers\StudentController::class)->except('show');
    Route::resource('lectures', App\Http\Controllers\LectureController::class)->except('show');
});
