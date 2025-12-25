document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('main-search-input');
    const resultsBox = document.getElementById('global-search-results');
    let debounceTimer;

    // 1. LISTEN FOR TYPING
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 1) {
            resultsBox.style.display = 'none';
            return;
        }

        // Wait 300ms after user stops typing to save requests
        debounceTimer = setTimeout(() => fetchResults(query), 300);
    });



    // 2. FETCH DATA
    function fetchResults(query) {

        const searchUrl = searchInput.getAttribute('data-route');

        fetch(`${searchUrl}?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                renderDropdown(data);
            })
            .catch(err => console.error('Search error:', err));
    }

    // 3. RENDER UI
    function renderDropdown(items) {
        if (items.length === 0) {
            resultsBox.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    <i class="fa-regular fa-face-frown mb-1"></i><br>
                    No matching records found.
                </div>`;
        } else {
            let html = '<div class="list-group list-group-flush">';

            items.forEach(item => {
                html += `
                    <a href="${item.url}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-bottom-0">
                        <div class="avatar-circle bg-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            ${item.icon}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark fw-bold text-truncate" style="font-size: 0.9rem;">${item.title}</h6>
                                <span class="badge bg-light text-secondary border ms-2">${item.meta}</span>
                            </div>
                            <small class="text-muted text-truncate d-block" style="font-size: 0.8rem;">${item.subtitle}</small>
                        </div>
                    </a>
                `;
            });

            html += '</div>';
            resultsBox.innerHTML = html;
        }
        resultsBox.style.display = 'block';
    }

    // 4. CLOSE ON CLICK OUTSIDE
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });


});



function openFullModal(url) {
    // 1. Show Loading Indicator
    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch the data.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading(); // The spinning animation
        }
    });

    // 2. Fetch Data
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Server Error');
            return response.text();
        })
        .then(html => {
            // 3. Close Loading
            Swal.close();

            // 4. Create Container & Inject HTML
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            const modalEl = wrapper.firstElementChild;

            // Validate Content
            if (!modalEl || !modalEl.classList.contains('modal')) {
                throw new Error('Invalid Content');
            }

            // 5. Append & Show
            document.body.appendChild(modalEl);
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();

            // 6. Destruction Logic
            modalEl.addEventListener('hidden.bs.modal', function () {
                bsModal.dispose();
                modalEl.remove();
            });
        })
        .catch(err => {
            console.error(err);
            // 7. Show Error Alert
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Unable to load details. Please check your connection.',
                confirmButtonColor: '#0d6efd'
            });
        });
}