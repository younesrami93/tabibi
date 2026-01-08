// public/js/appointments.js

// ==========================================
// 1. STATE MANAGEMENT & INITIALIZATION
// ==========================================
const urlParams = new URLSearchParams(window.location.search);

// Initialize State object from URL parameters (or defaults)
let state = {
    quick_filter: urlParams.get('quick_filter') || 'today_active',
    date_from:    urlParams.get('date_from')    || '',
    date_to:      urlParams.get('date_to')      || '',
    statuses:     urlParams.get('statuses')     || '',
    search:       urlParams.get('search')       || '',
    page:         urlParams.get('page')         || 1
};

// ==========================================
// 2. CORE FETCH FUNCTION (AJAX)
// ==========================================
function loadAppointments(page = 1, updateUrl = true) {
    state.page = page;

    const listBody = document.getElementById('appointmentListBody');
    const paginationContainer = document.getElementById('paginationContainer');

    // 1. Prepare Query Parameters for the Backend
    const params = new URLSearchParams({
        page: state.page,
        quick_filter: state.quick_filter,
        search: state.search
    });

    // Only append advanced fields if they are active
    if (state.date_from) params.append('date_from', state.date_from);
    if (state.date_to)   params.append('date_to', state.date_to);
    if (state.statuses)  params.append('statuses', state.statuses);

    // 2. Update Browser URL (so you can copy-paste the link or refresh)
    if (updateUrl) {
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({ path: newUrl }, '', newUrl);
    }

    // 3. Visual Feedback (Loading)
    if(listBody) listBody.style.opacity = '0.5';

    // 4. Fetch Data
    // Note: 'fetchUrl' is defined in the Blade file (e.g., /appointments/fetch)
    fetch(`${fetchUrl}?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            // Update Table Rows
            if(listBody) listBody.innerHTML = data.html;
            
            // Update Pagination Links
            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination;
                attachPaginationListeners(); // Re-bind click events to new links
            }
            
            if(listBody) listBody.style.opacity = '1';
        })
        .catch(err => {
            console.error('Error fetching appointments:', err);
            if(listBody) listBody.style.opacity = '1';
        });
}


// ==========================================
// 3. FILTER LOGIC (Tabs & Advanced)
// ==========================================

/**
 * Handle "Quick Tab" clicks (e.g., "Today's Queue", "All")
 */
function applyPreset(presetName) {
    state.quick_filter = presetName;
    state.page = 1;

    // Reset Advanced Fields internally when switching main tabs
    state.date_from = '';
    state.date_to = '';
    state.statuses = '';
    
    // UI: Update Tab Styles
    document.querySelectorAll('.filter-preset').forEach(btn => {
        if (btn.dataset.preset === presetName) {
            btn.classList.add('active-preset', 'btn-primary', 'text-white', 'shadow-sm');
            btn.classList.remove('text-muted', 'bg-light');
        } else {
            btn.classList.remove('active-preset', 'btn-primary', 'text-white', 'shadow-sm');
            btn.classList.add('text-muted');
        }
    });

    // Special Logic: If the user clicked "Finished" tab, we can pre-fill the status
    if (presetName === 'finished') {
        state.statuses = 'finished';
    } else if (presetName === 'cancelled') {
        state.statuses = 'cancelled';
    }

    // Clear Advanced Inputs visually (to match internal reset)
    const dateFromEl = document.getElementById('filterDateFrom');
    const dateToEl = document.getElementById('filterDateTo');
    const statusEl = document.getElementById('filterStatus');

    if(dateFromEl) dateFromEl.value = '';
    if(dateToEl) dateToEl.value = '';
    if(statusEl) statusEl.selectedIndex = -1;

    loadAppointments(1);
}

/**
 * Handle "Apply Filters" button in the Advanced section
 */
function applyAdvancedFilters() {
    state.date_from = document.getElementById('filterDateFrom').value;
    state.date_to = document.getElementById('filterDateTo').value;
    
    // Get Multi-Select Values
    const statusSelect = document.getElementById('filterStatus');
    const selected = Array.from(statusSelect.selectedOptions).map(option => option.value);
    state.statuses = selected.join(',');

    // Mark mode as 'custom' so backend ignores the "Today" default
    state.quick_filter = 'custom';
    state.page = 1;
    
    // Remove "Active" style from all Quick Tabs
    document.querySelectorAll('.filter-preset').forEach(btn => {
        btn.classList.remove('active-preset', 'btn-primary', 'text-white', 'shadow-sm');
        btn.classList.add('text-muted');
    });

    loadAppointments(1);
}

/**
 * Handle "Reset" button
 */
function resetFilters() {
    // Reset State
    state = {
        quick_filter: 'today_active',
        date_from: '',
        date_to: '',
        statuses: '',
        search: '',
        page: 1
    };

    // Reset UI Inputs
    document.getElementById('ajaxSearchInput').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterStatus').selectedIndex = -1;

    // Apply the default preset (this will trigger the fetch)
    applyPreset('today_active');
}


// ==========================================
// 4. EVENT LISTENERS
// ==========================================

let searchTimeout;
const searchInputEl = document.getElementById('ajaxSearchInput');

if (searchInputEl) {
    searchInputEl.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        state.search = this.value;
        searchTimeout = setTimeout(() => {
            loadAppointments(1);
        }, 400); // Debounce search by 400ms
    });
}

// Function to intercept Pagination Clicks
function attachPaginationListeners() {
    const links = document.querySelectorAll('#paginationContainer a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Extract page number securely from the URL
            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;
            loadAppointments(page);
        });
    });
}

// Browser Back/Forward Button Support
window.addEventListener('popstate', function(event) {
    window.location.reload(); // Simple reload to ensure state is correct
});

// Initial Load Logic
document.addEventListener('DOMContentLoaded', () => {
    // 1. If URL has advanced filters, expand the menu automatically
    if (state.date_from || state.statuses || state.quick_filter === 'custom') {
        const advancedFiltersEl = document.getElementById('advancedFilters');
        if(advancedFiltersEl) {
            new bootstrap.Collapse(advancedFiltersEl, { toggle: true });
        }
    }
    
    // 2. Highlight the correct tab based on URL
    if (state.quick_filter && state.quick_filter !== 'custom') {
        const btn = document.querySelector(`.filter-preset[data-preset="${state.quick_filter}"]`);
        if(btn) {
            // Manually apply styles (don't click() to avoid double fetch)
            btn.classList.add('active-preset', 'btn-primary', 'text-white', 'shadow-sm');
            btn.classList.remove('text-muted', 'bg-light');
        }
    }

    // 3. Attach listeners to the initial pagination links (if any exist)
    attachPaginationListeners();
});


// ==========================================
// 5. UTILITIES
// ==========================================

function confirmCancel(event) {
    event.preventDefault();
    const form = event.target;

    Swal.fire({
        title: 'Cancel Appointment?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Cancel it',
        cancelButtonText: 'Keep Appointment'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}