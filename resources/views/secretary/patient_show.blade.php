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
                            

                        <div style="">
    <table class="table table-hover align-middle">
        <thead class="bg-light text-muted small text-uppercase">
            <tr>
                <th class="ps-4" width="20%">Date & Time</th>
                <th width="35%">Visit Details</th>
                <th width="25%">Status & Payment</th>
                <th class="text-end pe-4" width="20%">Actions</th>
            </tr>
        </thead>
        <tbody class="border-top-0">
            @forelse($patient->appointments as $appt)
                <tr>
                    {{-- 1. DATE --}}
                    <td class="ps-4">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">{{ $appt->scheduled_at->format('d M Y') }}</span>
                            <small class="text-muted">{{ $appt->scheduled_at->format('H:i') }}</small>
                        </div>
                    </td>

                    {{-- 2. VISIT DETAILS (Type + Services + Notes) --}}
                    <td>
                        <div class="d-flex flex-column gap-1">
                            {{-- Type Badge --}}
                            <div>
                                @if($appt->type == 'consultation') 
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">Consultation</span>
                                @elseif($appt->type == 'urgency') 
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10">Urgency</span>
                                @elseif($appt->type == 'control') 
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10">Control</span>
                                @else 
                                    <span class="badge bg-light text-dark border">{{ ucfirst($appt->type) }}</span>
                                @endif
                            </div>
                            
                            {{-- Services / Notes Summary --}}
                            @if($appt->services->count() > 0)
                                <small class="text-dark fw-medium text-truncate" style="max-width: 250px;">
                                    <i class="fa-solid fa-notes-medical text-muted me-1"></i>
                                    {{ $appt->services->pluck('name')->implode(', ') }}
                                </small>
                            @elseif($appt->notes)
                                <small class="text-muted fst-italic text-truncate" style="max-width: 250px;">
                                    "{{ Str::limit($appt->notes, 40) }}"
                                </small>
                            @else
                                <small class="text-muted opacity-50">-</small>
                            @endif
                        </div>
                    </td>

                    {{-- 3. STATUS & PAYMENT --}}
                    <td>
                        <div class="d-flex flex-column gap-1">
                            {{-- Workflow Status --}}
                            <div>
                                @if($appt->status == 'finished') 
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-check me-1"></i>Finished</span>
                                @elseif($appt->status == 'pending_payment') 
                                    <span class="badge bg-warning bg-opacity-10 text-dark"><i class="fa-solid fa-clock me-1"></i>Pending Payment</span>
                                @elseif($appt->status == 'cancelled') 
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Cancelled</span>
                                @else 
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($appt->status) }}</span>
                                @endif
                            </div>

                            {{-- Financial Summary --}}
                            @if(!in_array($appt->status, ['cancelled', 'scheduled']))
                                <div class="small">
                                    <span class="fw-bold text-dark">{{ number_format($appt->total_price, 2) }} DH</span>
                                    <span class="text-muted mx-1">•</span>
                                    @if($appt->is_paid)
                                        <span class="text-success fw-bold x-small text-uppercase">Paid</span>
                                    @else
                                        <span class="text-danger fw-bold x-small text-uppercase">Unpaid</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- 4. ACTIONS (Buttons) --}}
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            
                            {{-- A. Prescription Print Button (Dropdown if multiple) --}}
                            @php 
                                $prescriptions = $appt->prescription ?? []; 
                                // Normalize if single block legacy format
                                if(isset($prescriptions['name'])) $prescriptions = [$prescriptions];
                            @endphp

                            @if(!empty($prescriptions))
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success border-success border-opacity-25 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-print me-1"></i> Rx
                                    </button>
                                    <ul class="dropdown-menu shadow-sm border-0">
                                        @foreach($prescriptions as $index => $block)
                                            <li>
                                                <a class="dropdown-item small" target="_blank"
                                                   href="{{ route('documents.print.type', 'prescription') }}?model=appointment&id={{ $appt->id }}&rx_index={{ $index }}">
                                                    <i class="fa-solid fa-file-prescription me-2 text-muted"></i>
                                                    Print {{ $block['title'] ?? 'Prescription #'.($index+1) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- B. View Details Button --}}
                            {{-- Note: Ensure you include the modal partial at the bottom of your file for this to work --}}
                            <button class="btn btn-sm btn-light border shadow-sm text-secondary" 
                                    onclick="openFullModal('{{ route('appointments.modal', $appt->id) }}')"
                                    title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            

                        </div>
                    </td>
                </tr>

                {{-- INCLUDE MODAL FOR THIS ROW (Or keep it outside loop if loading dynamically) --}}
                @include('layouts.partials.appointment_details_modal', ['appt' => $appt])

            @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <div class="opacity-50 mb-2"><i class="fa-solid fa-calendar-xmark fs-3"></i></div>
                        <div>No appointments found for this patient.</div>
                    </td>
                </tr>
            @endforelse
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
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('patients.update', $patient->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            @method('PUT')
            
            <div class="modal-header border-0 pb-0 ps-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Edit Patient Details</h5>
                    <p class="text-muted small mb-0">Update personal information for {{ $patient->first_name }} {{ $patient->last_name }}</p>
                </div>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                {{-- SECTION 1: IDENTITY --}}
                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                    <i class="fa-solid fa-id-card me-2"></i>Identity
                </h6>
                <div class="row g-3 mb-4">
                    {{-- Row 1: Names --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
                    </div>

                    {{-- Row 2: CIN & Gender --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">CIN (National ID)</label>
                        <input type="text" name="cin" class="form-control" value="{{ old('cin', $patient->cin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Gender <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="male" {{ $patient->gender == 'male' ? 'checked' : '' }}>
                                <label class="form-check-label" for="editGenderMale">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="female" {{ $patient->gender == 'female' ? 'checked' : '' }}>
                                <label class="form-check-label" for="editGenderFemale">Female</label>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Birth Date & Phone (Moved here as requested) --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($patient->birth_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="tel" name="phone" class="form-control border-start-0 ps-0" value="{{ old('phone', $patient->phone) }}">
                        </div>
                    </div>
                </div>

                <hr class="border-dashed my-4 opacity-50">

                {{-- SECTION 2: INSURANCE (Optional) --}}
                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                    <i class="fa-solid fa-notes-medical me-2"></i>Insurance (Mutuelle)
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Provider</label>
                        <select name="mutuelle_provider" class="form-select">
                            <option value="">None</option>
                            <option value="CNSS" {{ $patient->mutuelle_provider == 'CNSS' ? 'selected' : '' }}>CNSS</option>
                            <option value="CNOPS" {{ $patient->mutuelle_provider == 'CNOPS' ? 'selected' : '' }}>CNOPS</option>
                            <option value="FAR" {{ $patient->mutuelle_provider == 'FAR' ? 'selected' : '' }}>FAR</option>
                            <option value="Private" {{ $patient->mutuelle_provider == 'Private' ? 'selected' : '' }}>Private Insurance</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Membership Number</label>
                        <input type="text" name="mutuelle_number" class="form-control" value="{{ old('mutuelle_number', $patient->mutuelle_number) }}">
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top bg-light px-4 py-3 rounded-bottom-4">
                <button type="button" class="btn btn-light fw-bold text-muted" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- AUTO-OPEN APPOINTMENT MODAL SCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Check URL for ?open_appt=XYZ
        const urlParams = new URLSearchParams(window.location.search);
        const openApptId = urlParams.get('open_appt');

        if (openApptId) {
            // 2. Find the modal trigger button or the modal itself
            // We look for the button that targets the specific modal ID
            const targetModalId = `#viewModal-${openApptId}`;
            const modalElement = document.querySelector(targetModalId);

            if (modalElement) {
                // Initialize and show the modal using Bootstrap 5 API
                const myModal = new bootstrap.Modal(modalElement);
                myModal.show();

                // 3. Optional: Highlight the row in the table for better visibility
                const row = modalElement.closest('tr') || document.querySelector(`tr[data-appt-id="${openApptId}"]`);
                if(row) {
                    row.classList.add('table-active'); 
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // 4. Clean the URL (remove ?open_appt=...) so refreshing doesn't keep opening it
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
    });
</script>


@endsection