@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')

    {{-- ALERT: Missed Appointments --}}
    @if(isset($missedCount) && $missedCount > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm mb-4">
            <div><i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>{{ $missedCount }} unprocessed</strong>
                appointments from past days.</div>
            <a href="{{ route('appointments.index', ['filter_mode' => 'history']) }}"
                class="btn btn-sm btn-warning fw-bold">Review History</a>
        </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">

                {{-- Date Navigation --}}
                <div class="col-md-3 d-flex align-items-center gap-2">
                    @if(request('filter_mode') != 'history')
                        <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->subDay()->format('Y-m-d')]) }}"
                            class="btn btn-light btn-sm"><i class="fa-solid fa-chevron-left"></i></a>
                        <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse(request('date', now()))->format('D, d M') }}</span>
                        <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::parse(request('date', now()))->addDay()->format('Y-m-d')]) }}"
                            class="btn btn-light btn-sm"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <span class="badge bg-secondary">Viewing History</span>
                    @endif
                </div>

                {{-- NEW: Quick Search Input (Client Side to preserve numbering) --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i
                                class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="pageSearch" class="form-control border-0 bg-light"
                            placeholder="Find patient in list..." onkeyup="filterAppointments()">
                    </div>
                </div>

                {{-- Filters --}}
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

                                {{-- COL 1: QUEUE POSITION (Replaces Time) --}}
                                <div class="col-md-1 text-center border-end">
                                    @if(request('filter_mode', 'today_active') == 'today_active')
                                        {{-- Visual Rank --}}
                                        <div class="fs-3 fw-bold {{ $index == 0 ? 'text-success' : 'text-primary' }}">
                                            #{{ $index + 1 }}
                                        </div>

                                        {{-- Context Text --}}
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
                                        {{-- In History mode, showing time is better than rank --}}
                                        <h5 class="fw-bold mb-0 text-muted">{{ $appt->scheduled_at->format('H:i') }}</h5>
                                    @endif
                                </div>

                                {{-- COL 2: PATIENT INFO (Time Moved Here) --}}
                                <div class="col-md-4 ps-3">
                                    <div class="fw-bold fs-5">
                                        {{ $appt->patient->full_name }}
                                        @if($appt->type == 'urgency') <span class="badge bg-danger">URGENT</span> @endif
                                        @if($appt->status == 'finished') <span class="badge bg-secondary">Done</span> @endif
                                    </div>
                                    <div class="small text-muted">
                                        {{-- Time is now shown here --}}
                                        <i class="fa-regular fa-clock me-1"></i> {{ $appt->scheduled_at->format('H:i') }}
                                        <span class="mx-1">|</span>
                                        <i class="fa-solid fa-phone me-1"></i> {{ $appt->patient->phone }}
                                    </div>
                                </div>

                                {{-- COL 3: ACTIONS --}}
                                <div class="col-md-7 text-end">
                                    <div class="d-flex justify-content-end gap-2 align-items-center">

                                        {{-- VIEW DETAILS BUTTON --}}
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

                {{-- Include Modals (Finish & View) here inside the loop as discussed previously --}}
                {{-- ... [Copy the Finish Modal & View Modal code here from previous step] ... --}}
                <div class="modal fade" id="finishModal-{{ $appt->id }}" tabindex="-1">
                    {{-- (Keep your existing modal code) --}}
                    <div class="modal-dialog modal-lg">
                        <form action="{{ route('appointments.finish', $appt->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Finalize Appointment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- Service Table Logic --}}
                                    <table class="table table-bordered table-sm" id="servicesTable-{{ $appt->id }}">
                                        <tbody id="serviceRows-{{ $appt->id }}">
                                            @foreach($appt->services as $ix => $s)
                                                <tr class="service-row">
                                                    <td>{{ $s->name }}<input type="hidden" name="services[{{ $ix }}][id]"
                                                            value="{{ $s->id }}"></td>
                                                    <td><input type="number" class="form-control form-control-sm price-input"
                                                            name="services[{{ $ix }}][price]" value="{{ $s->pivot->price }}"
                                                            onchange="calculateTotal({{ $appt->id }})"></td>
                                                    <td><button type="button" class="btn btn-sm btn-danger"
                                                            onclick="removeRow(this, {{ $appt->id }})">X</button></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{-- Add Service UI ... --}}
                                    <div class="d-flex gap-2 mb-3">
                                        <select class="form-select form-select-sm" id="newServiceSelect-{{ $appt->id }}">
                                            <option value="">-- Add Service --</option>
                                            @foreach($allServices as $srv)
                                                <option value="{{ $srv->id }}" data-name="{{ $srv->name }}"
                                                    data-price="{{ $srv->price }}">{{ $srv->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-sm btn-secondary"
                                            onclick="addServiceRow({{ $appt->id }})">Add</button>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total:</span>
                                        <span class="fw-bold fs-5 text-primary"><span id="totalDisplay-{{ $appt->id }}">0.00</span>
                                            MAD</span>
                                    </div>
                                    <div class="mt-3">
                                        <label>Paid Amount</label>
                                        <input type="number" name="paid_amount" class="form-control form-control-sm"
                                            placeholder="Amount received...">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success w-100">Finish</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

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



    <script>
        // ==========================================
        // 1. CLIENT-SIDE SEARCH FILTER
        // ==========================================
        function filterAppointments() {
            // Get the search query and convert to lowercase
            let input = document.getElementById('pageSearch').value.toLowerCase();
            // Get all appointment cards
            let cards = document.getElementsByClassName('appointment-card');

            // Loop through all cards
            for (let i = 0; i < cards.length; i++) {
                // Get the patient name stored in the data attribute
                let name = cards[i].getAttribute('data-patient-name');

                // Check if the name matches the search input
                if (name.includes(input)) {
                    cards[i].classList.remove('d-none'); // Show
                } else {
                    cards[i].classList.add('d-none');    // Hide
                }
            }
        }

        // ==========================================
        // 2. MODAL: ADD SERVICE ROW
        // ==========================================
        function addServiceRow(apptId) {
            // Get references to the dropdown and the table body for this specific appointment
            const select = document.getElementById(`newServiceSelect-${apptId}`);
            const tbody = document.getElementById(`serviceRows-${apptId}`);

            // Get selected option data
            const selectedOption = select.options[select.selectedIndex];

            // Validation: Stop if no service is selected
            if (!select.value) return;

            const id = select.value;
            const name = selectedOption.getAttribute('data-name');
            const price = selectedOption.getAttribute('data-price');

            // Generate a unique index (timestamp) so input names don't conflict
            const index = Date.now();

            // Create the new table row HTML
            const row = `
                <tr class="service-row">
                    <td>
                        ${name}
                        <input type="hidden" name="services[${index}][id]" value="${id}">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm price-input"
                            name="services[${index}][price]" value="${price}"
                            oninput="calculateTotal(${apptId})">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this, ${apptId})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                `;

            // Append the row to the table
            tbody.insertAdjacentHTML('beforeend', row);

            // Reset the dropdown
            select.value = "";

            // Recalculate the total price
            calculateTotal(apptId);
        }

        // ==========================================
        // 3. MODAL: REMOVE SERVICE ROW
        // ==========================================
        function removeRow(btn, apptId) {
            // Remove the row (<tr>) that contains the clicked button
            btn.closest('tr').remove();

            // Recalculate the total price
            calculateTotal(apptId);
        }

        // ==========================================
        // 4. MODAL: CALCULATE TOTAL
        // ==========================================
        function calculateTotal(apptId) {
            // Find all price inputs for this specific modal
            const inputs = document.querySelectorAll(`#serviceRows-${apptId} .price-input`);
            let total = 0;

            // Sum up the values
            inputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Update the total display text
            document.getElementById(`totalDisplay-${apptId}`).innerText = total.toFixed(2);
        }

        // ==========================================
        // 5. INITIALIZE TOTALS ON PAGE LOAD
        // ==========================================
        document.addEventListener("DOMContentLoaded", function () {
            // Loop through all appointments rendered by Blade to set initial totals
            @foreach($appointments as $appt)
                calculateTotal({{ $appt->id }});
            @endforeach
                });
    </script>
@endsection