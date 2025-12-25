<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogItemController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicImageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MedicalServiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionTemplateController;
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

        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');


        Route::get('/appointments/fetch', [AppointmentController::class, 'fetchTable'])
            ->name('appointments.fetch');

        Route::resource('prescriptions_templates', PrescriptionTemplateController::class)
            ->parameters(['prescriptions_templates' => 'template']);

        Route::put('/appointments/{id}/complete-control', [AppointmentController::class, 'markControlDone'])
            ->name('appointments.complete_control');

        Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
            ->name('appointments.update_status');

        Route::get('/api/patients/search', [PatientController::class, 'search'])
            ->name('api.patients.search');

        Route::put('/appointments/{appointment}/finish', [AppointmentController::class, 'finish'])
            ->name('appointments.finish');

        Route::get('/documents/{id}/print', [DocumentController::class, 'printPreview'])->name('documents.print');
        Route::get('/documents/print-type/{type}', [DocumentController::class, 'printPreviewByType'])
            ->name('documents.print.type');
        // Media Library Routes
        Route::get('/api/images', [ClinicImageController::class, 'index'])->name('images.index');

        Route::get('/api/catalog/search', [CatalogItemController::class, 'search'])->name('api.catalog.search');

        Route::get('/appointments/{id}/modal', [AppointmentController::class, 'showModal'])->name('appointments.modal');
        Route::get('/appointments/{id}/finish-modal', [AppointmentController::class, 'showFinishModal'])->name('appointments.finish-modal');
    });

    // -------------------------------------------------------
    // GROUP 3: DOCTOR ONLY (Sensitive Medical/Financial)
    // -------------------------------------------------------
    Route::middleware('role:doctor')->group(function () {
        Route::resource('services', MedicalServiceController::class)->except(['create', 'show', 'edit']);
        Route::resource('secretaries', SecretaryController::class)->except(['create', 'show', 'edit']);

        Route::resource('catalog', CatalogItemController::class)->only(['index', 'store', 'destroy']);

        // route for document editor
        Route::get('/document-editor', function () {
            return view('layouts.editor.document_editor');
        })->name('document.editor');


        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::put('/documents/{id}', [DocumentController::class, 'update'])->name('documents.update');
        Route::post('/documents/{id}/duplicate', [DocumentController::class, 'duplicate'])
            ->name('documents.duplicate');
        // Editor Route
        Route::get('/documents/{id}/edit', [DocumentController::class, 'edit'])->name('documents.editor');
        Route::post('/documents/{id}/save', [DocumentController::class, 'updateContent'])->name('documents.update_content');

        Route::post('/api/images', [ClinicImageController::class, 'store'])->name('images.store');
        Route::delete('/api/images/{id}', [ClinicImageController::class, 'destroy'])->name('images.destroy');

    });


    Route::get('/global-search', [DashboardController::class, 'globalSearch'])->name('global.search');
});




// Redirect home to login
Route::get('/', function () {
    return redirect()->route('login');
});