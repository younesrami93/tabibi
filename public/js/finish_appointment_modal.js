// public/js/finish_appointment_modal.js

// ==========================================
// 2. FINISH APPOINTMENT MODAL LOGIC
// ==========================================


const manualPaymentEdits = {};

// --- A. Service Management (Select & Custom) ---

/**
 * Adds a Standard Catalog Service from the Dropdown
 */
function addServiceRow(apptId) {
    const select = document.getElementById(`newServiceSelect-${apptId}`);
    const tbody = document.getElementById(`serviceRows-${apptId}`);
    const noServicesMsg = document.getElementById(`no-services-msg-${apptId}`);
    const selectedOption = select.options[select.selectedIndex];

    if (!select.value) return;

    // Hide "No services" message if visible
    if (noServicesMsg) noServicesMsg.style.display = 'none';

    const id = select.value;
    const name = selectedOption.getAttribute('data-name');
    const price = selectedOption.getAttribute('data-price');
    const index = Date.now(); // Unique ID for this row

    const row = `
        <tr class="service-row fade-in">
            <td class="ps-3 border-0">
                <input type="hidden" name="services[${index}][id]" value="${id}">
                <input type="text" class="form-control form-control-sm bg-white border-0 fw-bold" value="${name}" readonly>
            </td>
            <td class="border-0" width="140">
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" class="form-control text-end price-input border-0 bg-light fw-bold" 
                           name="services[${index}][price]" value="${price}" 
                           oninput="calculateTotal(${apptId})">
                    <span class="input-group-text border-0 bg-light text-muted small">DH</span>
                </div>
            </td>
            <td class="text-center border-0" width="40">
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

/**
 * Adds a Custom Service Row (Editable Name & Price)
 */
function addCustomServiceRow(apptId) {
    const tbody = document.getElementById(`serviceRows-${apptId}`);
    const noServicesMsg = document.getElementById(`no-services-msg-${apptId}`);
    const index = Date.now();

    if (noServicesMsg) noServicesMsg.style.display = 'none';

    const row = `
        <tr class="service-row fade-in">
            <td class="ps-3 border-0">
                <div class="input-group input-group-sm">
                     <span class="input-group-text bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning-emphasis"><i class="fa-solid fa-pen"></i></span>
                     <input type="text" name="services[${index}][custom_name]" 
                       class="form-control border-warning border-opacity-25" 
                       placeholder="Enter service name..." required>
                </div>
            </td>
            <td class="border-0" width="120">
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" class="form-control text-end price-input border-warning border-opacity-25 fw-bold" 
                           name="services[${index}][price]" value="0.00" 
                           oninput="calculateTotal(${apptId})">
                    <span class="input-group-text border-warning border-opacity-25 text-muted small">DH</span>
                </div>
            </td>
            <td class="text-center border-0" width="40">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this, ${apptId})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);
}

function removeRow(btn, apptId) {
    const tbody = btn.closest('tbody');
    btn.closest('tr').remove();
    calculateTotal(apptId);

    // Show "No services" message if table is empty
    if (tbody.children.length === 0) {
        const msg = document.getElementById(`no-services-msg-${apptId}`);
        if (msg) msg.style.display = 'block';
    }
}

// --- B. Financial Calculations ---

function calculateTotal(apptId) {
    const baseInput = document.getElementById(`basePrice-${apptId}`);
    const basePrice = parseFloat(baseInput.value) || 0;

    const serviceInputs = document.querySelectorAll(`#serviceRows-${apptId} .price-input`);
    let servicesTotal = 0;
    serviceInputs.forEach(input => {
        servicesTotal += parseFloat(input.value) || 0;
    });

    // Update Services Subtotal
    const servicesSumDisplay = document.getElementById(`servicesSum-${apptId}`);
    if (servicesSumDisplay) servicesSumDisplay.innerText = servicesTotal.toFixed(2);

    // Update Grand Total
    const grandTotal = basePrice + servicesTotal;
    const totalDisplay = document.getElementById(`totalDisplay-${apptId}`);
    if (totalDisplay) totalDisplay.innerText = grandTotal.toFixed(2);

    // Update "Amount Received" if user hasn't manually edited it
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
// 3. PRESCRIPTION LOGIC (Multi-Block + Templates)
// ==========================================

/**
 * Adds a new Prescription Block (Card style)
 */
function addNewPrescriptionBlock(apptId) {
    const container = document.getElementById(`prescriptionsContainer-${apptId}`);
    const index = Date.now();

    const blockHtml = `
        <div class="card overflow-visible border rounded-3 shadow-sm prescription-block fade-in" id="pBlock-${apptId}-${index}">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-prescription text-success"></i>
                    <input type="text" name="prescriptions[${index}][title]" 
                           class="form-control form-control-sm border-0 fw-bold p-0 shadow-none" 
                           value="Prescription" style="width: 150px;" placeholder="Title">
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm border-0 bg-light text-muted small" style="width: 140px;" onchange="loadTemplateToBlock(this, ${apptId}, ${index})">
                        <option value="">Load Template...</option>
                        ${getTemplateOptions()}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removePrescriptionBlock(${apptId}, ${index})">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 border-0 text-muted x-small text-uppercase fw-bold" width="50%">Medicine</th>
                                <th class="border-0 text-muted x-small text-uppercase fw-bold">Instructions</th>
                                <th class="border-0" width="30"></th>
                            </tr>
                        </thead>
                        <tbody id="pItems-${apptId}-${index}">
                            </tbody>
                    </table>
                </div>
                <div class="p-2 border-top bg-light">
                    <button type="button" class="btn btn-sm btn-light text-primary w-100 fw-bold border-dashed" onclick="addItemToBlock(${apptId}, ${index})">
                        <i class="fa-solid fa-plus me-1"></i> Add Medicine / Test
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', blockHtml);
    // Add first item automatically
    addItemToBlock(apptId, index);
}

function removePrescriptionBlock(apptId, index) {
    const block = document.getElementById(`pBlock-${apptId}-${index}`);
    if (block) block.remove();
}

/**
 * Adds a Medicine Row with Live Search & Autocomplete
 */
function addItemToBlock(apptId, blockIndex, name = '', note = '', catalogId = '') {
    const tbody = document.getElementById(`pItems-${apptId}-${blockIndex}`);
    const itemIndex = Date.now() + Math.random().toString().slice(2, 5);

    const row = document.createElement('tr');

    // 1. Name Input with Live Search Logic (Hidden ID + Visible Input + Results Box)
    const tdName = document.createElement('td');
    tdName.className = 'ps-3 position-relative';

    const hiddenId = document.createElement('input');
    hiddenId.type = 'hidden';
    hiddenId.name = `prescriptions[${blockIndex}][items][${itemIndex}][catalog_item_id]`;
    hiddenId.value = catalogId;

    const inputName = document.createElement('input');
    inputName.type = 'text';
    inputName.name = `prescriptions[${blockIndex}][items][${itemIndex}][name]`;
    inputName.className = 'form-control form-control-sm fw-bold border-0 shadow-none';
    inputName.value = name;
    inputName.placeholder = "Type medicine name...";
    inputName.autocomplete = "off";

    const resultsDiv = document.createElement('div');
    resultsDiv.className = 'list-group position-absolute w-100 shadow-lg start-0';
    resultsDiv.style.zIndex = '1050';
    resultsDiv.style.display = 'none';
    resultsDiv.style.maxHeight = '200px';
    resultsDiv.style.overflowY = 'auto';
    resultsDiv.style.top = '100%';

    // Activate live search
    setupCatalogAutocomplete(inputName, resultsDiv, hiddenId);

    tdName.appendChild(hiddenId);
    tdName.appendChild(inputName);
    tdName.appendChild(resultsDiv);

    // 2. Note Input
    const tdNote = document.createElement('td');
    const inputNote = document.createElement('input');
    inputNote.type = 'text';
    inputNote.name = `prescriptions[${blockIndex}][items][${itemIndex}][note]`;
    inputNote.className = 'form-control form-control-sm border-0 shadow-none text-muted fst-italic';
    inputNote.placeholder = "Instructions...";
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

/**
 * Loads a template via AJAX and populates the block
 */
function loadTemplateToBlock(select, apptId, blockIndex) {
    const templateId = select.value;
    if (!templateId) return;

    select.value = ""; // Reset dropdown

    fetch(`/prescriptions_templates/${templateId}`)
        .then(res => res.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                // Clear existing empty row if it's the only one and empty
                const tbody = document.getElementById(`pItems-${apptId}-${blockIndex}`);
                const rows = tbody.querySelectorAll('tr');
                if (rows.length === 1) {
                    const inputs = rows[0].querySelectorAll('input[type="text"]');
                    if (inputs[0].value === '' && inputs[1].value === '') {
                        rows[0].remove();
                    }
                }

                // Add template items
                data.items.forEach(item => {
                    addItemToBlock(apptId, blockIndex, item.name, item.note || '', item.catalog_item_id || '');
                });
            }
        })
        .catch(err => console.error(err));
}

function getTemplateOptions() {
    // Helper to reuse options from the main list (rendered by Blade in the first block)
    const existingSelect = document.querySelector(`select[onchange^="loadTemplateToBlock"]`);
    return existingSelect ? existingSelect.innerHTML : '<option value="">Error loading templates</option>';
}

/**
 * Live Search Logic for Catalog Items
 */
function setupCatalogAutocomplete(input, resultsBox, hiddenIdInput) {
    input.addEventListener('input', function () {
        const q = this.value;
        if (q.length < 2) { resultsBox.style.display = 'none'; return; }

        // Ensure route is defined (fallback if not)
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

                            // Smart Note Logic (Populate dosage if available)
                            let smartNote = '';
                            if (item.default_quantity) {
                                let form = (item.form || '').toLowerCase();
                                let unitText = item.form || 'unit(s)';
                                if (form.includes('syrup') || form.includes('sirop')) unitText = 'Spoon(s)';
                                else if (form.includes('spray')) unitText = 'Puff(s)';
                                smartNote = `${item.default_quantity} ${unitText} x ${item.default_frequency}/day for ${item.default_duration} days`;
                            }

                            // Find the note input in the same row
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

    // Hide results on blur
    input.addEventListener('blur', () => { setTimeout(() => resultsBox.style.display = 'none', 200); });
}