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
    return to_route('login');
    // return view('welcome');
});

Auth::routes();
Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('exam/registrations/{student_id}/create', [App\Http\Controllers\ExamRegistrationController::class,'createByStudent'])->name('registrations.student');
    Route::get('exam/registrations/{student_id}/show', [App\Http\Controllers\ExamRegistrationController::class,'showByStudent'])->name('registrations.show.student');
    Route::get('exam/reports/departement', [App\Http\Controllers\ReportController::class,'showExamReport'])->name('reports.by.departement');
    Route::get('exam/reports/periode/{periode}', [App\Http\Controllers\ReportController::class,'showExamReportByPeriode'])->name('reports.by.periode');
    Route::get('exam/reports/section/{report_date_id}', [App\Http\Controllers\ReportController::class,'showExamReportBySection'])->name('reports.by.section');
    Route::get('exam/reports/examiner/{date}', [App\Http\Controllers\ReportController::class,'showExamReportByExaminer'])->name('reports.by.examiner');
    Route::get('exam/reports/{pns}/{report_date_id}', [App\Http\Controllers\ExamPaymentReportController::class,'reportBySection'])->name('reports.section');
    // Route::get('exam/reports/periode/{kode_laporan}', [App\Http\Controllers\ExamPaymentReportController::class,'reportExaminerByPeriode'])->name('reports.examiner.periode');
    Route::get('reports/periode-fresh/{periode}', [App\Http\Controllers\ExamPaymentReportController::class,'reportFreshByPeriode'])->name('reports.fresh.periode');
    // Route::get('reports/date-fresh/{date}', [App\Http\Controllers\ExamPaymentReportController::class,'reportFreshByDate'])->name('reports.fresh.date');
    Route::post('exam/massreports/{date}', [App\Http\Controllers\ExamPaymentReportController::class,'massReportByDate'])->name('reports.mass');
    Route::get('exam/reportdates/reported-list/{report_date_id}', [App\Http\Controllers\ReportDateController::class,'reportedList'])->name('reportdates.reportedlist');
    Route::put('exam/reportdates/set-report-date/{examregistration}', [App\Http\Controllers\ReportDateController::class,'setReportDate'])->name('reportdates.setreportdate');
    Route::get('exam/reportdates/not-reported-list/{report_date_id}', [App\Http\Controllers\ReportDateController::class,'notReportedList'])->name('reportdates.notreportedlist');
    Route::resource('users', App\Http\Controllers\UserController::class)->except('show');
    Route::resource('students', App\Http\Controllers\StudentController::class)->except('show');
    Route::resource('lectures', App\Http\Controllers\LectureController::class)->except('show');
    Route::resource('exam/registrations', App\Http\Controllers\ExamRegistrationController::class)->except('create');
    Route::resource('exam/paymentreports', App\Http\Controllers\ExamPaymentReportController::class);
    Route::resource('exam/reportdates', App\Http\Controllers\ReportDateController::class);
});
