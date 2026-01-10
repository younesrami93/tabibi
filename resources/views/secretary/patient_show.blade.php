@extends('layouts.admin')

@section('title', $patient->full_name)

@section('content')

    {{-- HEADER / BREADCRUMB --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}" class="text-muted text-decoration-none">Patients</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0">{{ $patient->full_name }}</h4>
        </div>
        <div class="d-flex gap-2">
           
        </div>
    </div>

    <div class="row g-4">
        
        {{-- LEFT COLUMN: PATIENT INFO CARD --}}
        <div class="col-lg-4">
            {{-- 1. Identity & Status --}}
            <div class="card  rounded-4 mb-4 overflow-hidden">
                <div class="card-body text-center p-4">
                    {{-- Avatar --}}
                    <div class="avatar-circle mx-auto mb-3 bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle" 
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                    </div>
                    
                    <h5 class="fw-bold mb-1">{{ $patient->full_name }}</h5>
                    <p class="text-muted small mb-3">
                        {{ $patient->age }} Years • {{ ucfirst($patient->gender) }}
                    </p>

                    {{-- Financial Status --}}

                    <div class="d-flex gap-2 w-100">
    
                        {{-- 1. Status Section (Grows to fill space) --}}
                        <div class="flex-grow-1">
                            @if($patient->current_balance > 0)
                                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-between p-2 rounded-3 mb-0 h-100">
                                    <div class="text-start">
                                        <small class="d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Outstanding Debt</small>
                                        <span class="fw-bold fs-5">{{ number_format($patient->current_balance, 2) }} <small>DH</small></span>
                                    </div>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger shadow-sm fw-bold px-2"
                                            onclick="openPaymentModal('{{ route('patients.payment', $patient->id) }}', {{ $patient->current_balance }})">
                                        Pay Now
                                    </button>
                                </div>
                            @else
                                <div class="bg-success bg-opacity-10 text-success rounded-3 fw-bold small h-100 d-flex align-items-center justify-content-center p-2">
                                    <i class="fa-solid fa-check-circle me-2"></i> Account Clear
                                </div>
                            @endif
                        </div>

                        {{-- 2. Edit Button (Auto width) --}}
                        <button class="btn btn-white border  fw-bold text-secondary px-4 d-flex align-items-center" 
                                onclick="openEditPatientModal('{{ route('patients.edit_modal', $patient->id) }}')"
                                title="Update Patient Profile">
                            <i class="fa-solid fa-pen me-2"></i> Edit
                        </button>

                    </div>
                   
                </div>
            </div>

            {{-- 2. Details Grid --}}
            <div class="card rounded-4 mb-4  overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold text-uppercase small text-muted"><i class="fa-regular fa-address-card me-2"></i>Personal Details</h6>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        {{-- Phone --}}
                        <li class="list-group-item px-0 border-bottom-0 d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Phone</span>
                            <span class="fw-bold text-dark">
                                @if($patient->phone)
                                    <a href="tel:{{ $patient->phone }}" class="text-decoration-none text-dark">{{ $patient->phone }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </span>
                        </li>
                        {{-- CIN --}}
                        <li class="list-group-item px-0 border-bottom-0 d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">CIN (ID)</span>
                            <span class="fw-bold text-dark">{{ $patient->cin ?: '-' }}</span>
                        </li>
                        {{-- DOB --}}
                        <li class="list-group-item px-0 border-bottom-0 d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Birth Date</span>
                            <span class="fw-bold text-dark">
                                {{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d M, Y') : '-' }}
                            </span>
                        </li>
                        {{-- Address --}}
                        <li class="list-group-item px-0 border-bottom-0">
                            <span class="d-block small text-muted mb-1">Address</span>
                            <span class="d-block fw-medium text-dark small lh-sm">{{ $patient->address ?: 'No address provided.' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- 3. Insurance Info (New) --}}
            @if($patient->mutuelle_provider || $patient->mutuelle_number)
            <div class="card rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold text-uppercase small text-muted"><i class="fa-solid fa-shield-heart me-2"></i>Insurance</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Provider</span>
                        <span class="fw-bold text-dark">{{ $patient->mutuelle_provider ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Number</span>
                        <span class="fw-bold text-dark">{{ $patient->mutuelle_number ?: '-' }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. Medical History --}}
            <div class="card rounded-4  overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold text-uppercase small text-muted"><i class="fa-solid fa-notes-medical me-2"></i>Medical History</h6>
                </div>
                <div class="card-body p-4">
                    @if($patient->medical_history)
                        <div class="p-3 bg-warning bg-opacity-10 text-dark rounded-3 small" style="white-space: pre-line;">{{ $patient->medical_history }}</div>
                    @else
                        <p class="text-muted small mb-0 fst-italic">No medical history recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: APPOINTMENTS ONLY --}}
        <div class="col-lg-8">
            <div class="card overflow-hidden rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-regular fa-calendar-check me-2"></i>Appointment History</h6>
                    <button class="btn btn-sm btn-primary fw-bold rounded-pill px-3" 
                            onclick="openBookModalForPatient({{ $patient->id }}, '{{ addslashes($patient->full_name) }}')">
                        <i class="fa-solid fa-plus me-1"></i> New Appointment
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4 py-3">Date</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Payment</th>
                                <th class="text-end pe-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->appointments()->orderBy('scheduled_at', 'desc')->get() as $appt)
                                <tr class="{{ $appt->status == 'cancelled' ? 'opacity-50' : '' }}">
                                    {{-- Date --}}
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $appt->scheduled_at->format('d M, Y') }}</div>
                                        <div class="small text-muted">{{ $appt->scheduled_at->format('H:i') }}</div>
                                    </td>
                                    
                                    {{-- Type --}}
                                    <td>
                                        @if($appt->type == 'urgency')
                                            <span class="badge bg-danger">Urgency</span>
                                        @elseif($appt->type == 'control')
                                            <span class="badge bg-info">Control</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Consultation</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @php
                                            $statusMap = [
                                                'scheduled' => ['secondary', 'Scheduled'],
                                                'waiting' => ['primary', 'Waiting'],
                                                'in_consultation' => ['warning', 'In Consult'],
                                                'finished' => ['success', 'Finished'],
                                                'cancelled' => ['danger', 'Cancelled'],
                                                'pending_payment' => ['info', 'Pending Pay'],
                                            ];
                                            $s = $statusMap[$appt->status] ?? ['secondary', $appt->status];
                                        @endphp
                                        <span class="badge bg-{{ $s[0] }} bg-opacity-10 text-{{ $s[0] }} border border-{{ $s[0] }} border-opacity-25 rounded-pill">
                                            {{ $s[1] }}
                                        </span>
                                    </td>

                                    {{-- Payment --}}
                                   <td>
                                    @if($appt->total_price > 0)
                                        <div class="small fw-bold">{{ number_format($appt->total_price, 2) }} DH</div>
                                        
                                        @if($appt->due_amount > 0)
                                            @if($appt->status === 'pending_payment')
                                                {{-- Case A: Patient is at the desk / On the way (Not a debt yet) --}}
                                                <span class="text-warning small fw-bold" style="font-size: 0.7rem;">
                                                    <i class="fa-solid fa-hourglass-half me-1"></i> To Collect
                                                </span>
                                            @else
                                                {{-- Case B: Appointment finished/closed but money is still owed (Real Debt) --}}
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">
                                                    Unpaid: {{ number_format($appt->due_amount, 2) }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-success small fw-bold">
                                                <i class="fa-solid fa-check-double me-1"></i> Paid
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                    {{-- Actions --}}
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light border rounded-circle shadow-sm text-secondary" 
                                                onclick="openFullModal('{{ route('appointments.modal', $appt->id) }}')" 
                                                title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-regular fa-calendar mb-2 fa-2x opacity-25"></i>
                                        <p class="mb-0 small">No appointments history.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    @include('layouts.partials.payment_modal')

    {{-- SCRIPT: Container for Dynamic Edit Modal --}}
    <div id="dynamic-modal-container"></div>

    <script>
        // Open the Edit Modal dynamically
        function openEditPatientModal(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const container = document.getElementById('dynamic-modal-container');
                    container.innerHTML = html;
                    const modal = new bootstrap.Modal(container.querySelector('.modal'));
                    modal.show();
                })
                .catch(err => console.error('Error:', err));
        }

        // Helper to open Book Modal with Patient Pre-selected
        // Note: This assumes you have the book_modal JS logic available globally or in main layout
        function openBookModalForPatient(id, name) {
            // Trigger the main book modal
            const modalEl = document.getElementById('bookAppointmentModal');
            if(modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                
                // Pre-fill logic (If your book_modal.js supports it)
                // If not, we can simple set the hidden input manually:
                setTimeout(() => {
                    const idInput = document.getElementById('patientIdInput');
                    const searchInput = document.getElementById('patientSearchInput');
                    const display = document.getElementById('selectedPatientDisplay');
                    const displayName = document.getElementById('selectedPatientName');
                    
                    if(idInput && display && displayName) {
                        idInput.value = id;
                        displayName.innerText = name;
                        display.classList.remove('d-none');
                        document.getElementById('patientSearchGroup').classList.add('d-none'); // Hide search input
                    }
                }, 200);
            }
        }
    </script>
@endsection