@extends('layouts.admin')

@section('title', 'Medical Catalog')
@section('header', 'Medical Catalog')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>

            <h4 class="mb-0 text-secondary">Medical Catalog</h4>
            <p class="text-muted small mb-0">Manage your frequent medicines and tests presets.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">

            {{-- TOGGLE BUTTONS (Replaces Old Tabs) --}}
            <div class="p-1 rounded-pill d-inline-flex border shadow-sm bg-white">

                {{-- Medicine Button --}}
                <button onclick="switchTab('medicine', this)" id="tab-medicine"
                    class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all {{ $type == 'medicine' ? 'btn-primary text-white shadow-sm' : 'text-muted bg-transparent' }}">
                    <i class="fa-solid fa-pills me-2"></i>Medicines
                </button>

                {{-- Lab Test Button --}}
                <button onclick="switchTab('test', this)" id="tab-test"
                    class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all {{ $type == 'test' ? 'btn-primary text-white shadow-sm' : 'text-muted bg-transparent' }}">
                    <i class="fa-solid fa-microscope me-2"></i>Lab Tests
                </button>

            </div>

            {{-- SCOPE FILTER --}}
            <select id="catalogScope" class="form-select form-select-sm shadow-sm border-0 bg-white" style="width: 140px;"
                onchange="performSearch()">
                <option value="all" {{ request('scope') == 'all' ? 'selected' : '' }}>Show All</option>
                <option value="mine" {{ request('scope') == 'mine' ? 'selected' : '' }}>My Items Only</option>
                <option value="system" {{ request('scope') == 'system' ? 'selected' : '' }}>System Only</option>
            </select>

            {{-- ADD BUTTON --}}
            <button class="btn btn-primary btn-sm fw-bold shadow-sm text-nowrap" data-bs-toggle="modal"
                data-bs-target="#addItemModal">
                <i class="fa-solid fa-plus me-2"></i>New Item
            </button>
        </div>
    </div>

    {{-- SEARCH BAR (Full Width Row) --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 ps-3"><i
                        class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="catalogSearch" class="form-control border-0 bg-white"
                    placeholder="Search medicines or tests..." onkeyup="performSearch()" value="{{ request('q') }}">
            </div>
        </div>
    </div>

    {{-- DYNAMIC LIST CONTAINER --}}
    <div class="card border-0 shadow-sm overflow-hidden" id="catalogContainer" style="min-height: 300px;">
        @include('layouts.partials.catalog_list')
    </div>

    {{-- MODAL INCLUDE --}}
    @include('layouts.partials.catalog_add_modal')

    {{-- SCRIPTS --}}
    <script>
        let searchTimeout;
        let currentType = "{{ $type }}";

        function performSearch() {
            let query = document.getElementById('catalogSearch').value;
            let scope = document.getElementById('catalogScope').value;

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                let container = document.getElementById('catalogContainer');
                container.style.opacity = '0.5';

                fetch(`{{ route('catalog.index') }}?type=${currentType}&q=${query}&scope=${scope}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                })
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                        container.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        container.style.opacity = '1';
                    });
            }, 300);
        }

        function switchTab(newType, btnElement) {
            currentType = newType;

            // 1. Reset ALL buttons to "Inactive" State
            // We select all buttons inside the toggle container (you can add a specific class to the container if needed)
            const allButtons = btnElement.parentElement.querySelectorAll('button');
            allButtons.forEach(btn => {
                btn.className = 'btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all text-muted bg-transparent';
            });

            // 2. Set CLICKED button to "Active" State (Blue & White)
            btnElement.className = 'btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all btn-primary text-white shadow-sm';

            // 3. Trigger Search
            performSearch();
        }
    </script>

    {{-- OPTIONAL: Add transition style locally if not in custom.css --}}
    <style>
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>

@endsection