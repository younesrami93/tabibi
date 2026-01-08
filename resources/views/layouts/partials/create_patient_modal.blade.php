<div class="modal fade" id="createPatientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('patients.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Create New Patient File</h5>
                        <p class="text-muted small mb-0">Fill in the details to register a new patient.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Personal Info --}}
                        <div class="col-12">
                            <h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Personal
                                Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">First Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Last Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CIN (ID)</label>
                            <input type="text" name="cin" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Birth Date</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Gender <span
                                    class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        {{-- Insurance --}}
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Insurance
                                (Optional)</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Provider</label>
                            <select name="mutuelle_provider" class="form-select">
                                <option value="">None</option>
                                <option value="CNSS">CNSS</option>
                                <option value="CNOPS">CNOPS</option>
                                <option value="AXA">AXA</option>
                                <option value="RMA">RMA</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Registration Number</label>
                            <input type="text" name="mutuelle_number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                    <button type="button" class="btn btn-white border text-muted shadow-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Create File</button>
                </div>
            </div>
        </form>
    </div>
</div>