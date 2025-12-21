@extends('layouts.admin')

@section('title', 'Patient Management')
@section('header', 'All Patients')

@section('content')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form action="{{ route('patients.index') }}" method="GET">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Search by CIN, Name, or Phone..." value="{{ request('search') }}">
                            <button class="btn btn-dark" type="submit">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                        <i class="fa-solid fa-user-plus"></i> Add New Patient
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Patient Name</th>
                            <th>Info (CIN / Phone)</th>
                            <th>Age / Gender</th>
                            <th>Mutuelle</th>
                            <th>Balance</th>
                            <th>Visits</th>
                            <th>Last Visit</th>
                            <th>Next Control</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-{{ $patient->gender == 'male' ? 'primary' : 'danger' }} bg-opacity-10 text-{{ $patient->gender == 'male' ? 'primary' : 'danger' }} fw-bold d-flex justify-content-center align-items-center rounded-circle"
                                            style="width: 40px; height: 40px;">
                                            {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $patient->full_name }}</div>
                                            <small class="text-muted">File #{{ $patient->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="mb-1"><i class="fa-regular fa-id-card me-1 text-muted"></i>
                                            {{ $patient->cin ?? '--' }}</span>
                                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>
                                            {{ $patient->phone ?? '--' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $patient->age }}</div>
                                    <small class="text-uppercase text-muted">{{ $patient->gender }}</small>
                                </td>
                                <td>
                                    @if($patient->mutuelle_provider)
                                        <span class="badge bg-info text-dark">{{ $patient->mutuelle_provider }}</span>
                                    @else
                                        <span class="text-muted small">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->current_balance > 0)
                                        <span class="badge bg-danger">Credit:
                                            {{ number_format($patient->current_balance, 2) }}</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">Clear</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark">
                                        {{ $patient->appointments_count }} Visits
                                    </span>
                                </td>



                                <td>
                                    @if($patient->lastAppointment)
                                        <div>{{ $patient->lastAppointment->scheduled_at->format('d M, Y') }}</div>
                                        <small class="text-muted">
                                            {{ $patient->lastAppointment->scheduled_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted small">No visits yet</span>
                                    @endif
                                </td>

                                <td>
                                    @if($patient->nextControl)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                @if($patient->nextControl->scheduled_at->isPast())
                                                    <div class="text-danger fw-bold" title="Overdue">
                                                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                                                        {{ $patient->nextControl->scheduled_at->format('d/m') }}
                                                    </div>
                                                    <small class="text-danger">Late</small>
                                                @else
                                                    <div class="text-primary fw-bold">
                                                        <i class="fa-regular fa-calendar-check me-1"></i>
                                                        {{ $patient->nextControl->scheduled_at->format('d/m') }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ $patient->nextControl->scheduled_at->format('H:i') }}</small>
                                                @endif
                                            </div>

                                            <form action="{{ route('appointments.complete_control', $patient->nextControl->id) }}"
                                                method="POST" class="ms-2">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-success border-0 bg-success bg-opacity-10"
                                                    title="Mark Control as Done Today">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small opacity-50">No Control</span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal"
                                            data-bs-target="#editPatientModal{{ $patient->id }}" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="{{ route('patients.show', $patient->id) }}"
                                            class="btn btn-sm btn-light border text-primary" title="History">
                                            <i class="fa-solid fa-file-medical"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editPatientModal{{ $patient->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <form action="{{ route('patients.update', $patient->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Patient: {{ $patient->full_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" name="first_name" class="form-control"
                                                            value="{{ $patient->first_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" name="last_name" class="form-control"
                                                            value="{{ $patient->last_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">CIN</label>
                                                        <input type="text" name="cin" class="form-control"
                                                            value="{{ $patient->cin }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" name="phone" class="form-control"
                                                            value="{{ $patient->phone }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Birth Date</label>
                                                        <input type="date" name="birth_date" class="form-control"
                                                            value="{{ $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Gender</label>
                                                        <select name="gender" class="form-select">
                                                            <option value="male" {{ $patient->gender == 'male' ? 'selected' : '' }}>Male</option>
                                                            <option value="female" {{ $patient->gender == 'female' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 border-top pt-3 mt-2">
                                                        <h6 class="text-primary mb-3">Insurance (Mutuelle)</h6>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Provider</label>
                                                        <select name="mutuelle_provider" class="form-select">
                                                            <option value="">None</option>
                                                            <option value="CNSS" {{ $patient->mutuelle_provider == 'CNSS' ? 'selected' : '' }}>CNSS</option>
                                                            <option value="CNOPS" {{ $patient->mutuelle_provider == 'CNOPS' ? 'selected' : '' }}>CNOPS</option>
                                                            <option value="AXA" {{ $patient->mutuelle_provider == 'AXA' ? 'selected' : '' }}>AXA</option>
                                                            <option value="RMA" {{ $patient->mutuelle_provider == 'RMA' ? 'selected' : '' }}>RMA</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Registration Number</label>
                                                        <input type="text" name="mutuelle_number" class="form-control"
                                                            value="{{ $patient->mutuelle_number }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-danger me-auto"
                                                    onclick="if(confirm('Archive this patient?')) document.getElementById('delete-pat-{{ $patient->id }}').submit()">Archive</button>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="delete-pat-{{ $patient->id }}"
                                        action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-user-group fa-2x mb-3 opacity-50"></i>
                                    <p>No patients found. Add your first patient above!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $patients->links() }}
        </div>
    </div>

    <div class="modal fade" id="createPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('patients.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Patient File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CIN</label>
                                <input type="text" name="cin" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Birth Date</label>
                                <input type="date" name="birth_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            <div class="col-12 border-top pt-3 mt-2">
                                <h6 class="text-primary mb-3">Insurance (Optional)</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Provider</label>
                                <select name="mutuelle_provider" class="form-select">
                                    <option value="">None</option>
                                    <option value="CNSS">CNSS</option>
                                    <option value="CNOPS">CNOPS</option>
                                    <option value="AXA">AXA</option>
                                    <option value="RMA">RMA</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="mutuelle_number" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create File</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection