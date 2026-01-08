// public/js/book_modal.js
// Handles Patient Search and modal functionality for booking appointments
// This file is independent and can be used in any page that includes the book modal

document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // BOOK APPOINTMENT MODAL FUNCTIONALITY
    // ==========================================

    const modalSearchInput = document.getElementById('patientSearchInput');
    const resultsBox = document.getElementById('searchResults');
    const patientIdInput = document.getElementById('patientIdInput');
    const newPatientForm = document.getElementById('newPatientForm');

    if (modalSearchInput) {
        modalSearchInput.addEventListener('input', function () {
            const query = this.value;
            if (query.length < 2) {
                if (resultsBox) resultsBox.style.display = 'none';
                return;
            }

            // Use the route defined in Blade, or fallback
            const searchUrl = (typeof patientSearchRoute !== 'undefined') ? patientSearchRoute : '/api/patients/search';

            fetch(`${searchUrl}?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    if (resultsBox) {
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
                        } else {
                            resultsBox.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Patient search error:', error);
                    if (resultsBox) resultsBox.style.display = 'none';
                });
        });

        // Close search results if clicking outside
        document.addEventListener('click', function (e) {
            if (modalSearchInput && resultsBox && !modalSearchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });
    }
});

// Global functions for patient selection (accessible from HTML onclick)
function selectPatient(patient) {
    const patientIdInput = document.getElementById('patientIdInput');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const selectedPatientName = document.getElementById('selectedPatientName');
    const patientSearchGroup = document.getElementById('patientSearchGroup');
    const resultsBox = document.getElementById('searchResults');
    const newPatientForm = document.getElementById('newPatientForm');

    if (patientIdInput) patientIdInput.value = patient.id;
    if (selectedPatientName) selectedPatientName.innerText = `${patient.first_name} ${patient.last_name}`;
    if (selectedPatientDisplay) selectedPatientDisplay.classList.remove('d-none');
    if (patientSearchGroup) patientSearchGroup.classList.add('d-none');
    if (resultsBox) resultsBox.style.display = 'none';
    if (newPatientForm) newPatientForm.classList.add('d-none');
}

function resetPatientSelection() {
    const patientIdInput = document.getElementById('patientIdInput');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const patientSearchGroup = document.getElementById('patientSearchGroup');
    const modalSearchInput = document.getElementById('patientSearchInput');

    if (patientIdInput) patientIdInput.value = '';
    if (selectedPatientDisplay) selectedPatientDisplay.classList.add('d-none');
    if (patientSearchGroup) patientSearchGroup.classList.remove('d-none');
    if (modalSearchInput) {
        modalSearchInput.value = '';
        modalSearchInput.focus();
    }
}

function toggleNewPatientMode() {
    const newPatientForm = document.getElementById('newPatientForm');
    const patientIdInput = document.getElementById('patientIdInput');
    const patientSearchGroup = document.getElementById('patientSearchGroup');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');

    if (!newPatientForm) {
        return;
    }

    const isHidden = newPatientForm.classList.contains('d-none');

    if (isHidden) {
        newPatientForm.classList.remove('d-none');
        if (patientIdInput) patientIdInput.value = '';
        if (patientSearchGroup) patientSearchGroup.classList.add('d-none');
        if (selectedPatientDisplay) selectedPatientDisplay.classList.add('d-none');
    } else {
        newPatientForm.classList.add('d-none');
        if (patientSearchGroup) patientSearchGroup.classList.remove('d-none');
    }
}