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
Route::resource('setting/users', App\Http\Controllers\Setting\UserController::class)->except('show');
Route::resource('students', App\Http\Controllers\StudentController::class)->except('show');
Route::resource('lectures', App\Http\Controllers\LectureController::class)->except('show');
Route::resource('setting/view-exam-registrations', App\Http\Controllers\Setting\ExamRegistrationController::class)->except('show');
Route::resource('setting/view-exam-examiners', App\Http\Controllers\Setting\ExamExaminerController::class)->except('show');
Route::resource('departement-exam-registrations', App\Http\Controllers\Examination\ExamRegistrationController::class)->except('show');
Route::resource('departement-exam-examiners', App\Http\Controllers\Examination\ExamRegistrationController::class)->except('show');
Route::get('departement-lectures/{departement_id}', [App\Http\Controllers\Departement\LectureController::class,'index'])->name('departement-lectures.index');
