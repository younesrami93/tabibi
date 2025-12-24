document.addEventListener('DOMContentLoaded', () => {
    
    const API_URL = '/api/images';
    const grid = document.getElementById('mediaGrid');
    const loader = document.getElementById('mediaLoader');
    const empty = document.getElementById('mediaEmpty');
    const uploadInput = document.getElementById('mediaUploadInput');
    const dropZone = document.getElementById('dropZone');
    let isLoaded = false;

    // --- 1. OPEN MODAL & FETCH ---
    window.openMediaLibrary = () => {
        // Ensure Bootstrap is available
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap 5 is required for the modal');
            return;
        }
        const modalEl = document.getElementById('mediaModal');
        if(!modalEl) { console.error('#mediaModal not found'); return; }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        if (!isLoaded) fetchImages();
    };

    // --- 2. FETCH IMAGES ---
    function fetchImages() {
        if(!loader || !grid) return;
        loader.classList.remove('d-none');
        grid.innerHTML = '';
        empty.classList.add('d-none');

        fetch(API_URL)
            .then(res => res.json())
            .then(images => {
                isLoaded = true;
                loader.classList.add('d-none');
                if (images.length === 0) {
                    empty.classList.remove('d-none');
                } else {
                    images.forEach(img => renderImage(img));
                }
            })
            .catch(err => console.error('Error loading images:', err));
    }

    // --- 3. RENDER SINGLE IMAGE ---
    function renderImage(img) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4 col-lg-3 fade-in';
        col.id = `img-card-${img.id}`;

        col.innerHTML = `
            <div class="media-item position-relative group">
                <div class="ratio-box border border-light shadow-sm bg-white" onclick="selectImage('${img.path}')">
                    <img src="/storage/${img.path}" alt="${img.filename}">
                </div>
                <div class="position-absolute top-0 end-0 p-1">
                    <button onclick="deleteImage(event, ${img.id})" class="btn-delete btn btn-sm btn-danger py-0 px-2 rounded opacity-0 transition shadow-sm" title="Delete">
                        &times;
                    </button>
                </div>
                <div class="small text-truncate mt-1 text-center text-muted" style="font-size: 10px;">
                    ${img.filename}
                </div>
            </div>
        `;
        grid.prepend(col);
    }

    // --- 4. INSERT INTO EDITOR ---
    window.selectImage = (path) => {
        const fullUrl = `/storage/${path}`;
        
        // Call the function in document_editor.js
        if (typeof window.addElement === 'function') {
            window.addElement('image', fullUrl);
        }

        const el = document.getElementById('mediaModal');
        const modal = bootstrap.Modal.getInstance(el);
        modal.hide();
    };

    // --- 5. UPLOAD LOGIC ---
    if(uploadInput) uploadInput.addEventListener('change', (e) => handleUpload(e.target.files[0]));
    
    if(dropZone) {
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) handleUpload(e.dataTransfer.files[0]);
        });
    }

    function handleUpload(file) {
        if (!file) return;

        const originalText = dropZone.querySelector('h6').innerText;
        dropZone.querySelector('h6').innerText = 'Uploading...';
        dropZone.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('image', file);

        fetch(API_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Upload failed');
            return res.json();
        })
        .then(newImage => {
            renderImage(newImage);
            empty.classList.add('d-none');
        })
        .catch(err => alert('Upload failed. Check file size.'))
        .finally(() => {
            dropZone.querySelector('h6').innerText = originalText;
            dropZone.style.opacity = '1';
            uploadInput.value = '';
        });
    }

    // --- 6. DELETE LOGIC ---
    window.deleteImage = (e, id) => {
        e.stopPropagation();
        if (!confirm('Delete this image?')) return;

        const card = document.getElementById(`img-card-${id}`);
        card.style.opacity = '0.5';

        fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => {
            if (res.ok) {
                card.remove();
                if (grid.children.length === 0) empty.classList.remove('d-none');
            } else {
                alert('Error deleting image.');
                card.style.opacity = '1';
            }
        });
    };
});