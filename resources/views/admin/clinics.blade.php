@extends('layouts.admin')

@section('title', 'Manage Clinics')
@section('header', 'Clinics Management')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 text-secondary">
               All Cabinets
            </h4>
            <p class="text-muted small mb-0">Manage subscriptions, credits, and system configuration.</p>
        </div>
        
        <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#createClinicModal">
            <i class="fa-solid fa-plus me-2"></i>New Clinic
        </button>
    </div>

    {{-- ALERTS --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center gap-2 mb-2 fw-bold">
                <i class="fa-solid fa-circle-exclamation"></i> Please fix the following errors:
            </div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Clinic Name</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Staff Accounts</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Config Preview</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Subscription</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Financials</th>
                        <th class="text-end pe-4 py-3 text-muted small fw-bold text-uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clinics as $clinic)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-white border shadow-sm text-primary rounded-3 me-3" style="width: 40px; height: 40px;">
                                        <i class="fa-solid fa-hospital fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $clinic->name }}</div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-phone me-1" style="font-size: 0.7rem;"></i> {{ $clinic->phone ?? 'No Phone' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3" title="Doctors">
                                        <i class="fa-solid fa-user-doctor me-1"></i> 
                                        {{ $clinic->users->where('role', 'doctor')->count() }}
                                    </span>
                                    
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3" title="Secretaries">
                                        <i class="fa-solid fa-id-card-clip me-1"></i> 
                                        {{ $clinic->users->where('role', 'secretary')->count() }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border fw-medium">
                                        {{ $clinic->config('currency_code') }}
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fa-regular fa-clock me-1 text-primary opacity-50"></i>{{ $clinic->config('slot_duration') }}m
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill text-uppercase mb-1" style="width: fit-content;">
                                        {{ $clinic->plan_type }}
                                    </span>
                                    <small class="fw-medium text-{{ $clinic->subscription_expires_at && $clinic->subscription_expires_at->isPast() ? 'danger' : 'success' }}">
                                        @if($clinic->subscription_expires_at)
                                            <i class="fa-regular fa-calendar me-1"></i>{{ $clinic->subscription_expires_at->format('d M, Y') }}
                                        @else
                                            <i class="fa-solid fa-infinity me-1"></i>Lifetime
                                        @endif
                                    </small>
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ number_format($clinic->subscription_price, 2) }} <span class="small fw-normal text-muted">DH</span></div>
                                @if($clinic->balance_due > 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">
                                        Due: {{ number_format($clinic->balance_due, 2) }}
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">
                                        Paid <i class="fa-solid fa-check ms-1"></i>
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-white border shadow-sm text-primary fw-bold" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editClinicModal{{ $clinic->id }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                            </td>
                        </tr>

                        {{-- EDIT MODAL (Inside Loop) --}}
                        <div class="modal fade" id="editClinicModal{{ $clinic->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <form action="{{ route('clinics.update', $clinic->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <div>
                                                <h5 class="modal-title fw-bold">Edit Clinic</h5>
                                                <p class="text-muted small mb-0">Update settings for {{ $clinic->name }}</p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            
                                            <div class="bg-light p-3 rounded border mb-3">
                                                <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-hospital me-2"></i>Clinic Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Name</label>
                                                        <input type="text" name="clinic_name" class="form-control" value="{{ $clinic->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Phone</label>
                                                        <input type="text" name="clinic_phone" class="form-control" value="{{ $clinic->phone }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-light p-3 rounded border mb-3">
                                                <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-file-contract me-2"></i>Subscription & Credit</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Plan Type</label>
                                                        <select name="plan_type" class="form-select">
                                                            <option value="monthly" {{ $clinic->plan_type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="yearly" {{ $clinic->plan_type == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                            <option value="lifetime" {{ $clinic->plan_type == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Total Price</label>
                                                        <input type="number" name="subscription_price" class="form-control" value="{{ $clinic->subscription_price }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Amount Paid</label>
                                                        <input type="number" name="amount_paid" class="form-control" value="{{ $clinic->total_paid }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Expiry Date</label>
                                                        <input type="date" name="expires_at" class="form-control" value="{{ $clinic->subscription_expires_at ? $clinic->subscription_expires_at->format('Y-m-d') : '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-light p-3 rounded border">
                                                <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-sliders me-2"></i>Configuration</h6>
                                                <div class="row g-3 mb-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Currency Code</label>
                                                        <input type="text" name="config[currency_code]" class="form-control" value="{{ $clinic->config('currency_code') }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">Symbol</label>
                                                        <input type="text" name="config[currency_symbol]" class="form-control" value="{{ $clinic->config('currency_symbol') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Country</label>
                                                        <input type="text" name="config[country]" class="form-control" value="{{ $clinic->config('country') }}">
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">Work Start</label>
                                                        <input type="time" name="config[calendar_start_time]" class="form-control" value="{{ $clinic->config('calendar_start_time') }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">Work End</label>
                                                        <input type="time" name="config[calendar_end_time]" class="form-control" value="{{ $clinic->config('calendar_end_time') }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">Slot (min)</label>
                                                        <input type="number" name="config[slot_duration]" class="form-control" value="{{ $clinic->config('default_price') }}">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                                            <button type="button" class="btn btn-white border text-muted shadow-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($clinics->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $clinics->links() }}
            </div>
        @endif
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="createClinicModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('clinics.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">Create New Clinic</h5>
                            <p class="text-muted small mb-0">Setup a new cabinet and head doctor account.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        
                        <div class="bg-light p-3 rounded border mb-3">
                            <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-hospital me-2"></i>1. Clinic Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Clinic Name <span class="text-danger">*</span></label>
                                    <input type="text" name="clinic_name" class="form-control" placeholder="e.g. Cabinet Al Shifa" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Phone</label>
                                    <input type="text" name="clinic_phone" class="form-control" placeholder="0536...">
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded border mb-3">
                            <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-user-doctor me-2"></i>2. Head Doctor Account</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Doctor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="doctor_name" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Email (Login) <span class="text-danger">*</span></label>
                                    <input type="email" name="doctor_email" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Password <span class="text-danger">*</span></label>
                                    <input type="text" name="doctor_password" class="form-control" value="123456" required>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded border mb-3">
                            <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-file-contract me-2"></i>3. Subscription Contract</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Plan Type</label>
                                    <select name="plan_type" class="form-select">
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly" selected>Yearly</option>
                                        <option value="lifetime">Lifetime</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Total Price (DH)</label>
                                    <input type="number" name="subscription_price" class="form-control" value="2000">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Paid Today (DH)</label>
                                    <input type="number" name="amount_paid" class="form-control" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Ends On</label>
                                    <input type="date" name="expires_at" class="form-control" value="{{ now()->addYear()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded border">
                            <h6 class="text-primary fw-bold small text-uppercase mb-3"><i class="fa-solid fa-sliders me-2"></i>4. Default Configuration</h6>
                            <div class="row g-3 mb-2">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Currency</label>
                                    <input type="text" name="config[currency_code]" class="form-control" value="MAD">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Symbol</label>
                                    <input type="text" name="config[currency_symbol]" class="form-control" value="DH">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Country</label>
                                    <input type="text" name="config[country]" class="form-control" value="Morocco">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Work Start</label>
                                    <input type="time" name="config[calendar_start_time]" class="form-control" value="09:00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Work End</label>
                                    <input type="time" name="config[calendar_end_time]" class="form-control" value="18:00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Slot (min)</label>
                                    <input type="number" name="config[slot_duration]" class="form-control" value="30">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-white border text-muted shadow-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection