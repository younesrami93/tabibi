@extends('layouts.admin')

@section('title', 'Prescription Templates')
@section('header', 'Manage Prescription')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fa-solid fa-clipboard-list text-primary me-2"></i> Prescription Templates</h4>
            <p class="text-muted small mb-0">Create standard protocols (e.g., "Flu Kit") to use instantly during
                appointments.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- TYPE FILTER --}}
            <select id="typeFilter" class="form-select shadow-sm" style="width: 150px;" onchange="filterTemplates()">
                <option value="all">All Types</option>
                <option value="medicine">Medicines</option>
                <option value="test">Lab Tests</option>
                <option value="mixed">Mixed</option>
            </select>

            {{-- SEARCH BAR --}}
            <div class="input-group shadow-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" id="pageSearch" class="form-control border-start-0 ps-0"
                    placeholder="Search templates..." onkeyup="filterTemplates()">
            </div>

            {{-- ADD BUTTON --}}
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                <i class="fa-solid fa-plus me-2"></i> New Template
            </button>
        </div>
    </div>

    {{-- LIST CONTAINER --}}
    <div class="card border-0 shadow-sm" id="templatesContainer">
        @include('layouts.partials.prescriptions_template_list')
    </div>

    {{-- ================================================= --}}
    {{-- MODAL: CREATE TEMPLATE --}}
    {{-- ================================================= --}}
    <div class="modal fade" id="createTemplateModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Create New Protocol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <input type="hidden" id="editingTemplateId" value="">

                <div class="modal-body bg-light">
                    <div class="row g-3 h-100">

                        {{-- LEFT: CATALOG SEARCH --}}
                        <div class="col-md-5 d-flex flex-column gap-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <label class="form-label fw-bold">Template Name</label>
                                    <input type="text" id="templateName" class="form-control mb-3"
                                        placeholder="e.g., Seasonal Flu Protocol">

                                    <label class="form-label fw-bold">Add Items</label>
                                    <div class="position-relative">
                                        <input type="text" id="catalogSearchInput" class="form-control"
                                            placeholder="Search medicine or test..." autocomplete="off">
                                        <div id="catalogResults" class="list-group position-absolute w-100 mt-1 shadow"
                                            style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-2">
                                        <i class="fa-solid fa-info-circle"></i> Click an item to add it to the list on the
                                        right.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: SELECTED ITEMS LIST --}}
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                                    <span>Items in this Template</span>
                                    <span class="badge bg-light text-dark border" id="itemCountBadge">0 items</span>
                                </div>
                                <div class="card-body p-0 d-flex flex-column">
                                    <div class="table-responsive flex-grow-1" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Item</th>
                                                    <th width="40%">Default Instruction</th>
                                                    <th width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="templateItemsBody">
                                                <tr id="emptyRow">
                                                    <td colspan="3" class="text-center py-5 text-muted">
                                                        <i
                                                            class="fa-solid fa-basket-shopping fa-2x mb-3 opacity-25"></i><br>
                                                        List is empty.
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success fw-bold px-4" onclick="saveTemplate()">
                        <i class="fa-solid fa-save me-2"></i> Save Template
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;
        let addedItems = []; // Changed const to let so we can reset it
        const modal = new bootstrap.Modal(document.getElementById('createTemplateModal'));
        // 1. DYNAMIC PAGE FILTER
        function filterTemplates() {
            let q = document.getElementById('pageSearch').value;
            let type = document.getElementById('typeFilter').value;

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('templatesContainer').style.opacity = '0.5';
                fetch(`{{ route('prescriptions_templates.index') }}?q=${q}&type=${type}`, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('templatesContainer').innerHTML = html;
                        document.getElementById('templatesContainer').style.opacity = '1';
                    });
            }, 300);
        }

        // 2. CATALOG SEARCH (Inside Modal)

        const cInput = document.getElementById('catalogSearchInput');
        const cResults = document.getElementById('catalogResults');

        if (cInput) {
            cInput.addEventListener('input', function () {
                let q = this.value;
                // Clear if empty
                if (q.length < 1) { cResults.style.display = 'none'; return; }

                fetch(`{{ route('api.catalog.search') }}?q=${q}`)
                    .then(res => res.json())
                    .then(data => {
                        cResults.innerHTML = '';
                        cResults.style.display = 'block';

                        // 1. Show Database Matches
                        if (data.length > 0) {
                            data.forEach(item => {
                                let btn = document.createElement('button');
                                btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                                let badge = (item.type === 'medicine')
                                    ? '<span class="badge bg-success-subtle text-success">Med</span>'
                                    : '<span class="badge bg-info-subtle text-info">Test</span>';

                                btn.innerHTML = `<div>${badge} <strong>${item.name}</strong> <small class="text-muted ms-1">${item.strength || ''}</small></div>`;
                                btn.onclick = () => addItemToTemplate(item);
                                cResults.appendChild(btn);
                            });
                        }

                        // 2. ALWAYS Show "Add Custom" Option
                        let customBtn = document.createElement('button');
                        customBtn.className = 'list-group-item list-group-item-action text-primary fw-bold';
                        customBtn.innerHTML = `<i class="fa-solid fa-pen-to-square me-2"></i> Add "${q}" as custom text`;

                        // Create a "Fake" item object for the custom text
                        customBtn.onclick = () => addItemToTemplate({
                            id: null,       // No DB ID
                            name: q,        // The text typed
                            form: null,     // No form
                            default_quantity: null
                        });

                        cResults.appendChild(customBtn);
                    });
            });
        }




        function addItemToTemplate(item, existingNote = null) {

            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }


            let note = '';

            if (existingNote) {
                // If editing, use the saved note
                note = existingNote;
            }
            else if (item.id && item.default_quantity) {
                // If new add, calculate smart dosage
                let form = (item.form || '').toLowerCase();
                let qty = item.default_quantity;
                let unitText = item.form || 'unit(s)';

                if (form.includes('syrup') || form.includes('sirop')) unitText = 'Spoon(s)';
                else if (form.includes('spray')) unitText = 'Puff(s)';
                else if (form.includes('drop')) unitText = 'Drop(s)';
                else if (form.includes('cream')) unitText = 'Application(s)';
                else if (form.includes('injection')) unitText = 'Injection(s)';

                note = `${qty} ${unitText} x ${item.default_frequency}/day for ${item.default_duration} days`;
            }

            const newItem = {
                tempId: Date.now() + Math.random(), // Randomize to avoid collision during fast load
                catalog_item_id: item.id || item.catalog_item_id || null, // Handle both structures
                name: item.name,
                note: note
            };
            addedItems.push(newItem);

            const tbody = document.getElementById('templateItemsBody');
            const tr = document.createElement('tr');
            tr.id = `row-${newItem.tempId}`; // Fix ID syntax

            let iconHtml = newItem.catalog_item_id
                ? '<i class="fa-solid fa-check-circle text-success me-2"></i>'
                : '<i class="fa-solid fa-keyboard text-muted me-2"></i>';

            tr.innerHTML = `
                                                                    <td class="ps-3"><div class="fw-bold">${iconHtml} ${newItem.name}</div></td>
                                                                    <td>
                                                                        <input type="text" class="form-control form-control-sm" 
                                                                               value="${newItem.note}" 
                                                                               onchange="updateItemNote(${newItem.tempId}, this.value)">
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-sm text-danger" onclick="removeItem(${newItem.tempId})"><i class="fa-solid fa-trash"></i></button>
                                                                    </td>
                                                                `;
            tbody.appendChild(tr);
            updateCount();
        }


        function editTemplate(id) {
            // 1. Reset Modal State
            document.getElementById('editingTemplateId').value = id;
            document.getElementById('modalTitle').innerText = "Edit Protocol";
            document.getElementById('templateName').value = "Loading...";
            document.getElementById('templateItemsBody').innerHTML = '';
            addedItems = []; // Reset array

            // 2. Open Modal
            modal.show();

            // 3. Fetch Data
            fetch(`/prescriptions_templates/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('templateName').value = data.name;

                    // 4. Loop items and add them to the table
                    // The saved JSON structure: { catalog_item_id: 1, name: "Doli", note: "..." }
                    data.items.forEach(item => {
                        addItemToTemplate(item, item.note);
                    });
                })
                .catch(err => {
                    alert('Error loading template');
                    console.error(err);
                });
        }


        document.getElementById('createTemplateModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('editingTemplateId').value = '';
            document.getElementById('modalTitle').innerText = "Create New Protocol";
            document.getElementById('templateName').value = '';
            document.getElementById('templateItemsBody').innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center py-5 text-muted">List is empty.</td></tr>';
            addedItems = [];
            updateCount();
        });

        function removeItem(tempId) {
            let idx = addedItems.findIndex(i => i.tempId === tempId); // Fix variable declaration
            if (idx > -1) addedItems.splice(idx, 1);
            document.getElementById(`row-${tempId}`).remove(); // Fix syntax
            updateCount();
        }

        function updateItemNote(tempId, val) {
            let item = addedItems.find(i => i.tempId === tempId);
            if (item) item.note = val;
        }

        function updateCount() {
            let count = addedItems.length;
            document.getElementById('itemCountBadge').innerText = count + ' items';
            if (count === 0) {
                // Re-show empty row if list cleared
                if (!document.getElementById('emptyRow')) {
                    document.getElementById('templateItemsBody').innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center py-5 text-muted">List is empty.</td></tr>';
                }
            }
        }

        function updateItemNote(tempId, val) {
            const item = addedItems.find(i => i.tempId === tempId);
            if (item) item.note = val;
        }

        function updateCount() {
            document.getElementById('itemCountBadge').innerText = addedItems.length + ' items';
        }

        // 4. SAVE TEMPLATE

        function saveTemplate() {
            const id = document.getElementById('editingTemplateId').value;
            const name = document.getElementById('templateName').value;

            if (!name) { alert('Please enter a Template Name'); return; }
            if (addedItems.length === 0) { alert('Please add items'); return; }

            // Determine URL and Method
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
                        alert(id ? 'Template Updated!' : 'Template Created!');
                        location.reload();
                    } else {
                        alert('Error saving template.');
                    }
                });
        }


    </script>

@endsection