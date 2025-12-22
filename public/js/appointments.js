// public/js/appointments.js

// ==========================================
// 1. QUEUE FILTER (Client-Side)
// ==========================================
function filterAppointments() {
    const input = document.getElementById('pageSearch').value.toLowerCase();
    const rows = document.getElementsByClassName('appointment-row');

    for (let i = 0; i < rows.length; i++) {
        const name = rows[i].getAttribute('data-patient-name');
        if (name && name.includes(input)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// ==========================================
// 2. FINISH MODAL (Services & Payments)
// ==========================================
const manualPaymentEdits = {};

// A. Add Standard Catalog Service
function addServiceRow(apptId) {
    const select = document.getElementById(`newServiceSelect-${apptId}`);
    const tbody = document.getElementById(`serviceRows-${apptId}`);
    const selectedOption = select.options[select.selectedIndex];

    if (!select.value) return;

    const id = select.value;
    const name = selectedOption.getAttribute('data-name');
    const price = selectedOption.getAttribute('data-price');
    const index = Date.now();

    const row = `
        <tr class="service-row">
            <td class="ps-3">
                <input type="hidden" name="services[${index}][id]" value="${id}">
                <input type="text" class="form-control form-control-sm bg-light border-0 fw-bold" value="${name}" readonly>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-end price-input" 
                       name="services[${index}][price]" value="${price}" 
                       oninput="calculateTotal(${apptId})">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this, ${apptId})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);
    select.value = "";
    calculateTotal(apptId);
}

// B. Add Custom Service (Editable Name)
function addCustomServiceRow(apptId) {
    const tbody = document.getElementById(`serviceRows-${apptId}`);
    const index = Date.now(); // Unique ID

    const row = `
        <tr class="service-row">
            <td class="ps-3">
                {{-- No ID for custom services --}}
                <input type="text" name="services[${index}][custom_name]" 
                       class="form-control form-control-sm fw-bold" 
                       placeholder="Enter service name..." required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-end price-input" 
                       name="services[${index}][price]" value="0.00" 
                       oninput="calculateTotal(${apptId})">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this, ${apptId})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);
}

function removeRow(btn, apptId) {
    btn.closest('tr').remove();
    calculateTotal(apptId);
}

function calculateTotal(apptId) {
    const baseInput = document.getElementById(`basePrice-${apptId}`);
    const basePrice = parseFloat(baseInput.value) || 0;

    const serviceInputs = document.querySelectorAll(`#serviceRows-${apptId} .price-input`);
    let servicesTotal = 0;
    serviceInputs.forEach(input => {
        servicesTotal += parseFloat(input.value) || 0;
    });

    const servicesSumDisplay = document.getElementById(`servicesSum-${apptId}`);
    if (servicesSumDisplay) servicesSumDisplay.innerText = servicesTotal.toFixed(2);

    const grandTotal = basePrice + servicesTotal;
    const totalDisplay = document.getElementById(`totalDisplay-${apptId}`);
    if (totalDisplay) totalDisplay.innerText = grandTotal.toFixed(2);

    if (!manualPaymentEdits[apptId]) {
        const paidInput = document.getElementById(`paidAmount-${apptId}`);
        if (paidInput) paidInput.value = grandTotal.toFixed(2);
    }
}

function attachPaymentListeners() {
    const paidInputs = document.querySelectorAll('[id^="paidAmount-"]');
    paidInputs.forEach(input => {
        input.addEventListener('input', function () {
            const id = this.id.replace('paidAmount-', '');
            manualPaymentEdits[id] = true;
        });
    });
}

// ==========================================
// 3. MULTIPLE PRESCRIPTIONS LOGIC
// ==========================================

// Add a new Prescription Block (Card)
function addNewPrescriptionBlock(apptId) {
    const container = document.getElementById(`prescriptionsContainer-${apptId}`);
    const index = Date.now(); // Unique ID for the block

    // We clone the structure from the Blade but using JS template literal
    const blockHtml = `
        <div class="card border shadow-sm prescription-block" id="pBlock-${apptId}-${index}" data-index="${index}">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small"><i class="fa-solid fa-file-medical"></i></span>
                    <input type="text" name="prescriptions[${index}][title]" 
                           class="form-control form-control-sm fw-bold border-0 bg-transparent p-0" 
                           value="Prescription" 
                           style="width: 200px;" placeholder="Prescription Title">
                </div>
                <div class="d-flex gap-2">
                    {{-- Template Loader --}}
                    <select class="form-select form-select-sm" style="width: 160px;" onchange="loadTemplateToBlock(this, ${apptId}, ${index})">
                        <option value="">Load Template...</option>
                        ${getTemplateOptions(apptId)}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removePrescriptionBlock(${apptId}, ${index})">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" width="45%">Item Name</th>
                                <th>Dosage / Instructions</th>
                                <th width="30"></th>
                            </tr>
                        </thead>
                        <tbody id="pItems-${apptId}-${index}">
                            {{-- Items go here --}}
                        </tbody>
                    </table>
                </div>
                <div class="p-2 text-center border-top bg-light">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="addItemToBlock(${apptId}, ${index})">
                        <i class="fa-solid fa-plus-circle me-1"></i> Add Medicine / Test
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', blockHtml);
}

function removePrescriptionBlock(apptId, index) {
    const block = document.getElementById(`pBlock-${apptId}-${index}`);
    if (block) block.remove();
}

function addItemToBlock(apptId, blockIndex, name = '', note = '', catalogId = '') {
    const tbody = document.getElementById(`pItems-${apptId}-${blockIndex}`);
    const itemIndex = Date.now() + Math.random().toString().slice(2, 5);

    const row = document.createElement('tr');

    // 1. Name Input (Live Search)
    const tdName = document.createElement('td');
    tdName.className = 'ps-3 position-relative';

    const hiddenId = document.createElement('input');
    hiddenId.type = 'hidden';
    hiddenId.name = `prescriptions[${blockIndex}][items][${itemIndex}][catalog_item_id]`;
    hiddenId.value = catalogId;

    const inputName = document.createElement('input');
    inputName.type = 'text';
    inputName.name = `prescriptions[${blockIndex}][items][${itemIndex}][name]`;
    inputName.className = 'form-control form-control-sm fw-bold';
    inputName.value = name;
    inputName.placeholder = "Type medicine name...";
    inputName.autocomplete = "off";

    // Results container
    const resultsDiv = document.createElement('div');
    resultsDiv.className = 'list-group position-absolute w-100 shadow-lg start-0';
    resultsDiv.style.zIndex = '1050';
    resultsDiv.style.display = 'none';
    resultsDiv.style.maxHeight = '200px';
    resultsDiv.style.overflowY = 'auto';
    resultsDiv.style.top = '100%';

    setupCatalogAutocomplete(inputName, resultsDiv, hiddenId);

    tdName.appendChild(hiddenId);
    tdName.appendChild(inputName);
    tdName.appendChild(resultsDiv);

    // 2. Note Input
    const tdNote = document.createElement('td');
    const inputNote = document.createElement('input');
    inputNote.type = 'text';
    inputNote.name = `prescriptions[${blockIndex}][items][${itemIndex}][note]`;
    inputNote.className = 'form-control form-control-sm';
    inputNote.value = note;
    tdNote.appendChild(inputNote);

    // 3. Delete Action
    const tdAction = document.createElement('td');
    tdAction.className = 'text-center';
    tdAction.innerHTML = `<button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-xmark"></i></button>`;

    row.appendChild(tdName);
    row.appendChild(tdNote);
    row.appendChild(tdAction);
    tbody.appendChild(row);
}

function loadTemplateToBlock(select, apptId, blockIndex) {
    const templateId = select.value;
    if (!templateId) return;

    // Reset select
    select.value = "";

    fetch(`/prescriptions_templates/${templateId}`)
        .then(res => res.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    addItemToBlock(apptId, blockIndex, item.name, item.note || '', item.catalog_item_id || '');
                });
            }
        })
        .catch(err => console.error(err));
}

// Helper: Reuse options from the first select on the page (since we can't easily pass PHP array to JS string)
function getTemplateOptions(apptId) {
    // Find any existing template select on the page to copy options from
    // We look for the one in the first block or the main one if we had one
    const existingSelect = document.querySelector(`select[onchange^="loadTemplateToBlock"]`);
    if (existingSelect) return existingSelect.innerHTML;
    return '<option value="">Error loading templates</option>';
}

// --- Live Search Logic (Reused) ---
function setupCatalogAutocomplete(input, resultsBox, hiddenIdInput) {
    input.addEventListener('input', function () {
        const q = this.value;
        if (q.length < 2) { resultsBox.style.display = 'none'; return; }

        const searchUrl = (typeof catalogSearchRoute !== 'undefined') ? catalogSearchRoute : '/api/catalog/search';

        fetch(`${searchUrl}?q=${q}`)
            .then(res => res.json())
            .then(data => {
                resultsBox.innerHTML = '';
                if (data.length > 0) {
                    resultsBox.style.display = 'block';
                    data.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action py-2 text-start small border-0 border-bottom';

                        const badge = item.type === 'medicine'
                            ? '<span class="badge bg-success bg-opacity-10 text-success me-2">Med</span>'
                            : '<span class="badge bg-info bg-opacity-10 text-info me-2">Test</span>';

                        btn.innerHTML = `${badge} <strong>${item.name}</strong> <span class='text-muted ms-1'>${item.strength || ''}</span>`;

                        btn.onclick = () => {
                            input.value = item.name;
                            if (hiddenIdInput) hiddenIdInput.value = item.id;
                            resultsBox.style.display = 'none';

                            // Smart Note Logic
                            let smartNote = '';
                            if (item.default_quantity) {
                                let form = (item.form || '').toLowerCase();
                                let qty = item.default_quantity;
                                let unitText = item.form || 'unit(s)';
                                if (form.includes('syrup') || form.includes('sirop')) unitText = 'Spoon(s)';
                                else if (form.includes('spray')) unitText = 'Puff(s)';
                                smartNote = `${qty} ${unitText} x ${item.default_frequency}/day for ${item.default_duration} days`;
                            }

                            // Find note input in same row
                            const row = input.closest('tr');
                            const noteInput = row.querySelector('input[name*="[note]"]');
                            if (noteInput && smartNote) noteInput.value = smartNote;
                        };
                        resultsBox.appendChild(btn);
                    });
                } else {
                    resultsBox.style.display = 'none';
                }
            });
    });

    input.addEventListener('blur', () => {
        setTimeout(() => resultsBox.style.display = 'none', 200);
    });
}

// ==========================================
// 4. NEW APPOINTMENT (Patient Search)
// ==========================================
// ... (Keeping your existing logic untouched for safety) ...
const searchInput = document.getElementById('patientSearchInput');
const resultsBox = document.getElementById('searchResults');
const patientIdInput = document.getElementById('patientIdInput');
const newPatientForm = document.getElementById('newPatientForm');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value;
        if (query.length < 2) { resultsBox.style.display = 'none'; return; }
        const searchUrl = (typeof patientSearchRoute !== 'undefined') ? patientSearchRoute : '/api/patients/search';
        fetch(`${searchUrl}?q=${query}`)
            .then(res => res.json())
            .then(data => {
                resultsBox.innerHTML = '';
                if (data.length > 0) {
                    resultsBox.style.display = 'block';
                    data.forEach(p => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action text-start';
                        item.innerHTML = `<strong>${p.first_name} ${p.last_name}</strong><small class='text-muted ms-2'>${p.phone || ''}</small>`;
                        item.onclick = () => selectPatient(p);
                        resultsBox.appendChild(item);
                    });
                } else { resultsBox.style.display = 'none'; }
            });
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
}

function selectPatient(patient) {
    patientIdInput.value = patient.id;
    document.getElementById('selectedPatientName').innerText = `${patient.first_name} ${patient.last_name}`;
    document.getElementById('selectedPatientDisplay').classList.remove('d-none');
    document.getElementById('patientSearchGroup').classList.add('d-none');
    resultsBox.style.display = 'none';
    if (newPatientForm) newPatientForm.classList.add('d-none');
}

function resetPatientSelection() {
    patientIdInput.value = '';
    document.getElementById('selectedPatientDisplay').classList.add('d-none');
    document.getElementById('patientSearchGroup').classList.remove('d-none');
    searchInput.value = '';
    searchInput.focus();
}

function toggleNewPatientMode() {
    if (!newPatientForm) return;
    const isHidden = newPatientForm.classList.contains('d-none');
    if (isHidden) {
        newPatientForm.classList.remove('d-none');
        patientIdInput.value = '';
        document.getElementById('patientSearchGroup').classList.add('d-none');
        document.getElementById('selectedPatientDisplay').classList.add('d-none');
    } else {
        newPatientForm.classList.add('d-none');
        document.getElementById('patientSearchGroup').classList.remove('d-none');
    }
}