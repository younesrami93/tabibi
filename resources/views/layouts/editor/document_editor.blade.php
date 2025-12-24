<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document->name }} - Editor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="{{ asset('css/editor.css') }}" rel="stylesheet">
</head>

<body class="d-flex vh-100 vw-100">

    <aside
        class="w-64px bg-theme-panel d-flex flex-column align-items-center py-3 border-end border-theme z-2 no-print flex-shrink-0 gap-3 shadow-sm">
        <div class="size-40px rounded d-flex align-items-center justify-content-center mb-2"
            style="background-color: var(--primary-blue);">
            <i class="fa-solid fa-notes-medical text-white fs-5"></i>
        </div>



        <button onclick="addElement('text')"
            class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition"
            title="Add Text">
            <i class="fa-solid fa-font"></i>
        </button>

        <button onclick="openMediaLibrary()"
            class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition"
            title="Add Image">
            <i class="fa-regular fa-image"></i>
        </button>
        <input type="file" id="imgUploadBtn" hidden accept="image/*">

        <div class="position-relative dropdown-parent">
            <button
                class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
                <i class="fa-solid fa-shapes"></i>
            </button>
            <div class="custom-dropdown-menu rounded shadow-sm overflow-hidden">
                <button onclick="addElement('shape', 'rect')" class="custom-dropdown-item"><i
                        class="fa-regular fa-square me-2"></i> Rectangle</button>
                <button onclick="addElement('shape', 'circle')" class="custom-dropdown-item"><i
                        class="fa-regular fa-circle me-2"></i> Circle</button>
                <button onclick="addElement('shape', 'line')" class="custom-dropdown-item"><i
                        class="fa-solid fa-minus me-2"></i> Separator</button>
            </div>
        </div>

        <button onclick="showVarModal()"
            class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition"
            title="Variables">
            <i class="fa-solid fa-code"></i>
        </button>

        <div class="border-top border-theme w-50 my-1"></div>

        <button onclick="document.getElementById('bgUploadBtn').click()"
            class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition"
            title="Background">
            <i class="fa-solid fa-paperclip"></i>
        </button>
        <input type="file" id="bgUploadBtn" hidden accept="image/*">

        <a href="{{ route('documents.index') }}"
            class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-danger hover-bg-theme rounded transition mt-auto"
            title="Exit">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </aside>

    <main class="flex-grow-1 position-relative overflow-auto bg-theme-base d-flex flex-column">

        <div
            class="h-48px border-bottom border-theme bg-theme-panel d-flex align-items-center justify-content-between px-4 no-print flex-shrink-0 shadow-sm z-1">

            <div class="d-flex align-items-center gap-3">
                <i class="fa-regular fa-file-lines text-primary"></i>
                <input type="text" id="docName"
                    class="form-control form-control-sm bg-transparent border-0 text-dark rounded-0 shadow-none fw-bold"
                    style="width: 250px; font-size: 1.1rem;" value="{{ $document->name }}"
                    placeholder="Document Name..." onchange="saveDoc()">

                <div class="vr h-50 my-auto text-secondary opacity-25"></div>

                <button class="btn btn-sm btn-light border border-theme text-secondary" onclick="openPageSetup()">
                    <i class="fa-solid fa-compass-drafting me-2"></i> Page Setup
                </button>

                <span id="saveStatus" class="small text-muted fst-italic ms-2"></span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center bg-theme-base border border-theme rounded me-2">
                    <button onclick="zoom(-0.1)" class="btn btn-sm text-secondary hover-bg-theme border-0 px-2"><i
                            class="fa-solid fa-minus"></i></button>
                    <span id="zoomLevel" class="small px-2 fw-medium text-muted">100%</span>
                    <button onclick="zoom(0.1)" class="btn btn-sm text-secondary hover-bg-theme border-0 px-2"><i
                            class="fa-solid fa-plus"></i></button>
                </div>
                <button onclick="saveDoc(true)" class="btn btn-primary btn-sm fw-medium px-4"
                    style="background-color: var(--primary-blue);">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Save
                </button>
                <button onclick="window.print()"
                    class="btn btn-light btn-sm fw-medium px-3 border border-theme text-secondary bg-white">Print</button>
            </div>
        </div>

        <div class="flex-grow-1 overflow-auto p-5 d-flex justify-content-center position-relative" id="workspace">
            <div id="canvas" class="a4-page"></div>
        </div>
    </main>

    <aside
        class="w-320px bg-theme-panel border-start border-theme d-flex flex-column no-print flex-shrink-0 z-2 shadow-sm">
        <div class="d-flex border-bottom border-theme">
            <button onclick="switchTab('props')" id="tab-props"
                class="flex-fill btn rounded-0 py-3 small fw-bold text-uppercase text-primary border-bottom border-primary border-2 bg-white">Properties</button>
            <button onclick="switchTab('layers')" id="tab-layers"
                class="flex-fill btn rounded-0 py-3 small fw-bold text-uppercase text-muted hover-bg-theme">Layers</button>
        </div>

        <div id="panel-props" class="d-flex flex-column h-100 overflow-y-auto">
            <div id="propEmpty" class="p-5 text-center text-muted mt-5 small">
                <div class="mb-3 p-3 rounded-circle bg-light d-inline-block text-secondary"><i
                        class="fa-solid fa-arrow-pointer fs-3"></i></div>
                <br><span class="fw-medium">No Element Selected</span><br><span class="opacity-75">Click an item to
                    edit.</span>
            </div>

            @include('layouts.editor.editor_properties_panel')
        </div>

        <div id="panel-layers" class="d-none flex-column h-100 overflow-y-auto pb-4 bg-white">
            <div id="layersList" class="d-flex flex-column"></div>
        </div>
    </aside>

    <div id="varModal"
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 z-3 d-none align-items-center justify-content-center"
        style="backdrop-filter: blur(2px);z-index: 10050;">
        <div class="bg-white rounded-3 shadow-lg border border-theme d-flex flex-column"
            style="width: 500px; max-height: 80vh;">

            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-code me-2 text-primary"></i> Insert Variable
                </h6>
                <button
                    onclick="document.getElementById('varModal').classList.add('d-none'); document.getElementById('varModal').classList.remove('d-flex')"
                    class="btn-close small"></button>
            </div>

            <div class="p-3 overflow-auto custom-scrollbar">

                <div class="mb-3">
                    <label class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Patient
                        Information</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="addVar('{patient_name}')" class="btn btn-xs btn-outline-secondary">Full
                            Name</button>
                        <button onclick="addVar('{patient_age}')" class="btn btn-xs btn-outline-secondary">Age</button>
                        <button onclick="addVar('{patient_gender}')"
                            class="btn btn-xs btn-outline-secondary">Gender</button>
                        <button onclick="addVar('{patient_dob}')" class="btn btn-xs btn-outline-secondary">Date of
                            Birth</button>
                        <button onclick="addVar('{patient_phone}')"
                            class="btn btn-xs btn-outline-secondary">Phone</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Appointment
                        Details</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="addVar('{appt_date}')" class="btn btn-xs btn-outline-secondary">Date</button>
                        <button onclick="addVar('{appt_time}')" class="btn btn-xs btn-outline-secondary">Time</button>
                        <button onclick="addVar('{appt_id}')" class="btn btn-xs btn-outline-secondary">Reference
                            ID</button>
                        <button onclick="addVar('{appt_type}')" class="btn btn-xs btn-outline-secondary">Type
                            (Consult/Control)</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Medical
                        Content</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="addVar('{notes}')" class="btn btn-xs btn-outline-primary fw-medium">Notes /
                            Diagnosis</button>
                        <button onclick="addVar('{prescription}')"
                            class="btn btn-xs btn-outline-primary fw-medium">Prescription List</button>
                        <button onclick="addVar('{services_list}')"
                            class="btn btn-xs btn-outline-primary fw-medium">Services Performed</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small fw-bold text-muted text-uppercase mb-2"
                        style="font-size: 11px;">Financials</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="addVar('{price_base}')" class="btn btn-xs btn-outline-secondary">Consultation
                            Price</button>
                        <button onclick="addVar('{price_total}')" class="btn btn-xs btn-outline-secondary">Total
                            Price</button>
                        <button onclick="addVar('{patient_balance}')" class="btn btn-xs btn-outline-danger">Current
                            Debt/Credit</button>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Clinic &
                        Doctor</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="addVar('{doctor_name}')" class="btn btn-xs btn-outline-secondary">Doctor
                            Name</button>
                        <button onclick="addVar('{clinic_name}')" class="btn btn-xs btn-outline-secondary">Clinic
                            Name</button>
                        <button onclick="addVar('{clinic_address}')" class="btn btn-xs btn-outline-secondary">Clinic
                            Address</button>
                        <button onclick="addVar('{date}')" class="btn btn-xs btn-outline-dark">Current Date</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="pageSetupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Page Setup</h6>
                    <button type="button" class="btn-close small" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Paper Size</label>
                        <select id="paperPreset" class="form-select form-select-sm" onchange="toggleCustomInputs()">
                            <option value="a4">A4 (Standard)</option>
                            <option value="a3">A3</option>
                            <option value="a5">A5</option>
                            <option value="letter">US Letter</option>
                            <option value="legal">US Legal</option>
                            <option value="tabloid">Tabloid</option>
                            <option value="b4">B4</option>
                            <option value="b5">B5</option>
                            <option value="custom">Custom Size...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold d-block">Orientation</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="orientation" id="orientPortrait"
                                autocomplete="off" checked onchange="toggleCustomInputs()">
                            <label class="btn btn-sm btn-outline-secondary" for="orientPortrait"><i
                                    class="fa-regular fa-file me-1"></i> Portrait</label>

                            <input type="radio" class="btn-check" name="orientation" id="orientLandscape"
                                autocomplete="off" onchange="toggleCustomInputs()">
                            <label class="btn btn-sm btn-outline-secondary" for="orientLandscape"><i
                                    class="fa-regular fa-file-image me-1"></i> Landscape</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Width (mm)</label>
                            <input type="number" id="paperWidth" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Height (mm)</label>
                            <input type="number" id="paperHeight" class="form-control form-control-sm" disabled>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="applyPageSetup()" class="btn btn-primary btn-sm px-4">Apply
                        Changes</button>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.editor.media_library_modal')


    <style>
        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.2rem;
        }
    </style>

    <script>
        window.docData = @json($editorData);
        window.saveUrl = "{{ route('documents.update_content', $document->id) }}";
    </script>
    <script src="{{ asset('js/document_editor.js') }}"></script>

    <script>
        const style = document.createElement('style');
        style.innerHTML = `.hidden { display: none !important; }`;
        document.head.appendChild(style);
    </script>


    <script src="{{ asset('js/media_manager.js') }}"></script>
</body>

</html>