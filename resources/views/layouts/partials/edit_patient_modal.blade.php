<div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-4">
                
                {{-- Header --}}
                <div class="modal-header border-bottom-0">
                    <div>
                        <h5 class="modal-title fw-bold">Edit Patient</h5>
                        <p class="mb-0 text-muted small">Update medical and personal information</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 pt-0">
                    
                    {{-- Section 1: Identity --}}
                    <h6 class="text-uppercase text-primary small fw-bold mb-3 mt-2">
                        <i class="fa-regular fa-id-card me-1"></i> Identity
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $patient->first_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $patient->last_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CIN (ID)</label>
                            <input type="text" name="cin" class="form-control" value="{{ $patient->cin }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="male" {{ $patient->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $patient->gender == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Birth Date</label>
                        
                            <input type="date" name="birth_date" class="form-control" value="{{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('Y-m-d') : '' }}">

                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $patient->phone }}">
                        </div>
                    </div>

                    {{-- Section 2: Insurance (Added) --}}
                    <h6 class="text-uppercase text-primary small fw-bold mb-3 border-top pt-3">
                        <i class="fa-solid fa-shield-heart me-1"></i> Insurance (Mutuelle)
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Provider</label>
                            <select name="mutuelle_provider" class="form-select">
                                <option value="" {{ empty($patient->mutuelle_provider) ? 'selected' : '' }}>None</option>
                                <option value="CNSS" {{ $patient->mutuelle_provider == 'CNSS' ? 'selected' : '' }}>CNSS</option>
                                <option value="CNOPS" {{ $patient->mutuelle_provider == 'CNOPS' ? 'selected' : '' }}>CNOPS</option>
                                <option value="AXA" {{ $patient->mutuelle_provider == 'AXA' ? 'selected' : '' }}>AXA</option>
                                <option value="RMA" {{ $patient->mutuelle_provider == 'RMA' ? 'selected' : '' }}>RMA</option>
                                
                                @if($patient->mutuelle_provider && !in_array($patient->mutuelle_provider, ['CNSS', 'CNOPS', 'AXA', 'RMA']))
                                    <option value="{{ $patient->mutuelle_provider }}" selected>{{ $patient->mutuelle_provider }}</option>
                                @endif
                            </select>
                        </div>                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Membership / Policy Number</label>
                            <input type="text" name="mutuelle_number" class="form-control" 
                                   placeholder="XXX-XXXXX" 
                                   value="{{ $patient->mutuelle_number }}">
                        </div>
                    </div>

                    {{-- Section 3: Medical & Address --}}
                    <h6 class="text-uppercase text-primary small fw-bold mb-3 border-top pt-3">
                        <i class="fa-solid fa-file-medical me-1"></i> Details
                    </h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $patient->address }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Medical History / Notes</label>
                            <textarea name="medical_history" class="form-control bg-warning bg-opacity-10 border-warning border-opacity-25 text-dark" 
                                      rows="3">{{ $patient->medical_history }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer border-top-0 px-4 pb-4 justify-content-between pt-0">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0" 
                            onclick="if(confirm('Delete this patient? This cannot be undone.')) document.getElementById('delete-pat-{{ $patient->id }}').submit();">
                        <i class="fa-regular fa-trash-can me-1"></i> Delete
                    </button>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Hidden Delete Form --}}
        <form id="delete-pat-{{ $patient->id }}" action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-none">
            @csrf @method('DELETE')
        </form>
    </div>
</div>