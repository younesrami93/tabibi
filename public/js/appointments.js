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
// 4. NEW APPOINTMENT (Patient Search)
// ==========================================
// This section handles the "Book Appointment" modal logic

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