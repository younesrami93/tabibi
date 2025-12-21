@extends('layouts.admin')

@section('title', $patient->full_name)

@section('content')

<div class="row g-4">
    <!-- LEFT COLUMN: Patient Profile Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center pt-5 pb-4">
                <!-- Avatar -->
                <div class="avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                </div>
                
                <h4 class="fw-bold mb-1">{{ $patient->full_name }}</h4>
                <div class="text-muted mb-3">{{ $patient->age }} Years • {{ ucfirst($patient->gender) }}</div>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <a href="tel:{{ $patient->phone }}" class="btn btn-white btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-phone me-1"></i> Call
                    </a>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editPatientModal">
                        <i class="fa-solid fa-pen me-1"></i> Edit
                    </button>
                </div>

                <hr class="opacity-25 my-4">

                <!-- Contact Details -->
                <div class="text-start px-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-light p-2 me-3 text-primary">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Phone Number</small>
                            <span class="fw-medium">{{ $patient->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-light p-2 me-3 text-primary">
                            <i class="fa-solid fa-cake-candles"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Date of Birth</small>
                            <span class="fw-medium">{{ $patient->dob ? $patient->dob->format('d M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-light p-2 me-3 text-primary">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Address</small>
                            <span class="fw-medium">{{ $patient->address ?? 'No address recorded' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-light p-2 me-3 text-primary">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">CIN / ID</small>
                            <span class="fw-medium">{{ $patient->cin ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: History & Tabs -->
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                <ul class="nav nav-tabs nav-fill card-header-tabs" id="patientTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold py-3" data-bs-toggle="tab" data-bs-target="#tab-appointments">
                            <i class="fa-regular fa-calendar-check me-2"></i> Appointments
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-3" data-bs-toggle="tab" data-bs-target="#tab-prescriptions">
                            <i class="fa-solid fa-file-prescription me-2"></i> Prescriptions
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-3" data-bs-toggle="tab" data-bs-target="#tab-notes">
                            <i class="fa-solid fa-notes-medical me-2"></i> Medical Notes
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    
                    <!-- TAB 1: APPOINTMENTS HISTORY -->
                    <div class="tab-pane fade show active" id="tab-appointments">
                        @if($patient->appointments->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark fa-3x opacity-25 mb-3"></i>
                                <h5>No appointments yet.</h5>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->appointments as $appt)
                                        <tr>
                                            <td class="fw-medium">{{ $appt->scheduled_at->format('d M Y, H:i') }}</td>
                                            <td>
                                                @if($appt->type == 'consultation') <span class="badge badge-soft-info">Consultation</span>
                                                @elseif($appt->type == 'urgency') <span class="badge badge-soft-danger">Urgency</span>
                                                @else <span class="badge badge-light text-dark border">{{ ucfirst($appt->type) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($appt->status == 'finished') <span class="badge badge-soft-success">Finished</span>
                                                @elseif($appt->status == 'cancelled') <span class="badge badge-soft-danger">Cancelled</span>
                                                @else <span class="badge badge-soft-warning text-dark">{{ ucfirst($appt->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <!-- View Modal Trigger (Reusing existing modal if needed, or simple link) -->
                                                <button class="btn btn-sm btn-white text-primary"><i class="fa-solid fa-eye"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- TAB 2: PRESCRIPTIONS -->
                    

                    <!-- TAB 3: NOTES -->
                    <div class="tab-pane fade" id="tab-notes">
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                            <i class="fa-solid fa-circle-info me-3 fs-4"></i>
                            <div>
                                <strong>General Medical Notes</strong>
                                <div class="small">These are general observations about the patient (Allergies, Chronic Conditions, etc).</div>
                            </div>
                        </div>
                        
                        <form action="#" method="POST"> <!-- Route needed for updating notes -->
                            @csrf
                            <textarea class="form-control" rows="6" placeholder="Type notes here...">{{ $patient->medical_notes ?? '' }}</textarea>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary fw-bold">Save Notes</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Patient Modal (Placeholder) -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Form to edit patient details goes here.</p>
            </div>
        </div>
    </div>
</div>

@endsection