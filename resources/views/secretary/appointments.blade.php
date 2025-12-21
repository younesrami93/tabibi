@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')

    {{-- ALERT: Missed Appointments --}}
    @if(isset($missedCount) && $missedCount > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm mb-4">
            <div>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>{{ $missedCount }} unprocessed</strong> appointments from past days.
            </div>
            <a href="{{ route('appointments.index', ['filter_mode' => 'history']) }}" class="btn btn-sm btn-warning fw-bold">
                Review History
            </a>
        </div>
    @endif

    {{-- TOOLBAR: Date, Search & Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">

                {{-- 1. Date Navigation --}}
                <div class="col-md-3 d-flex align-items-center gap-2">
                    @if(request('filter_mode') != 'history')
                        <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->subDay()->format('Y-m-d')]) }}"
                            class="btn btn-light btn-sm">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <span class="fw-bold fs-5">
                            {{ \Carbon\Carbon::parse(request('date', now()))->format('D, d M') }}
                        </span>
                        <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->addDay()->format('Y-m-d')]) }}"
                            class="btn btn-light btn-sm">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="badge bg-secondary">Viewing History</span>
                    @endif
                </div>

                {{-- 2. Client-Side Search Input --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" id="pageSearch" class="form-control border-0 bg-light"
                            placeholder="Find patient in list..." onkeyup="filterAppointments()">
                    </div>
                </div>

                {{-- 3. Filters & New Button --}}
                <div class="col-md-5 d-flex justify-content-end gap-2">
                    <div class="btn-group" role="group">
                        <a href="{{ route('appointments.index', ['filter_mode' => 'today_active']) }}"
                            class="btn btn-sm {{ request('filter_mode', 'today_active') == 'today_active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Active
                        </a>
                        <a href="{{ route('appointments.index', ['filter_mode' => 'all']) }}"
                            class="btn btn-sm {{ request('filter_mode') == 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All
                        </a>
                        <a href="{{ route('appointments.index', ['filter_mode' => 'history']) }}"
                            class="btn btn-sm {{ request('filter_mode') == 'history' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            History
                        </a>
                    </div>

                    {{-- THE NEW APPOINTMENT BUTTON --}}
                    <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                        <i class="fa-solid fa-plus"></i> New
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- APPOINTMENTS LIST --}}
    <div class="row g-3" id="appointmentList">
        @if($appointments->isEmpty())
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-regular fa-clipboard fa-3x mb-3 opacity-25"></i>
                <h5>No appointments found.</h5>
            </div>
        @else
            @foreach($appointments as $index => $appt)
                <div class="col-12 appointment-card" data-patient-name="{{ strtolower($appt->patient->full_name) }}">
                    <div
                        class="card border-0 shadow-sm border-start border-4 
                                                                                                                                                                                                                                                {{ $appt->status == 'in_consultation' ? 'border-success bg-success bg-opacity-10' : '' }}
                                                                                                                                                                                                                                                {{ $appt->status == 'preparing' ? 'border-warning' : '' }}
                                                                                                                                                                                                                                                {{ $appt->type == 'urgency' ? 'border-danger' : 'border-primary' }}">

                        <div class="card-body py-2">
                            <div class="row align-items-center">

                                {{-- COL 1: QUEUE POSITION --}}
                                <div class="col-md-1 text-center border-end">
                                    @if(request('filter_mode', 'today_active') == 'today_active')
                                        {{-- Visual Rank --}}
                                        <div class="fs-3 fw-bold {{ $index == 0 ? 'text-success' : 'text-primary' }}">
                                            #{{ $index + 1 }}
                                        </div>
                                        <small class="text-muted d-block fw-bold" style="font-size: 10px;">
                                            @if($appt->status == 'in_consultation')
                                                <span class="text-success animate-pulse">INSIDE</span>
                                            @elseif($index == 0)
                                                NEXT
                                            @else
                                                Queue
                                            @endif
                                        </small>
                                    @else
                                        {{-- History Mode shows Time --}}
                                        <h5 class="fw-bold mb-0 text-muted">{{ $appt->scheduled_at->format('H:i') }}</h5>
                                    @endif
                                </div>

                                {{-- COL 2: PATIENT INFO --}}
                                <div class="col-md-4 ps-3">
                                    <div class="fw-bold fs-5">
                                        {{ $appt->patient->full_name }}
                                        @if($appt->type == 'urgency') <span class="badge bg-danger">URGENT</span> @endif
                                        @if($appt->status == 'finished') <span class="badge bg-secondary">Done</span> @endif
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fa-regular fa-clock me-1"></i> {{ $appt->scheduled_at->format('H:i') }}
                                        <span class="mx-1">|</span>
                                        <i class="fa-solid fa-phone me-1"></i> {{ $appt->patient->phone }}
                                    </div>
                                </div>

                                {{-- COL 3: ACTIONS --}}
                                <div class="col-md-7 text-end">
                                    <div class="d-flex justify-content-end gap-2 align-items-center">

                                        {{-- View Details --}}
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                            data-bs-target="#viewModal-{{ $appt->id }}" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <div class="vr mx-2"></div>

                                        {{-- Workflow Buttons --}}
                                        @if($appt->status == 'scheduled')
                                            <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                                @csrf @method('PUT') <input type="hidden" name="status" value="waiting">
                                                <button class="btn btn-sm btn-outline-primary fw-bold">Arrived</button>
                                            </form>
                                        @endif

                                        @if($appt->status == 'waiting')
                                            <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                                @csrf @method('PUT') <input type="hidden" name="status" value="preparing">
                                                <button class="btn btn-sm btn-warning">Prepare</button>
                                            </form>
                                        @endif

                                        @if($appt->status == 'preparing')
                                            <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                                @csrf @method('PUT') <input type="hidden" name="status" value="in_consultation">
                                                <button class="btn btn-sm btn-success">Start Consult</button>
                                            </form>
                                        @endif

                                        @if($appt->status == 'in_consultation')
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#finishModal-{{ $appt->id }}">
                                                <i class="fa-solid fa-check-double"></i> Complete
                                            </button>
                                        @endif

                                        @if(!in_array($appt->status, ['finished', 'cancelled']))
                                            <button class="btn btn-sm btn-light text-danger" title="Cancel"><i
                                                    class="fa-solid fa-xmark"></i></button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- MODAL A: FINISH APPOINTMENT (Inside Loop) --}}
                {{-- ========================================== --}}


                <div class="modal fade" id="finishModal-{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <form action="{{ route('appointments.finish', $appt->id) }}" method="POST">
                            @csrf @method('PUT')

                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-success text-white py-3">
                                    <div class="d-flex flex-column">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Finalize & Invoice
                                        </h5>
                                        <small class="opacity-75">Patient: {{ $appt->patient->full_name }}</small>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body bg-light">
                                    <div class="row g-4">

                                        <div class="col-lg-8">
                                            <div class="card shadow-sm border-0 h-100">
                                                <div class="card-header bg-white py-3 border-bottom">
                                                    <h6 class="mb-0 fw-bold text-primary"><i
                                                            class="fa-solid fa-stethoscope me-2"></i> Medical Services</h6>
                                                </div>
                                                <div class="card-body">

                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr class="text-uppercase small text-muted">
                                                                    <th>Description</th>
                                                                    <th style="width: 140px;">Price (MAD)</th>
                                                                    <th style="width: 50px;"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="serviceRows-{{ $appt->id }}">
                                                                @foreach($appt->services as $ix => $s)
                                                                    <tr class="service-row">
                                                                        <td>
                                                                            <span class="fw-medium text-dark">{{ $s->name }}</span>
                                                                            <input type="hidden" name="services[{{ $ix }}][id]"
                                                                                value="{{ $s->id }}">
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step="0.01"
                                                                                class="form-control form-control-sm text-end price-input"
                                                                                name="services[{{ $ix }}][price]"
                                                                                value="{{ $s->pivot->price }}"
                                                                                oninput="calculateTotal({{ $appt->id }})">
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-link text-danger p-0"
                                                                                onclick="removeRow(this, {{ $appt->id }})">
                                                                                <i class="fa-solid fa-trash-can"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="bg-light p-2 rounded border border-dashed mb-4">
                                                        <div class="d-flex gap-2">
                                                            <select class="form-select form-select-sm"
                                                                id="newServiceSelect-{{ $appt->id }}">
                                                                <option value="">+ Add Medical Service...</option>
                                                                @foreach($allServices as $srv)
                                                                    <option value="{{ $srv->id }}" data-name="{{ $srv->name }}"
                                                                        data-price="{{ $srv->price }}">
                                                                        {{ $srv->name }} ({{ number_format($srv->price, 2) }} MAD)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-primary px-3 fw-bold"
                                                                onclick="addServiceRow({{ $appt->id }})">
                                                                ADD
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mt-auto">
                                                        <label
                                                            class="form-label small fw-bold text-muted text-uppercase">Consultation
                                                            Notes / Diagnosis</label>
                                                        <textarea name="notes" class="form-control" rows="3"
                                                            placeholder="Write internal notes here...">{{ $appt->notes }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="card shadow-sm border-0 h-100">
                                                <div class="card-header bg-white py-3 border-bottom">
                                                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-calculator me-2"></i>
                                                        Payment Details</h6>
                                                </div>
                                                <div class="card-body d-flex flex-column">

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">Consultation Fee</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white border-end-0"><i
                                                                    class="fa-solid fa-user-doctor text-muted"></i></span>
                                                            <input type="number" step="0.01"
                                                                class="form-control border-start-0 fw-bold fs-5 text-end text-dark"
                                                                id="basePrice-{{ $appt->id }}" name="price"
                                                                value="{{ number_format($appt->price, 2, '.', '') }}"
                                                                oninput="calculateTotal({{ $appt->id }})">
                                                            <span class="input-group-text bg-white">MAD</span>
                                                        </div>
                                                    </div>

                                                    <hr class="my-3 text-muted opacity-25">

                                                    <div class="d-flex justify-content-between mb-2 small text-muted">
                                                        <span>Services Total:</span>
                                                        <span class="fw-bold"><span id="servicesSum-{{ $appt->id }}">0.00</span>
                                                            MAD</span>
                                                    </div>

                                                    <div class="bg-primary bg-opacity-10 p-3 rounded mb-4 text-center">
                                                        <small class="text-primary fw-bold text-uppercase d-block mb-1">Total to
                                                            Pay</small>
                                                        <h2 class="mb-0 fw-bold text-primary display-6">
                                                            <span id="totalDisplay-{{ $appt->id }}">0.00</span> <small
                                                                class="fs-6 text-dark">MAD</small>
                                                        </h2>
                                                    </div>

                                                    <div class="mt-auto">
                                                        <label class="form-label small fw-bold text-success">
                                                            <i class="fa-solid fa-money-bill-wave me-1"></i> Amount Received
                                                        </label>
                                                        <div class="input-group input-group-lg">
                                                            <span class="input-group-text bg-success text-white border-success"><i
                                                                    class="fa-solid fa-hand-holding-dollar"></i></span>
                                                            <input type="number" step="0.01"
                                                                class="form-control border-success text-success fw-bold text-end"
                                                                name="paid_amount" id="paidAmount-{{ $appt->id }}"
                                                                placeholder="0.00">
                                                        </div>
                                                        <div class="form-text text-end small">Auto-filled. Edit for partial payment.
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="card-footer bg-white border-top-0 pb-3 pt-0">
                                                    <button type="submit"
                                                        class="btn btn-success w-100 py-2 fw-bold text-uppercase shadow-sm">
                                                        <i class="fa-solid fa-check-circle me-2"></i> Confirm & Finish
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- ========================================== --}}
                {{-- MODAL B: VIEW DETAILS (Inside Loop) --}}
                {{-- ========================================== --}}
                <div class="modal fade" id="viewModal-{{ $appt->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Details: #{{ $index + 1 }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6>{{ $appt->patient->full_name }}</h6>
                                <p>Scheduled: {{ $appt->scheduled_at->format('H:i') }}</p>
                                <p>Status: {{ $appt->status }}</p>
                                <hr>
                                <small class="text-muted">History:</small>
                                <ul>
                                    @foreach($appt->history as $h)
                                        <li>{{ $h->status }} at {{ $h->created_at->format('H:i') }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- MODAL C: BOOK NEW APPOINTMENT (Global) --}}
    {{-- ========================================== --}}
    <div class="modal fade" id="bookAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Book Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Patient Selection --}}
                        <div class="mb-3 position-relative">
                            <label class="form-label">Patient Name</label>
                            <div class="input-group" id="patientSearchGroup">
                                <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                                <input type="text" class="form-control" id="patientSearchInput"
                                    placeholder="Type name to search..." autocomplete="off">
                                <button type="button" class="btn btn-outline-primary" id="btnShowNewPatient"
                                    onclick="toggleNewPatientMode()">
                                    <i class="fa-solid fa-plus"></i> New
                                </button>
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow"
                                style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                            <div id="selectedPatientDisplay"
                                class="alert alert-primary d-flex justify-content-between align-items-center mt-2 d-none">
                                <span id="selectedPatientName" class="fw-bold"></span>
                                <button type="button" class="btn-close btn-sm" onclick="resetPatientSelection()"></button>
                            </div>
                            <input type="hidden" name="patient_id" id="patientIdInput">
                        </div>

                        {{-- New Patient Fields --}}
                        <div id="newPatientForm" class="p-3 bg-light rounded border mb-3 d-none">
                            <h6 class="text-primary border-bottom pb-2">New Patient Details</h6>
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="new_first_name"
                                        class="form-control form-control-sm" placeholder="First Name"></div>
                                <div class="col-6"><input type="text" name="new_last_name"
                                        class="form-control form-control-sm" placeholder="Last Name"></div>
                                <div class="col-12"><input type="text" name="new_phone" class="form-control form-control-sm"
                                        placeholder="Phone"></div>
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
                                <label class="form-label">Date & Time</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control"
                                    value="{{ request('date', now()->format('Y-m-d')) }}T{{ now()->format('H:00') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="consultation">Consultation</option>
                                    <option value="control">Control (Follow-up)</option>
                                    <option value="urgency">Urgency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Confirm Booking</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script>
        // ==========================================
        // 1. QUEUE FILTER (Client-Side)
        // ==========================================
        function filterAppointments() {
            const input = document.getElementById('pageSearch').value.toLowerCase();
            const cards = document.getElementsByClassName('appointment-card');
            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-patient-name');
                if (name.includes(input)) {
                    cards[i].classList.remove('d-none');
                } else {
                    cards[i].classList.add('d-none');
                }
            }
        }

        // ==========================================
        // 2. FINISH MODAL & SMART PAYMENTS
        // ==========================================

        // Track if the user has manually edited the "Paid Amount" field
        // If true, we stop auto-updating it when the total changes.
        const manualPaymentEdits = {};

        function addServiceRow(apptId) {
            const select = document.getElementById(`newServiceSelect-${apptId}`);
            const tbody = document.getElementById(`serviceRows-${apptId}`);
            const selectedOption = select.options[select.selectedIndex];

            if (!select.value) return;

            const id = select.value;
            const name = selectedOption.getAttribute('data-name');
            const price = selectedOption.getAttribute('data-price');
            const index = Date.now();

            const row = `
                <tr class="service-row">
                    <td>
                        <span class="fw-medium text-dark">${name}</span>
                        <input type="hidden" name="services[${index}][id]" value="${id}">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm text-end price-input" 
                            name="services[${index}][price]" value="${price}" 
                            oninput="calculateTotal(${apptId})">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this, ${apptId})">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;

            tbody.insertAdjacentHTML('beforeend', row);
            select.value = "";
            calculateTotal(apptId);
        }

        function removeRow(btn, apptId) {
            btn.closest('tr').remove();
            calculateTotal(apptId);
        }

        function calculateTotal(apptId) {
            // 1. Get Base Price
            const baseInput = document.getElementById(`basePrice-${apptId}`);
            const basePrice = parseFloat(baseInput.value) || 0;

            // 2. Sum Services
            const serviceInputs = document.querySelectorAll(`#serviceRows-${apptId} .price-input`);
            let servicesTotal = 0;
            serviceInputs.forEach(input => {
                servicesTotal += parseFloat(input.value) || 0;
            });

            // 3. Update Displays
            const servicesSumDisplay = document.getElementById(`servicesSum-${apptId}`);
            if (servicesSumDisplay) servicesSumDisplay.innerText = servicesTotal.toFixed(2);

            const grandTotal = basePrice + servicesTotal;
            const totalDisplay = document.getElementById(`totalDisplay-${apptId}`);
            if (totalDisplay) totalDisplay.innerText = grandTotal.toFixed(2);

            // 4. Auto-Fill Paid Amount (Smart Logic)
            // Only update if the user hasn't touched the field manually
            if (!manualPaymentEdits[apptId]) {
                const paidInput = document.getElementById(`paidAmount-${apptId}`);
                if (paidInput) paidInput.value = grandTotal.toFixed(2);
            }
        }

        // Attach listeners to detect manual edits
        function attachPaymentListeners() {
            const paidInputs = document.querySelectorAll('[id^="paidAmount-"]');
            paidInputs.forEach(input => {
                input.addEventListener('input', function () {
                    // Extract ID from "paidAmount-123"
                    const id = this.id.replace('paidAmount-', '');
                    manualPaymentEdits[id] = true; // Mark this appointment as manually edited
                });
            });
        }

        // ==========================================
        // 3. NEW APPOINTMENT (Patient Search)
        // ==========================================
        const searchInput = document.getElementById('patientSearchInput');
        const resultsBox = document.getElementById('searchResults');
        const patientIdInput = document.getElementById('patientIdInput');
        const newPatientForm = document.getElementById('newPatientForm');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value;
                if (query.length < 2) { resultsBox.style.display = 'none'; return; }

                fetch(`{{ route('api.patients.search') }}?q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (data.length > 0) {
                            resultsBox.style.display = 'block';
                            data.forEach(p => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action text-start';
                                item.innerHTML = `<strong>${p.first_name} ${p.last_name}</strong> <small class='text-muted'>${p.phone || ''}</small>`;
                                item.onclick = () => selectPatient(p);
                                resultsBox.appendChild(item);
                            });
                        } else {
                            resultsBox.style.display = 'none';
                        }
                    });
            });
        }

        function selectPatient(patient) {
            patientIdInput.value = patient.id;
            document.getElementById('selectedPatientName').innerText = `${patient.first_name} ${patient.last_name}`;
            document.getElementById('selectedPatientDisplay').classList.remove('d-none');
            document.getElementById('patientSearchGroup').classList.add('d-none');
            resultsBox.style.display = 'none';
            newPatientForm.classList.add('d-none');
        }

        function resetPatientSelection() {
            patientIdInput.value = '';
            document.getElementById('selectedPatientDisplay').classList.add('d-none');
            document.getElementById('patientSearchGroup').classList.remove('d-none');
            searchInput.value = '';
            searchInput.focus();
        }

        function toggleNewPatientMode() {
            const isHidden = newPatientForm.classList.contains('d-none');
            if (isHidden) {
                newPatientForm.classList.remove('d-none');
                patientIdInput.value = '';
                document.getElementById('patientSearchGroup').classList.add('d-none');
                document.getElementById('btnShowNewPatient').innerText = 'Cancel New';
            } else {
                newPatientForm.classList.add('d-none');
                document.getElementById('patientSearchGroup').classList.remove('d-none');
                document.getElementById('btnShowNewPatient').innerHTML = '<i class="fa-solid fa-plus"></i> New';
            }
        }

        // ==========================================
        // INITIALIZATION
        // ==========================================
        document.addEventListener("DOMContentLoaded", function () {
            // Calculate totals for all loaded modals
            @if(isset($appointments) && !$appointments->isEmpty())
                @foreach($appointments as $appt)
                    calculateTotal({{ $appt->id }});
                @endforeach
            @endif

            // Start listening for manual payment edits
            attachPaymentListeners();
        });
    </script>

@endsection