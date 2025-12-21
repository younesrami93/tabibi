// public/js/appointments.js

// ==========================================
// 1. QUEUE FILTER (Client-Side)
// ==========================================
function filterAppointments() {
    const input = document.getElementById('pageSearch').value.toLowerCase();
    // CHANGED: Look for table rows instead of cards
    const rows = document.getElementsByClassName('appointment-row');

    for (let i = 0; i < rows.length; i++) {
        const name = rows[i].getAttribute('data-patient-name');
        // Simple visibility toggle for table rows
        if (name && name.includes(input)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// ==========================================
// 2. FINISH MODAL & SMART PAYMENTS
// ==========================================
const manualPaymentEdits = {};

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
            <td>
                <span class="fw-medium text-dark">${name}</span>
                <input type="hidden" name="services[${index}][id]" value="${id}">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-end price-input" 
                    name="services[${index}][price]" value="${price}" 
                    oninput="calculateTotal(${apptId})">
            </td>
            <td>
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
// 3. NEW APPOINTMENT (Patient Search)
// ==========================================
const searchInput = document.getElementById('patientSearchInput');
const resultsBox = document.getElementById('searchResults');
const patientIdInput = document.getElementById('patientIdInput');
const newPatientForm = document.getElementById('newPatientForm');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value;
        if (query.length < 2) { resultsBox.style.display = 'none'; return; }

        // FIXED: Use the global variable defined in Blade instead of {{ route }}
        const url = `${patientSearchRoute}?q=${query}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                resultsBox.innerHTML = '';
                if (data.length > 0) {
                    resultsBox.style.display = 'block';
                    data.forEach(p => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action text-start';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>${p.first_name} ${p.last_name}</strong>
                                <small class='text-muted'>${p.phone || 'No Phone'}</small>
                            </div>`;
                        item.onclick = () => selectPatient(p);
                        resultsBox.appendChild(item);
                    });
                } else {
                    resultsBox.style.display = 'none';
                }
            })
            .catch(err => console.error(err));
    });

    // Close search results when clicking outside
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