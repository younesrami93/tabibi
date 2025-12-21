@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')


    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 text-secondary">
                Appointments
            </h4>
            <p class="text-muted small mb-0">Manage schedule, patient queue, and consultations.</p>
        </div>

        <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal"
            data-bs-target="#bookAppointmentModal">
            <i class="fa-solid fa-plus me-2"></i>Book Appointment
        </button>
    </div>

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


    {{-- 1. SEARCH BAR (Separated Card) --}}
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

    {{-- 2. TABLE CARD (Tabs & Date Attached) --}}
    <div class="card border-0 shadow-sm overflow-hidden">

        {{-- Card Header: Controls --}}
        <div class="card-header bg-white p-3 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                {{-- LEFT: Filter Tabs (Pills) --}}
                <div class="bg-light p-1 rounded-pill border d-inline-flex">
                    <a href="{{ route('appointments.index', ['filter_mode' => 'today_active', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode', 'today_active') == 'today_active' ? 'btn-primary shadow-sm text-white bg-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-list-ol me-2"></i>Active Queue
                    </a>
                    <a href="{{ route('appointments.index', ['filter_mode' => 'all', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode') == 'all' ? 'btn-white shadow-sm text-white  bg-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-layer-group me-2"></i>All
                    </a>
                    <a href="{{ route('appointments.index', ['filter_mode' => 'history', 'date' => request('date')]) }}"
                        class="btn btn-sm rounded-pill px-4 fw-bold transition-all {{ request('filter_mode') == 'history' ? 'btn-white shadow-sm text-white  bg-primary' : 'text-muted border-0 bg-transparent' }}">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>History
                    </a>
                </div>

                {{-- RIGHT: Date Picker --}}
                <form action="{{ route('appointments.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="filter_mode" value="{{ request('filter_mode', 'today_active') }}">

                    {{-- Prev Day --}}
                    <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->subDay()->format('Y-m-d'), 'filter_mode' => request('filter_mode')]) }}"
                        class="btn btn-white btn-sm shadow-sm border rounded-circle"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                        title="Previous Day">
                        <i class="fa-solid fa-chevron-left text-muted" style="font-size: 0.8rem;"></i>
                    </a>

                    {{-- Date Display/Input --}}
                    <div class="input-group input-group-sm border rounded-3 shadow-sm overflow-hidden"
                        style="width: 160px;">
                        <span class="input-group-text bg-white border-0 ps-2 pe-1 text-primary">
                            <i class="fa-regular fa-calendar"></i>
                        </span>
                        <input type="date" name="date"
                            class="form-control border-0 bg-white fw-bold text-center p-0 text-dark"
                            style="outline: none; box-shadow: none; font-size: 0.9rem;"
                            value="{{ request('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                    </div>

                    {{-- Next Day --}}
                    <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->addDay()->format('Y-m-d'), 'filter_mode' => request('filter_mode')]) }}"
                        class="btn btn-white btn-sm shadow-sm border rounded-circle"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                        title="Next Day">
                        <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                    </a>
                </form>

            </div>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
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
                                @if($appt->status == 'scheduled')
                                    <span
                                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3">Scheduled</span>
                                @elseif($appt->status == 'waiting')
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">Waiting
                                        Room</span>
                                @elseif($appt->status == 'preparing')
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">Preparing</span>
                                @elseif($appt->status == 'in_consultation')
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">In
                                        Consultation</span>
                                @elseif($appt->status == 'finished')
                                    <span
                                        class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill px-3">Finished</span>
                                @elseif($appt->status == 'cancelled')
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">Cancelled</span>
                                @endif
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

                                    @if($appt->status == 'in_consultation')
                                        <button class="btn btn-sm btn-primary fw-bold rounded-pill px-3 shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#finishModal-{{ $appt->id }}">
                                            <i class="fa-solid fa-check-double me-1"></i> Finish
                                        </button>
                                    @endif

                                    {{-- More Actions --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-white border shadow-sm text-muted rounded-2"
                                            data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                            <li>
                                                <button class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#viewModal-{{ $appt->id }}">
                                                    <i class="fa-solid fa-eye text-primary me-2"></i> View Details
                                                </button>
                                            </li>
                                            @if(!in_array($appt->status, ['finished', 'cancelled']))
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('appointments.update_status', $appt->id) }}"
                                                        method="POST">
                                                        @csrf @method('PUT') <input type="hidden" name="status" value="cancelled">
                                                        <button class="dropdown-item text-danger">
                                                            <i class="fa-solid fa-xmark me-2"></i> Cancel Appointment
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                </div>

                                {{-- MODAL: FINISH --}}
                                <div class="modal fade text-start" id="finishModal-{{ $appt->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <form action="{{ route('appointments.finish', $appt->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title fw-bold"><i
                                                            class="fa-solid fa-file-invoice-dollar me-2"></i>Finalize
                                                        Consultation</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body bg-light">
                                                    <div class="row g-4">
                                                        <div class="col-lg-8">
                                                            <div class="card border-0 shadow-sm h-100">
                                                                <div class="card-header bg-white fw-bold">Medical Services</div>
                                                                <div class="card-body">
                                                                    <table class="table table-sm align-middle mb-3">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>Service</th>
                                                                                <th width="120">Price</th>
                                                                                <th width="30"></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="serviceRows-{{ $appt->id }}">
                                                                            @foreach($appt->services as $ix => $s)
                                                                                <tr class="service-row">
                                                                                    <td>{{ $s->name }}<input type="hidden"
                                                                                            name="services[{{ $ix }}][id]"
                                                                                            value="{{ $s->id }}"></td>
                                                                                    <td><input type="number" step="0.01"
                                                                                            class="form-control form-control-sm text-end price-input"
                                                                                            name="services[{{ $ix }}][price]"
                                                                                            value="{{ $s->pivot->price }}"
                                                                                            oninput="calculateTotal({{ $appt->id }})">
                                                                                    </td>
                                                                                    <td><button type="button"
                                                                                            class="btn btn-link text-danger p-0"
                                                                                            onclick="removeRow(this, {{ $appt->id }})"><i
                                                                                                class="fa-solid fa-trash"></i></button>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                    <div
                                                                        class="d-flex gap-2 p-2 bg-light rounded border border-dashed">
                                                                        <select class="form-select form-select-sm"
                                                                            id="newServiceSelect-{{ $appt->id }}">
                                                                            <option value="">+ Add Service...</option>
                                                                            @foreach($allServices as $srv)
                                                                                <option value="{{ $srv->id }}"
                                                                                    data-name="{{ $srv->name }}"
                                                                                    data-price="{{ $srv->price }}">{{ $srv->name }}
                                                                                    ({{ number_format($srv->price, 2) }} MAD)
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <button type="button" class="btn btn-sm btn-primary"
                                                                            onclick="addServiceRow({{ $appt->id }})">Add</button>
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <label class="form-label small fw-bold">Notes /
                                                                            Diagnosis</label>
                                                                        <textarea name="notes" class="form-control"
                                                                            rows="3">{{ $appt->notes }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="card border-0 shadow-sm h-100">
                                                                <div class="card-header bg-white fw-bold">Payment</div>
                                                                <div class="card-body">
                                                                    <div class="mb-3">
                                                                        <label class="small text-muted">Consultation Fee</label>
                                                                        <div class="input-group"><input type="number"
                                                                                step="0.01" class="form-control fw-bold"
                                                                                id="basePrice-{{ $appt->id }}" name="price"
                                                                                value="{{ number_format($appt->price, 2, '.', '') }}"
                                                                                oninput="calculateTotal({{ $appt->id }})"><span
                                                                                class="input-group-text">MAD</span></div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between mb-3"><span
                                                                            class="text-muted">Services:</span><span
                                                                            class="fw-bold"><span
                                                                                id="servicesSum-{{ $appt->id }}">0.00</span>
                                                                            MAD</span></div>
                                                                    <div
                                                                        class="bg-primary bg-opacity-10 p-3 rounded text-center mb-3">
                                                                        <small class="text-primary fw-bold">TOTAL TO PAY</small>
                                                                        <h2 class="mb-0 text-primary fw-bold"><span
                                                                                id="totalDisplay-{{ $appt->id }}">0.00</span>
                                                                            <small class="fs-6">MAD</small></h2>
                                                                    </div>
                                                                    <div>
                                                                        <label class="small text-muted">Amount Received</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control border-success text-success fw-bold text-end"
                                                                            name="paid_amount" id="paidAmount-{{ $appt->id }}">
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer bg-white border-top-0 pb-3">
                                                                    <button type="submit"
                                                                        class="btn btn-success w-100 fw-bold shadow-sm">Confirm
                                                                        & Finish</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- MODAL: VIEW DETAILS --}}
                                <div class="modal fade text-start" id="viewModal-{{ $appt->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Appointment Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar-circle me-3 bg-light text-dark fw-bold rounded-circle d-flex justify-content-center align-items-center"
                                                        style="width: 50px; height: 50px;">
                                                        {{ substr($appt->patient->first_name, 0, 1) }}{{ substr($appt->patient->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0 fw-bold">{{ $appt->patient->full_name }}</h5>
                                                        <p class="text-muted small mb-0">{{ $appt->patient->phone }}</p>
                                                    </div>
                                                </div>
                                                <div class="bg-light p-3 rounded mb-3">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Status:</span>
                                                        <span
                                                            class="fw-bold text-capitalize">{{ str_replace('_', ' ', $appt->status) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted">Type:</span>
                                                        <span class="fw-bold text-capitalize">{{ $appt->type }}</span>
                                                    </div>
                                                </div>
                                                <h6 class="text-primary small fw-bold text-uppercase border-bottom pb-2">History
                                                </h6>
                                                <ul class="list-unstyled small text-muted">
                                                    @foreach($appt->history as $h)
                                                        <li class="mb-1"><i
                                                                class="fa-solid fa-clock me-2 text-primary opacity-50"></i>
                                                            {{ $h->status }} - {{ $h->created_at->format('H:i') }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-50">
                                    <i class="fa-regular fa-calendar-xmark fa-3x mb-3"></i>
                                    <p class="mb-0">No appointments found for this selection.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL C: BOOK NEW APPOINTMENT --}}
    <div class="modal fade" id="bookAppointmentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">Book Appointment</h5>
                            <p class="text-muted small mb-0">Schedule a new visit or control.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">

                        {{-- Patient Selection --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Patient</label>

                            {{-- Search Input --}}
                            <div class="input-group" id="patientSearchGroup">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="fa-solid fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="patientSearchInput"
                                    placeholder="Type name to search..." autocomplete="off">
                                <button type="button" class="btn btn-light border" id="btnShowNewPatient"
                                    onclick="toggleNewPatientMode()">
                                    <i class="fa-solid fa-plus text-primary"></i> New
                                </button>
                            </div>

                            {{-- Dropdown Results --}}
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg mt-1"
                                style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></div>

                            {{-- Selected State --}}
                            <div id="selectedPatientDisplay"
                                class="alert alert-primary d-flex justify-content-between align-items-center mt-2 mb-0 d-none">
                                <div>
                                    <i class="fa-solid fa-user-check me-2"></i>
                                    <span id="selectedPatientName" class="fw-bold"></span>
                                </div>
                                <button type="button" class="btn-close btn-sm" onclick="resetPatientSelection()"></button>
                            </div>
                            <input type="hidden" name="patient_id" id="patientIdInput">
                        </div>

                        {{-- New Patient Form --}}
                        <div id="newPatientForm" class="p-3 bg-light rounded border mb-4 d-none position-relative">
                            <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 m-2"
                                onclick="toggleNewPatientMode()"></button>
                            <h6 class="text-primary fw-bold small text-uppercase mb-3">New Patient Details</h6>
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="new_first_name"
                                        class="form-control form-control-sm" placeholder="First Name"></div>
                                <div class="col-6"><input type="text" name="new_last_name"
                                        class="form-control form-control-sm" placeholder="Last Name"></div>
                                <div class="col-12"><input type="text" name="new_phone" class="form-control form-control-sm"
                                        placeholder="Phone Number"></div>
                                <div class="col-12">
                                    <select name="new_gender" class="form-select form-select-sm">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Date & Time</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control"
                                    value="{{ request('date', now()->format('Y-m-d')) }}T{{ now()->format('H:00') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Visit Type</label>
                                <select name="type" class="form-select">
                                    <option value="consultation">Consultation</option>
                                    <option value="control">Control (Follow-up)</option>
                                    <option value="urgency">Urgency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Note (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Reason for visit..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Confirm Booking</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Variables & Scripts --}}
    <script>
        const patientSearchRoute = "{{ route('api.patients.search') }}";
    </script>
    <script src="{{ asset('js/appointments.js') }}"></script>
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

@endsection