@extends('layouts.admin')

@section('title', 'Medical Services')
@section('header', 'Services List')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                My Services
            </h4>
            <p class="text-muted small mb-0">Manage service pricing, duration, and insurance codes.</p>
        </div>
        
        <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#createServiceModal">
            <i class="fa-solid fa-plus me-2"></i>Add Service
        </button>
    </div>

    {{-- SEARCH & FILTER CARD --}}
    <div class="card mb-4">
        <div class="card-body p-2">
            <form action="{{ route('services.index') }}" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0 ps-3">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-0 bg-white" 
                           placeholder="Search services by name or code..." value="{{ request('q') }}">
                    <button type="submit" class="d-none btn btn-primary fw-bold px-4 rounded-end-3 d-none">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="ps-4 text-muted fw-bold small text-uppercase py-3">Code & Name</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Description</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Duration</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Price</th>
                            <th class="text-end pe-4 text-muted fw-bold small text-uppercase py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if($service->code)
                                            <div class="badge bg-dark text-white me-3 px-2 py-1 rounded-2 shadow-sm" style="min-width: 40px;">
                                                {{ $service->code }}
                                            </div>
                                        @else
                                            <div class="badge bg-light text-muted border me-3 px-2 py-1 rounded-2" style="min-width: 40px;">-</div>
                                        @endif
                                        
                                        <div>
                                            <div class="fw-bold text-dark {{ !$service->is_active ? 'text-muted text-decoration-line-through' : '' }}">
                                                {{ $service->name }}
                                            </div>
                                            @if(!$service->is_active)
                                                <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.65rem;">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    {{ Str::limit($service->description, 50) ?? '-' }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fa-regular fa-clock me-2 text-primary opacity-50"></i> 
                                        {{ $service->duration_minutes }} min
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        {{ number_format($service->price, 2) }} <small class="text-muted fw-normal">DH</small>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-white border shadow-sm text-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editServiceModal{{ $service->id }}"
                                                title="Edit Service">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Delete this service?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-white border shadow-sm text-danger" title="Delete Service">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('services.update', $service->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Edit Service</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">Code</label>
                                                        <input type="text" name="code" class="form-control bg-light" value="{{ $service->code }}">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label small fw-bold text-muted">Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Price (DH) <span class="text-danger">*</span></label>
                                                        <input type="number" name="price" class="form-control" value="{{ $service->price }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Duration (min)</label>
                                                        <input type="number" name="duration_minutes" class="form-control" value="{{ $service->duration_minutes }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold text-muted">Description</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $service->description }}</textarea>
                                                    </div>
                                                    <div class="col-12 mt-3">
                                                        <div class="form-check form-switch p-3 bg-light rounded border">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-medium ms-2" for="activeSwitch{{ $service->id }}">Active (Available for booking)</label>
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
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="fa-solid fa-notes-medical fa-3x mb-3"></i>
                                        <p class="mb-0">No services found. Add one to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($services->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="createServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">New Medical Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Code</label>
                                <input type="text" name="code" class="form-control bg-light" placeholder="e.g. C">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Consultation" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Price (DH) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" placeholder="300" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Duration (min)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="30">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Instructions for patient..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-white border text-muted shadow-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Add Service</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection