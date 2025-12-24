@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Document Manager</h4>
            <p class="text-muted small mb-0">Manage your clinic's templates and medical files.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createDocModal">
            <i class="fa-solid fa-plus me-2"></i> Create Document
        </button>
    </div>

    <div class="row g-4">
        @forelse($documents as $doc)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card  border-0 shadow-sm hover-elevate transition">
                    <a href="{{ route('documents.editor', $doc->id) }}" class="text-decoration-none text-dark">
                        <div class="card-body pb-0 text-center p-4">
                            <div class="mb-3">
                                <div class="avatar avatar-lg rounded-circle bg-light-primary text-primary mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                    <i class="fa-regular fa-file-lines fs-2"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-1 text-truncate">{{ $doc->name }}</h6>
                            <span class="badge bg-light text-secondary border mb-2">{{ $doc->role ?? 'General' }}</span>
                        </div>
                    </a>

                    <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between align-items-center px-4 pt-0">
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Edited {{ $doc->updated_at->diffForHumans() }}
                        </small>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.editor', $doc->id) }}">
                                        <i class="fa-regular fa-pen-to-square me-2 text-primary"></i> Open Editor
                                    </a>
                                </li>
                                <li>
                                    <button class="dropdown-item" 
                                            onclick="openEditModal({{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ addslashes($doc->role ?? '') }}')">
                                        <i class="fa-solid fa-tag me-2 text-secondary"></i> Rename / Edit Role
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fa-regular fa-trash-can me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="mb-3 opacity-25">
                    <i class="fa-regular fa-folder-open display-1"></i>
                </div>
                <h5 class="text-muted">No documents found</h5>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="createDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('documents.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Document Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Blood Test Report" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Role / Category</label>
                    <input type="text" name="role" class="form-control" placeholder="e.g. Lab Report" value="General">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create & Open</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editDocForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Document Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Document Name</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Role / Category</label>
                    <input type="text" name="role" id="editRole" class="form-control">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, role) {
        // Set the form action dynamically based on ID
        const form = document.getElementById('editDocForm');
        form.action = `/documents/${id}`;

        // Populate inputs
        document.getElementById('editName').value = name;
        document.getElementById('editRole').value = role;

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('editDocModal'));
        modal.show();
    }
</script>

<style>
    .hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .bg-light-primary { background-color: rgba(13, 110, 253, 0.1); }
</style>
@endsection