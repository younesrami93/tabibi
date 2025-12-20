@extends('layouts.admin')

@section('title', 'Daily Agenda')
@section('header', 'Appointments')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->subDay()->format('Y-m-d')]) }}"
                class="btn btn-light btn-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h4 class="mb-0 fw-bold text-primary">
                {{ \Carbon\Carbon::parse(request('date', now()))->format('l, d M Y') }}
                @if(\Carbon\Carbon::parse(request('date', now()))->isToday())
                    <span class="badge bg-success ms-2 fs-6">Today</span>
                @endif
            </h4>
            <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->addDay()->format('Y-m-d')]) }}"
                class="btn btn-light btn-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <div class="d-flex gap-2">
            <form action="{{ route('appointments.index') }}" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}"
                    onchange="this.form.submit()">
            </form>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                <i class="fa-solid fa-plus"></i> New Appointment
            </button>
        </div>
    </div>

    <div class="row g-4">
        @if($appointments->isEmpty())
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3"><i class="fa-regular fa-calendar-xmark fa-4x"></i></div>
                <h5>No appointments for this day</h5>
                <p>Enjoy your break or click "New Appointment" to book.</p>
            </div>
        @else
            @foreach($appointments as $appt)
                <div class="col-md-12">
                    <div
                        class="card border-0 shadow-sm border-start border-4 border-{{ $appt->type == 'control' ? 'info' : ($appt->type == 'urgency' ? 'danger' : 'primary') }}">
                        <div class="card-body py-2">
                            <div class="row align-items-center">

                                <div class="col-md-1 text-center border-end">
                                    <h5 class="fw-bold mb-0 text-dark">{{ $appt->scheduled_at->format('H:i') }}</h5>
                                    <small class="text-muted">{{ $appt->duration ?? 30 }}m</small>
                                </div>

                                <div class="col-md-4 ps-4">
                                    <div class="fw-bold fs-5">
                                        {{ $appt->patient->full_name }}
                                        @if($appt->type == 'control')
                                            <span class="badge bg-info bg-opacity-10 text-info ms-2">Control</span>
                                        @elseif($appt->type == 'urgency')
                                            <span class="badge bg-danger ms-2">URGENT</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa-solid fa-phone me-1"></i> {{ $appt->patient->phone ?? '-' }}
                                        @if($appt->notes)
                                            <span class="ms-2 text-warning"><i class="fa-solid fa-note-sticky"></i> Note
                                                available</span>
                                        @endif
                                    </small>
                                </div>

                                <div class="col-md-7 text-end">
                                    <div class="d-flex justify-content-end gap-2 align-items-center">

                                        @if($appt->status == 'scheduled')
                                            <span class="badge bg-secondary">Scheduled</span>
                                        @elseif($appt->status == 'waiting')
                                            <span class="badge bg-warning text-dark animate-pulse">Waiting Room</span>
                                        @elseif($appt->status == 'finished')
                                            <span class="badge bg-success">Finished</span>
                                        @endif

                                        <div class="vr mx-2"></div>

                                        @if($appt->status == 'scheduled')
                                            <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="waiting">
                                                <button class="btn btn-sm btn-outline-warning fw-bold">
                                                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Arrived
                                                </button>
                                            </form>
                                        @endif

                                        @if($appt->status == 'waiting')
                                            <a href="#" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-stethoscope"></i> Start Consult
                                            </a>
                                        @endif

                                        <button class="btn btn-sm btn-light text-danger" title="Cancel">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

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

                        <div id="newPatientForm" class="p-3 bg-light rounded border mb-3 d-none">
                            <h6 class="text-primary border-bottom pb-2">New Patient Details</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" name="new_first_name" class="form-control form-control-sm"
                                        placeholder="First Name">
                                </div>
                                <div class="col-6">
                                    <input type="text" name="new_last_name" class="form-control form-control-sm"
                                        placeholder="Last Name">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="new_phone" class="form-control form-control-sm"
                                        placeholder="Phone">
                                </div>
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
                                    <option value="consultation">Consultation (Standard)</option>
                                    <option value="control">Control (Follow-up)</option>
                                    <option value="urgency">Urgency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note (Optional)</label>
                                <input type="text" name="notes" class="form-control"
                                    placeholder="e.g. Fever, Stomach ache...">
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
        const searchInput = document.getElementById('patientSearchInput');
        const resultsBox = document.getElementById('searchResults');
        const patientIdInput = document.getElementById('patientIdInput');
        const newPatientForm = document.getElementById('newPatientForm');

        // Search Logic
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

        // Select Logic
        function selectPatient(patient) {
            patientIdInput.value = patient.id;
            document.getElementById('selectedPatientName').innerText = `${patient.first_name} ${patient.last_name}`;
            document.getElementById('selectedPatientDisplay').classList.remove('d-none');
            document.getElementById('patientSearchGroup').classList.add('d-none');
            resultsBox.style.display = 'none';
            newPatientForm.classList.add('d-none'); // Hide new form if open
        }

        // Reset Logic
        function resetPatientSelection() {
            patientIdInput.value = '';
            document.getElementById('selectedPatientDisplay').classList.add('d-none');
            document.getElementById('patientSearchGroup').classList.remove('d-none');
            searchInput.value = '';
            searchInput.focus();
        }

        // Toggle "New Patient" Mode
        function toggleNewPatientMode() {
            const isHidden = newPatientForm.classList.contains('d-none');
            if (isHidden) {
                newPatientForm.classList.remove('d-none');
                patientIdInput.value = ''; // Clear ID to force creation
                document.getElementById('patientSearchGroup').classList.add('d-none'); // Hide search
                document.getElementById('btnShowNewPatient').innerText = 'Cancel New';
            } else {
                newPatientForm.classList.add('d-none');
                document.getElementById('patientSearchGroup').classList.remove('d-none');
            }
        }
    </script>

@endsection