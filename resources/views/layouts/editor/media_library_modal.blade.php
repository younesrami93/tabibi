<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-secondary">
                    <i class="fa-regular fa-images me-2"></i> Media Library
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">

                <div class="upload-zone mb-4 p-4 text-center border-2 border-dashed rounded-3 bg-light position-relative"
                    id="dropZone" style="border-color: #cbd5e1; cursor: pointer; transition: all 0.2s;">
                    <div class="d-flex flex-column align-items-center justify-content-center gap-2 pointer-events-none">
                        <div
                            class="size-40px bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center text-primary mb-1">
                            <i class="fa-solid fa-cloud-arrow-up fs-5"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0">Click or Drag image to upload</h6>
                        <small class="text-muted">Supports JPG, PNG (Max 5MB)</small>
                    </div>
                    <input type="file" id="mediaUploadInput"
                        class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                        accept="image/png, image/jpeg, image/jpg">
                </div>

                <div id="mediaLoader" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2">Loading library...</p>
                </div>

                <div id="mediaEmpty" class="text-center py-5 d-none">
                    <i class="fa-regular fa-folder-open fs-1 text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">No images found.</p>
                </div>

                <div id="mediaGrid" class="row g-3 overflow-auto custom-scrollbar" style="max-height: 400px;">
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .upload-zone:hover,
    .upload-zone.dragover {
        background-color: #e0f2fe !important;
        /* Light blue */
        border-color: #0ea5e9 !important;
    }

    .media-item {
        transition: transform 0.2s;
        cursor: pointer;
    }

    .media-item:hover {
        transform: translateY(-2px);
    }

    .media-item:hover .btn-delete {
        opacity: 1 !important;
    }

    /* Aspect Ratio Box for images */
    .ratio-box {
        position: relative;
        width: 100%;
        padding-top: 100%;
        /* 1:1 Aspect Ratio */
        overflow: hidden;
        background: #f8fafc;
        border-radius: 8px;
    }

    .ratio-box img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
    }
</style>