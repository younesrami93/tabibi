@extends('layouts.admin')

@section('title', 'Medical Catalog')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fa-solid fa-book-medical text-primary me-2"></i> Medical Catalog</h4>
            <p class="text-muted small mb-0">Manage your frequent medicines and test presets.</p>
        </div>

        <div class="d-flex gap-2">

            {{-- NEW: SCOPE FILTER --}}
            <select id="catalogScope" class="form-select shadow-sm" style="width: 150px;" onchange="performSearch()">
                <option value="all" {{ request('scope') == 'all' ? 'selected' : '' }}>Show All</option>
                <option value="mine" {{ request('scope') == 'mine' ? 'selected' : '' }}>My Items Only</option>
                <option value="system" {{ request('scope') == 'system' ? 'selected' : '' }}>System Only</option>
            </select>

            {{-- SEARCH BAR --}}
            <div class="input-group shadow-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" id="catalogSearch" class="form-control border-start-0 ps-0" placeholder="Search..."
                    onkeyup="performSearch()" value="{{ request('q') }}">
            </div>

            {{-- ADD BUTTON --}}
            <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal"
                data-bs-target="#addItemModal">
                <i class="fa-solid fa-plus me-2"></i> New Item
            </button>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $type == 'medicine' ? 'active fw-bold' : '' }}" href="javascript:void(0)"
                onclick="switchTab('medicine')">
                <i class="fa-solid fa-pills me-2"></i> Medicines
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type == 'test' ? 'active fw-bold' : '' }}" href="javascript:void(0)"
                onclick="switchTab('test')">
                <i class="fa-solid fa-microscope me-2"></i> Lab Tests
            </a>
        </li>
    </ul>

    {{-- DYNAMIC LIST CONTAINER --}}
    <div class="card border-0 shadow-sm" id="catalogContainer">
        @include('layouts.partials.catalog_list')
    </div>

    {{-- MODAL INCLUDE --}}
    @include('layouts.partials.catalog_add_modal')
    {{-- UPDATED JAVASCRIPT --}}
    <script>
        let searchTimeout;
        // Store current type in a JS variable so we don't lose it when filtering
        let currentType = "{{ $type }}";

        function performSearch() {
            let query = document.getElementById('catalogSearch').value;
            let scope = document.getElementById('catalogScope').value; // Get Filter Value

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                document.getElementById('catalogContainer').style.opacity = '0.5';

                // Send Type + Query + Scope
                fetch(`{{ route('catalog.index') }}?type=${currentType}&q=${query}&scope=${scope}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('catalogContainer').innerHTML = html;
                        document.getElementById('catalogContainer').style.opacity = '1';
                    })
                    .catch(error => console.error('Error:', error));
            }, 300);
        }

        // Helper to switch tabs without reloading page (optional, but smoother)
        function switchTab(newType) {
            currentType = newType;

            // Update visual active state
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active', 'fw-bold'));
            event.currentTarget.classList.add('active', 'fw-bold');

            performSearch(); // Refresh list with new type
        }
    </script>

@endsection