<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicalServiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SecretaryController;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});



Route::middleware('auth')->group(function () {

    // Everyone can see the Dashboard (The Controller handles the logic inside)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    // -------------------------------------------------------
    // GROUP 1: SUPER ADMIN ONLY
    // -------------------------------------------------------
    Route::middleware('role:super_admin')->group(function () {

        // Clinics Management
        Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
        Route::post('/clinics/{id}/update', [ClinicController::class, 'update'])->name('clinics.update');

        // Doctors Management (Platform Level)
        Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
        Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
        Route::post('/doctors/{id}/update', [DoctorController::class, 'update'])->name('doctors.update');


        Route::resource('services', MedicalServiceController::class)->except(['create', 'show', 'edit']);

    });

    // -------------------------------------------------------
    // GROUP 2: DOCTORS & ASSISTANTS (Cabinet Access)
    // -------------------------------------------------------
    Route::middleware('role:doctor,secretary')->group(function () {
        Route::resource('patients', PatientController::class);

        Route::put('/appointments/{id}/complete-control', [AppointmentController::class, 'markControlDone'])
            ->name('appointments.complete_control');

        Route::get('/api/patients/search', function (Request $request) {
            $search = $request->query('q');
            return Patient::where('clinic_id', Auth::user()->clinic_id)
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                })
                ->limit(10)
                ->get(['id', 'first_name', 'last_name', 'phone']);
        })->name('api.patients.search');
    });

    // -------------------------------------------------------
    // GROUP 3: DOCTOR ONLY (Sensitive Medical/Financial)
    // -------------------------------------------------------
    Route::middleware('role:doctor')->group(function () {
        Route::resource('services', MedicalServiceController::class)->except(['create', 'show', 'edit']);
        Route::resource('secretaries', SecretaryController::class)->except(['create', 'show', 'edit']);
    });

});




// Redirect home to login
Route::get('/', function () {
    return redirect()->route('login');
});