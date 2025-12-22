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