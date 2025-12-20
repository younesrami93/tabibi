@extends('layouts.admin')

@section('title', 'Manage Clinics')
@section('header', 'Clinics Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-secondary">All Cabinets</h4>
            <small class="text-muted">Manage subscriptions, credits, and configuration</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClinicModal">
            <i class="fa-solid fa-plus"></i> New Clinic
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Clinic Name</th>
                        <th>Staff Accounts</th> <th>Config Preview</th>
                        <th>Subscription</th>
                        <th>Financials (Credit)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clinics as $clinic)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $clinic->name }}</div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-phone me-1"></i> {{ $clinic->phone ?? '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" title="Doctors">
                                        <i class="fa-solid fa-user-doctor me-1"></i> 
                                        {{ $clinic->users->where('role', 'doctor')->count() }}
                                    </span>
                                    
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" title="Assistants">
                                        <i class="fa-solid fa-clipboard-user me-1"></i> 
                                        {{ $clinic->users->where('role', 'secretary')->count() }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $clinic->config('currency_code') }}
                                </span>
                                <div class="small text-muted mt-1">
                                    <i class="fa-regular fa-clock"></i> {{ $clinic->config('slot_duration') }} min
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $clinic->plan_type }}</span>
                                <br>
                                <small class="text-{{ $clinic->subscription_expires_at && $clinic->subscription_expires_at->isPast() ? 'danger' : 'success' }}">
                                    {{ $clinic->subscription_expires_at ? $clinic->subscription_expires_at->format('d/m/Y') : 'Lifetime' }}
                                </small>
                            </td>

                            <td>
                                <div>Price: {{ number_format($clinic->subscription_price, 2) }}</div>
                                @if($clinic->balance_due > 0)
                                    <span class="badge bg-danger">
                                        Rest: {{ number_format($clinic->balance_due, 2) }}
                                    </span>
                                @else
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>

                            <td>
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editClinicModal{{ $clinic->id }}">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="editClinicModal{{ $clinic->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('clinics.update', $clinic->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit {{ $clinic->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            
                                            <h6 class="text-primary border-bottom pb-2 mb-3">1. Clinic Details</h6>
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="clinic_name" class="form-control" value="{{ $clinic->name }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="clinic_phone" class="form-control" value="{{ $clinic->phone }}">
                                                </div>
                                            </div>

                                            <h6 class="text-primary border-bottom pb-2 mb-3">2. Subscription & Credit</h6>
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Plan Type</label>
                                                    <select name="plan_type" class="form-select">
                                                        <option value="monthly" {{ $clinic->plan_type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                        <option value="yearly" {{ $clinic->plan_type == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                        <option value="lifetime" {{ $clinic->plan_type == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Total Price</label>
                                                    <input type="number" name="subscription_price" class="form-control" value="{{ $clinic->subscription_price }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Amount Paid</label>
                                                    <input type="number" name="amount_paid" class="form-control" value="{{ $clinic->total_paid }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Expiry Date</label>
                                                    <input type="date" name="expires_at" class="form-control" value="{{ $clinic->subscription_expires_at ? $clinic->subscription_expires_at->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>

                                            <h6 class="text-primary border-bottom pb-2 mb-3">3. Configuration Settings</h6>
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Currency Code</label>
                                                    <input type="text" name="config[currency_code]" class="form-control" value="{{ $clinic->config('currency_code') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Symbol</label>
                                                    <input type="text" name="config[currency_symbol]" class="form-control" value="{{ $clinic->config('currency_symbol') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Country</label>
                                                    <input type="text" name="config[country]" class="form-control" value="{{ $clinic->config('country') }}">
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" name="config[calendar_start_time]" class="form-control" value="{{ $clinic->config('calendar_start_time') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" name="config[calendar_end_time]" class="form-control" value="{{ $clinic->config('calendar_end_time') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Slot Duration (min)</label>
                                                    <input type="number" name="config[slot_duration]" class="form-control" value="{{ $clinic->config('slot_duration') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $clinics->links() }}
        </div>
    </div>

    <div class="modal fade" id="createClinicModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('clinics.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Clinic & Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <h6 class="text-primary border-bottom pb-2 mb-3">1. Clinic Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Clinic Name <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" class="form-control" placeholder="e.g. Cabinet Al Shifa" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="clinic_phone" class="form-control" placeholder="0536...">
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3">2. Head Doctor Account</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Doctor Name <span class="text-danger">*</span></label>
                                <input type="text" name="doctor_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email (Login) <span class="text-danger">*</span></label>
                                <input type="email" name="doctor_email" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="text" name="doctor_password" class="form-control" value="123456" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3">3. Subscription Contract</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Plan Type</label>
                                <select name="plan_type" class="form-select">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly" selected>Yearly</option>
                                    <option value="lifetime">Lifetime</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total Price (DH)</label>
                                <input type="number" name="subscription_price" class="form-control" value="2000">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Paid Today (DH)</label>
                                <input type="number" name="amount_paid" class="form-control" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ends On</label>
                                <input type="date" name="expires_at" class="form-control" value="{{ now()->addYear()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3">4. Default Configuration</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Currency Code</label>
                                <input type="text" name="config[currency_code]" class="form-control" value="MAD">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Symbol</label>
                                <input type="text" name="config[currency_symbol]" class="form-control" value="DH">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="config[country]" class="form-control" value="Morocco">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Work Start</label>
                                <input type="time" name="config[calendar_start_time]" class="form-control" value="09:00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Work End</label>
                                <input type="time" name="config[calendar_end_time]" class="form-control" value="18:00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Slot Duration (min)</label>
                                <input type="number" name="config[slot_duration]" class="form-control" value="30">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection