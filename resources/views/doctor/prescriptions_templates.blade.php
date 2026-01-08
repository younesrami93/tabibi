@extends('layouts.admin')

@section('title', 'Prescription Templates')
@section('header', 'Manage Prescription')

@section('content')

    {{-- HEADER SECTION --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

        <div>
            <h4 class="mb-1 text-secondary">
                Prescription Templates
            </h4>
            <p class="text-muted small mb-0">
                Create and manage standard prescription protocols for quick use during appointments.
            </p>
        </div>


        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
            <i class="fa-solid fa-plus me-2"></i>New Template
        </button>
    </div>

    {{-- SEARCH & FILTER CARD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="d-flex flex-column flex-md-row gap-2">
                {{-- Search Bar --}}
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-white border-0 ps-3">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>
                    <input type="text" id="pageSearch" class="form-control border-0 bg-white"
                        placeholder="Search templates by name..." onkeyup="filterTemplates()">
                </div>

                <div class="d-none d-md-block vr my-2 text-muted opacity-25"></div>

                {{-- Filter Dropdown --}}
                <div class="d-flex align-items-center px-2">
                    <span class="text-muted small fw-bold text-uppercase me-2 d-none d-md-block">Type:</span>
                    <select id="typeFilter" class="form-select border-0 bg-white fw-medium py-1" style="min-width: 160px;"
                        onchange="filterTemplates()">
                        <option value="all">Show All</option>
                        <option value="medicine">Medicines Only</option>
                        <option value="test">Lab Tests Only</option>
                        <option value="mixed">Mixed Protocols</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- LIST CONTAINER --}}
    <div class="card overflow-hidden border-0 shadow-sm" id="templatesContainer" style="min-height: 200px;">
        @include('layouts.partials.prescriptions_template_list')
    </div>

    {{-- ================================================= --}}
    {{-- MODAL: CREATE / EDIT TEMPLATE --}}
    {{-- ================================================= --}}
    <div class="modal fade" id="createTemplateModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="modalTitle">Create New Protocol</h5>
                        <p class="text-muted small mb-0">Define a set of items to prescribe quickly.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <input type="hidden" id="editingTemplateId" value="">

                <div class="modal-body p-4 bg-light">
                    <div class="row g-4 h-100">

                        {{-- LEFT: CONFIGURATION --}}
                        <div class="col-lg-4 d-flex flex-column gap-3">
                            {{-- Name Input --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <label class="form-label fw-bold text-dark small text-uppercase">Template Name</label>
                                    <input type="text" id="templateName" class="form-control"
                                        placeholder="e.g., Seasonal Flu Kit">
                                </div>
                            </div>

                            {{-- Item Search --}}
                            <div class="card border-0 shadow-sm flex-grow-1">
                                <div class="card-body d-flex flex-column">
                                    <label class="form-label fw-bold text-dark small text-uppercase">Add Items to
                                        List</label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border me-0"><i
                                                    class="fa-solid fa-search"></i></span>
                                            <input type="text" id="catalogSearchInput" class="form-control bg-light border"
                                                placeholder="Search medicine or test..." autocomplete="off">
                                        </div>

                                        {{-- Dropdown Results --}}
                                        <div id="catalogResults"
                                            class="list-group position-absolute w-100 mt-2 shadow-lg border-0"
                                            style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;">
                                        </div>
                                    </div>

                                    <div class="mt-auto text-center p-3 text-muted opacity-50">
                                        <i class="fa-solid fa-arrow-pointer fa-2x mb-2"></i>
                                        <p class="small mb-0">Search and select items above to build your protocol.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: SELECTED ITEMS LIST --}}
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div
                                    class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Selected Items</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"
                                        id="itemCountBadge">0 items</span>
                                </div>

                                <div class="card-body p-0 position-relative">
                                    <div class="table-responsive" style="height: 400px; overflow-y: auto;">
                                        <table class="table align-middle mb-0 table-hover">
                                            <thead class="bg-light sticky-top" style="z-index: 5;">
                                                <tr>
                                                    <th class="ps-4 text-muted fw-bold small text-uppercase">Item Name</th>
                                                    <th width="45%" class="text-muted fw-bold small text-uppercase">
                                                        Instruction / Note</th>
                                                    <th width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="templateItemsBody">
                                                <tr id="emptyRow">
                                                    <td colspan="3" class="text-center align-middle" style="height: 300px;">
                                                        <div class="text-muted opacity-50">
                                                            <i class="fa-solid fa-basket-shopping fa-3x mb-3"></i>
                                                            <p>List is currently empty.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-top-0 pt-0 pe-4 pb-4 bg-light">
                    <button type="button" class="btn btn-white text-muted border shadow-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" onclick="saveTemplate()">
                        <i class="fa-solid fa-save me-2"></i>Save Protocol
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        let searchTimeout;
        let addedItems = [];
        const modalElement = document.getElementById('createTemplateModal');
        const modal = new bootstrap.Modal(modalElement);

        // 1. FILTER TEMPLATES
        function filterTemplates() {
            let q = document.getElementById('pageSearch').value;
            let type = document.getElementById('typeFilter').value;
            let container = document.getElementById('templatesContainer');

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                container.style.opacity = '0.5';
                fetch(`{{ route('prescriptions_templates.index') }}?q=${q}&type=${type}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                })
                    .then(res => res.text())
                    .then(html => {
                        container.innerHTML = html;
                        container.style.opacity = '1';
                    });
            }, 300);
        }

        // 2. CATALOG SEARCH LOGIC
        const cInput = document.getElementById('catalogSearchInput');
        const cResults = document.getElementById('catalogResults');

        if (cInput) {
            cInput.addEventListener('input', function () {
                let q = this.value;
                if (q.length < 1) { cResults.style.display = 'none'; return; }

                fetch(`{{ route('api.catalog.search') }}?q=${q}`)
                    .then(res => res.json())
                    .then(data => {
                        cResults.innerHTML = '';
                        cResults.style.display = 'block';

                        // DB Items
                        if (data.length > 0) {
                            data.forEach(item => {
                                let btn = document.createElement('button');
                                btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2';

                                let badgeClass = (item.type === 'medicine') ? 'bg-success' : 'bg-info';
                                let icon = (item.type === 'medicine') ? 'fa-pills' : 'fa-microscope';

                                btn.innerHTML = `
                                        <div class="icon-box ${badgeClass} bg-opacity-10 text-${badgeClass === 'bg-success' ? 'success' : 'info'} rounded-circle" style="width:32px; height:32px;">
                                            <i class="fa-solid ${icon} small"></i>
                                        </div>
                                        <div class="lh-sm">
                                            <div class="fw-bold text-dark">${item.name}</div>
                                            <small class="text-muted" style="font-size: 11px;">${item.strength || 'No strength'}</small>
                                        </div>
                                    `;
                                btn.onclick = () => {
                                    addItemToTemplate(item);
                                    cInput.value = '';
                                    cResults.style.display = 'none';
                                };
                                cResults.appendChild(btn);
                            });
                        }

                        // Custom Item Option
                        let customBtn = document.createElement('button');
                        customBtn.className = 'list-group-item list-group-item-action text-primary fw-bold py-2 bg-light';
                        customBtn.innerHTML = `<i class="fa-solid fa-plus me-2"></i> Add "${q}" as custom text`;
                        customBtn.onclick = () => {
                            addItemToTemplate({ id: null, name: q, form: null, default_quantity: null });
                            cInput.value = '';
                            cResults.style.display = 'none';
                        };
                        cResults.appendChild(customBtn);
                    });
            });

            // Close results when clicking outside
            document.addEventListener('click', function (e) {
                if (!cInput.contains(e.target) && !cResults.contains(e.target)) {
                    cResults.style.display = 'none';
                }
            });
        }

        // 3. ADD ITEM TO TABLE
        function addItemToTemplate(item, existingNote = null) {
            // Hide empty row
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.style.display = 'none';

            let note = '';
            if (existingNote) {
                note = existingNote;
            } else if (item.id && item.default_quantity) {
                // Smart Dosage logic
                let form = (item.form || '').toLowerCase();
                let qty = item.default_quantity;
                let unitText = item.form || 'unit(s)';

                if (form.includes('syrup') || form.includes('sirop')) unitText = 'Spoon(s)';
                else if (form.includes('spray')) unitText = 'Puff(s)';
                else if (form.includes('cream')) unitText = 'Application(s)';

                note = `${qty} ${unitText} x ${item.default_frequency}/day for ${item.default_duration} days`;
            }

            const newItem = {
                tempId: Date.now() + Math.random(),
                catalog_item_id: item.id || item.catalog_item_id || null,
                name: item.name,
                note: note
            };
            addedItems.push(newItem);

            const tbody = document.getElementById('templateItemsBody');
            const tr = document.createElement('tr');
            tr.id = `row-${newItem.tempId.toString().replace('.', '-')}`; // Safe ID

            let iconHtml = newItem.catalog_item_id
                ? '<i class="fa-solid fa-check-circle text-success me-2"></i>'
                : '<i class="fa-regular fa-keyboard text-muted me-2"></i>';

            tr.innerHTML = `
                    <td class="ps-4">
                        <div class="fw-medium text-dark">${iconHtml} ${newItem.name}</div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-light bg-light text-dark" 
                               placeholder="e.g. 1 pill after lunch"
                               value="${newItem.note}" 
                               onchange="updateItemNote(${newItem.tempId}, this.value)">
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm text-danger opacity-50 hover-opacity-100" onclick="removeItem(${newItem.tempId})">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                `;
            tbody.appendChild(tr);
            updateCount();
        }

        function updateItemNote(tempId, val) {
            let item = addedItems.find(i => i.tempId === tempId);
            if (item) item.note = val;
        }

        function removeItem(tempId) {
            let idx = addedItems.findIndex(i => i.tempId === tempId);
            if (idx > -1) addedItems.splice(idx, 1);

            let row = document.getElementById(`row-${tempId.toString().replace('.', '-')}`);
            if (row) row.remove();

            updateCount();
        }

        function updateCount() {
            let count = addedItems.length;
            document.getElementById('itemCountBadge').innerText = count + ' items';
            if (count === 0) {
                let emptyRow = document.getElementById('emptyRow');
                if (emptyRow) emptyRow.style.display = 'table-row';
            }
        }

        // 4. MODAL MANAGEMENT
        function editTemplate(id) {
            document.getElementById('editingTemplateId').value = id;
            document.getElementById('modalTitle').innerText = "Edit Protocol";
            document.getElementById('templateName').value = "Loading...";
            document.getElementById('templateItemsBody').innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center align-middle" style="height: 300px;"><div class="text-muted opacity-50"><i class="fa-solid fa-basket-shopping fa-3x mb-3"></i><p>List is currently empty.</p></div></td></tr>';
            addedItems = [];
            modal.show();

            fetch(`/prescriptions_templates/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('templateName').value = data.name;
                    data.items.forEach(item => {
                        addItemToTemplate(item, item.note);
                    });
                })
                .catch(err => {
                    alert('Error loading template');
                    console.error(err);
                });
        }

        modalElement.addEventListener('hidden.bs.modal', function () {
            document.getElementById('editingTemplateId').value = '';
            document.getElementById('modalTitle').innerText = "Create New Protocol";
            document.getElementById('templateName').value = '';
            document.getElementById('templateItemsBody').innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center align-middle" style="height: 300px;"><div class="text-muted opacity-50"><i class="fa-solid fa-basket-shopping fa-3x mb-3"></i><p>List is currently empty.</p></div></td></tr>';
            addedItems = [];
            updateCount();
        });

        // 5. SAVE
        function saveTemplate() {
            const id = document.getElementById('editingTemplateId').value;
            const name = document.getElementById('templateName').value;

            if (!name) { alert('Please enter a Template Name'); return; }
            if (addedItems.length === 0) { alert('Please add items to the protocol'); return; }

            const url = id ? `/prescriptions_templates/${id}` : "{{ route('prescriptions_templates.store') }}";
            const method = id ? "PUT" : "POST";

            fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ name: name, items: addedItems })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        location.reload(); // Simple reload to show new item
                    } else {
                        alert('Error saving template.');
                    }
                });
        }
    </script>

@endsection