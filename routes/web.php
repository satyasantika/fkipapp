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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('exam/registrations/{student_id}/create', [App\Http\Controllers\ExamRegistrationController::class,'createByStudent'])->name('registrations.student');
Route::get('exam/registrations/{student_id}/show', [App\Http\Controllers\ExamRegistrationController::class,'showByStudent'])->name('registrations.show.student');
Route::get('exam/reports/departement', [App\Http\Controllers\ReportController::class,'showExamReport'])->name('reports.by.departement');
Route::get('exam/reports/periode/{periode}', [App\Http\Controllers\ReportController::class,'showExamReportByPeriode'])->name('reports.by.periode');
Route::get('exam/reports/date/{date}', [App\Http\Controllers\ReportController::class,'showExamReportByDate'])->name('reports.by.date');
Route::get('exam/reports/{date}', [App\Http\Controllers\ExamPaymentReportController::class,'reportByDate'])->name('reports.date');
Route::resource('users', App\Http\Controllers\UserController::class)->except('show');
Route::resource('students', App\Http\Controllers\StudentController::class)->except('show');
Route::resource('lectures', App\Http\Controllers\LectureController::class)->except('show');
Route::resource('exam/registrations', App\Http\Controllers\ExamRegistrationController::class)->except('create');
Route::resource('exam/paymentreports', App\Http\Controllers\ExamPaymentReportController::class);
