@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')




    {{-- ALERT: Missed Appointments --}}
    @if(isset($missedCount) && $missedCount > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-warning fs-5"></i>
                <span><strong>{{ $missedCount }} unprocessed</strong> appointments from past days.</span>
            </div>
            <a href="{{ route('appointments.index', ['filter_mode' => 'history']) }}"
                class="btn btn-sm btn-warning text-white fw-bold shadow-sm">
                Review History
            </a>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fa-solid fa-calendar-check text-primary me-2"></i>Appointments
            </h4>
            <p class="text-muted small mb-0">Manage schedule, patient queue, and consultations.</p>
        </div>

        <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal"
            data-bs-target="#bookAppointmentModal">
            <i class="fa-solid fa-plus me-2"></i>Book Appointment
        </button>
    </div>

    {{-- SEARCH BAR --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 ps-3">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                </span>
                <input type="text" id="pageSearch" class="form-control border-0 bg-white"
                    placeholder="Search patient name, phone, or status..." onkeyup="filterAppointments()">
            </div>
        </div>
    </div>

    {{-- APPOINTMENTS TABLE --}}
    <div class="card pb-3 border-0 shadow-sm overflow-visible">

        {{-- Controls Header --}}
        <div class="card-header bg-white p-3 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="bg-light p-1 rounded-pill border d-inline-flex shadow-sm">
                    <a href="{{ route('appointments.index', ['filter_mode' => 'today_active', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode', 'today_active') == 'today_active' ? 'btn-white shadow-sm text-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-list-ol me-2"></i>Active Queue
                    </a>
                    <a href="{{ route('appointments.index', ['filter_mode' => 'all', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode') == 'all' ? 'btn-white shadow-sm text-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-layer-group me-2"></i>All
                    </a>
                    <a href="{{ route('appointments.index', ['filter_mode' => 'history', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode') == 'history' ? 'btn-white shadow-sm text-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>History
                    </a>
                </div>

                <form action="{{ route('appointments.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="filter_mode" value="{{ request('filter_mode', 'today_active') }}">
                    <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->subDay()->format('Y-m-d'), 'filter_mode' => request('filter_mode')]) }}"
                        class="btn btn-white btn-sm shadow-sm border rounded-circle"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-chevron-left text-muted" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="input-group input-group-sm border rounded-3 shadow-sm overflow-hidden"
                        style="width: 160px;">
                        <span class="input-group-text bg-white border-0 ps-2 pe-1 text-primary"><i
                                class="fa-regular fa-calendar"></i></span>
                        <input type="date" name="date"
                            class="form-control border-0 bg-white fw-bold text-center p-0 text-dark"
                            style="outline: none; box-shadow: none; font-size: 0.9rem;"
                            value="{{ request('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                    </div>
                    <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->addDay()->format('Y-m-d'), 'filter_mode' => request('filter_mode')]) }}"
                        class="btn btn-white btn-sm shadow-sm border rounded-circle"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                    </a>
                </form>
            </div>
        </div>

        <div class="table-responsive overflow-visible">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Time / Queue</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Patient</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Type</th>
                        <th class="text-end pe-4 py-3 text-muted small fw-bold text-uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="appointmentListBody">
                    @forelse($appointments as $index => $appt)
                        <tr class="appointment-row {{ $appt->status == 'in_consultation' ? 'bg-success bg-opacity-10' : '' }}"
                            data-patient-name="{{ strtolower($appt->patient->full_name) }}">

                            {{-- 1. Queue/Time --}}
                            <td class="ps-4">
                                @if(request('filter_mode', 'today_active') == 'today_active')
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle bg-white border shadow-sm fw-bold text-dark d-flex align-items-center justify-content-center rounded-circle"
                                            style="width: 35px; height: 35px;">
                                            {{ $index + 1 }}
                                        </div>
                                        @if($appt->status == 'in_consultation')
                                            <span class="badge bg-success animate-pulse">INSIDE</span>
                                        @elseif($index == 0)
                                            <span class="badge bg-primary">NEXT</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="fw-bold text-dark">{{ $appt->scheduled_at->format('H:i') }}</div>
                                    <small class="text-muted">{{ $appt->scheduled_at->format('d M') }}</small>
                                @endif
                            </td>

                            {{-- 2. Patient --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2 bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle"
                                        style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        {{ substr($appt->patient->first_name, 0, 1) }}{{ substr($appt->patient->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $appt->patient->full_name }}</div>
                                        <div class="small text-muted"><i class="fa-solid fa-phone me-1"
                                                style="font-size: 0.7rem;"></i>{{ $appt->patient->phone }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Status --}}
                            <td>
                                @php
                                    $statusColors = [
                                        'scheduled' => 'secondary',
                                        'waiting' => 'primary',
                                        'preparing' => 'warning',
                                        'in_consultation' => 'success',
                                        'pending_payment' => 'info',
                                        'finished' => 'dark',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $statusColors[$appt->status] ?? 'secondary';
                                @endphp
                                <span
                                    class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill px-3">
                                    {{ str_replace('_', ' ', ucfirst($appt->status)) }}
                                </span>
                            </td>

                            {{-- 4. Type --}}
                            <td>
                                @if($appt->type == 'urgency')
                                    <span class="badge bg-danger text-white shadow-sm">URGENCY</span>
                                @elseif($appt->type == 'control')
                                    <span class="badge bg-info text-white shadow-sm">Control</span>
                                @else
                                    <span class="text-muted small">Consultation</span>
                                @endif
                            </td>

                            {{-- 5. Actions --}}
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2 align-items-center">
                                    {{-- Workflow Buttons --}}
                                    @if($appt->status == 'scheduled')
                                        <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                            @csrf @method('PUT') <input type="hidden" name="status" value="waiting">
                                            <button
                                                class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">Arrived</button>
                                        </form>
                                    @endif

                                    @if($appt->status == 'waiting')
                                        <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                            @csrf @method('PUT') <input type="hidden" name="status" value="preparing">
                                            <button
                                                class="btn btn-sm btn-warning text-white fw-bold rounded-pill px-3">Prepare</button>
                                        </form>
                                    @endif

                                    @if($appt->status == 'preparing')
                                        <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                            @csrf @method('PUT') <input type="hidden" name="status" value="in_consultation">
                                            <button
                                                class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3">Start</button>
                                        </form>
                                    @endif

                                    {{-- 2. IN CONSULTATION --}}
                                    @if($appt->status == 'in_consultation')
                                        @if(auth()->user()->role === 'doctor')
                                            {{-- Doctor: Finish -> Pending Payment --}}
                                            <button class="btn btn-sm btn-primary fw-bold rounded-pill px-3 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#finishModal-{{ $appt->id }}">
                                                <i class="fa-solid fa-check me-1"></i> Finish
                                            </button>
                                        @else
                                            {{-- Secretary: Force Finish -> Finished directly --}}
                                            {{-- Useful if doctor forgets to click finish --}}
                                            <button class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#finishModal-{{ $appt->id }}"
                                                title="Close consultation and collect payment">
                                                <i class="fa-solid fa-check-double me-1"></i> Finish
                                            </button>
                                        @endif
                                    @endif


                                    {{-- BUTTON 2: PENDING PAYMENT (Only Secretary sees this) --}}
                                    @if($appt->status == 'pending_payment')
                                        @if(auth()->user()->role !== 'doctor')
                                            <button class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#finishModal-{{ $appt->id }}">
                                                <i class="fa-solid fa-cash-register me-1"></i> Collect Payment
                                            </button>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info">Sent to Secretary</span>
                                        @endif
                                    @endif

                                    {{-- Dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-white border shadow-sm text-muted rounded-2"
                                            data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                            <li><button class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#viewModal-{{ $appt->id }}"><i
                                                        class="fa-solid fa-eye text-primary me-2"></i> View Details</button>
                                            </li>
                                            @if(!in_array($appt->status, ['finished', 'cancelled']))
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('appointments.update_status', $appt->id) }}"
                                                        method="POST">
                                                        @csrf @method('PUT') <input type="hidden" name="status" value="cancelled">
                                                        <button class="dropdown-item text-danger"><i
                                                                class="fa-solid fa-xmark me-2"></i> Cancel</button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                {{-- =================================================== --}}
                                {{-- NEW FINISH MODAL WITH PRESCRIPTION INTEGRATION --}}
                                {{-- =================================================== --}}

                                @include('layouts.partials.finish_appointment') {{-- Use your existing modal or add it back here
                                --}}


                                {{-- View Modal --}}
                                @include('layouts.partials.appointment_details_modal') {{-- Use your existing modal or add it
                                back here --}}


                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Book Modal (Same as before) --}}

    @include('layouts.partials.book_modal') {{-- Use your existing modal or add it back here --}}

    {{-- SCRIPTS --}}
    <script>
        const patientSearchRoute = "{{ route('api.patients.search') }}";
    </script>
    <script src="{{ asset('js/appointments.js') }}"></script>
    <script src="{{ asset('js/finish_appointment_modal.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            @if(isset($appointments) && !$appointments->isEmpty())
                @foreach($appointments as $appt)
                    calculateTotal({{ $appt->id }});
                @endforeach
            @endif
            attachPaymentListeners();
        });
    </script>



    {{-- SPECIAL SECTION: Render the modal for the just-finished appointment --}}
    @if(isset($flashAppointment))
        {{-- Reuse your existing modal file for this specific appointment --}}
        @include('layouts.partials.appointment_details_modal', ['appt' => $flashAppointment])

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Open the modal immediately
                var myModal = new bootstrap.Modal(document.getElementById('viewModal-{{ $flashAppointment->id }}'));
                myModal.show();
            });
        </script>
    @endif


@endsection