<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediDesign Pro (Light Theme)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/editor.css') }}" rel="stylesheet">


</head>

<body class="d-flex vh-100 vw-100">

    <aside class="w-64px bg-theme-panel d-flex flex-column align-items-center py-3 border-end border-theme z-2 no-print flex-shrink-0 gap-3 shadow-sm">
        
        <div class="size-40px rounded d-flex align-items-center justify-content-center mb-2" style="background-color: var(--primary-blue);">
            <i class="fa-solid fa-notes-medical text-white fs-5"></i>
        </div>

        <button onclick="addElement('text')" class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
            <i class="fa-solid fa-font"></i>
            <span class="tool-tooltip">Add Text</span>
        </button>

        <button onclick="document.getElementById('imgUploadBtn').click()" class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
            <i class="fa-regular fa-image"></i>
            <span class="tool-tooltip">Add Image</span>
        </button>
        <input type="file" id="imgUploadBtn" hidden accept="image/*">

        <div class="position-relative dropdown-parent">
            <button class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
                <i class="fa-solid fa-shapes"></i>
            </button>
            <div class="custom-dropdown-menu rounded shadow-sm overflow-hidden">
                <button onclick="addElement('shape', 'rect')" class="custom-dropdown-item"><i class="fa-regular fa-square me-2"></i> Rectangle</button>
                <button onclick="addElement('shape', 'circle')" class="custom-dropdown-item"><i class="fa-regular fa-circle me-2"></i> Circle</button>
                <button onclick="addElement('shape', 'line')" class="custom-dropdown-item"><i class="fa-solid fa-minus me-2"></i> Separator</button>
            </div>
        </div>

        <button onclick="showVarModal()" class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
            <i class="fa-solid fa-code"></i>
            <span class="tool-tooltip">Insert Variable</span>
        </button>

        <div class="border-top border-theme w-50 my-1"></div>

        <button onclick="setMode('bg')" id="bgToolBtn" class="btn tool-btn size-40px p-0 d-flex align-items-center justify-content-center text-secondary hover-bg-theme rounded transition">
            <i class="fa-solid fa-paperclip"></i>
            <span class="tool-tooltip">Set Background</span>
        </button>
        <input type="file" id="bgUploadBtn" hidden accept="image/*">
    </aside>

    <main class="flex-grow-1 position-relative overflow-auto bg-theme-base d-flex flex-column">

        <div class="h-48px border-bottom border-theme bg-theme-panel d-flex align-items-center justify-content-between px-4 no-print flex-shrink-0 shadow-sm z-1">
            <div class="d-flex align-items-center gap-3">
                <select id="docSelector" class="form-select form-select-sm border-0 py-1 fw-bold text-secondary" style="width: auto; background-color: transparent;" onchange="switchDoc(this.value)">
                    <option value="new">+ New Document</option>
                </select>
                <input type="text" id="docName" class="form-control form-control-sm bg-transparent border-0 text-dark rounded-0 shadow-none fw-bold" 
                    style="width: 250px; font-size: 1.1rem;" placeholder="Document Name..." onchange="saveDoc()">
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center bg-theme-base border border-theme rounded me-2">
                    <button onclick="zoom(-0.1)" class="btn btn-sm text-secondary hover-bg-theme border-0 px-2"><i class="fa-solid fa-minus"></i></button>
                    <span id="zoomLevel" class="small px-2 fw-medium text-muted">100%</span>
                    <button onclick="zoom(0.1)" class="btn btn-sm text-secondary hover-bg-theme border-0 px-2"><i class="fa-solid fa-plus"></i></button>
                </div>
                <button onclick="saveDoc()" class="btn btn-primary btn-sm fw-medium px-4" style="background-color: var(--primary-blue);">Save Changes</button>
                <button onclick="window.print()" class="btn btn-light btn-sm fw-medium px-3 border border-theme text-secondary bg-white">Print</button>
            </div>
        </div>

        <div class="flex-grow-1 overflow-auto p-5 d-flex justify-content-center position-relative" id="workspace">
            <div id="canvas" class="a4-page">
                </div>
        </div>
    </main>

    <aside class="w-320px bg-theme-panel border-start border-theme d-flex flex-column no-print flex-shrink-0 z-2 shadow-sm">

        <div class="d-flex border-bottom border-theme">
            <button onclick="switchTab('props')" id="tab-props" class="flex-fill btn rounded-0 py-3 small fw-bold text-uppercase text-primary border-bottom border-primary border-2 bg-white" style="color: var(--primary-blue) !important;">Properties</button>
            <button onclick="switchTab('layers')" id="tab-layers" class="flex-fill btn rounded-0 py-3 small fw-bold text-uppercase text-muted hover-bg-theme">Layers</button>
        </div>

        <div id="panel-props" class="d-flex flex-column h-100 overflow-y-auto">
            <div id="propEmpty" class="p-5 text-center text-muted mt-5 small">
                <div class="mb-3 p-3 rounded-circle bg-light d-inline-block text-secondary">
                    <i class="fa-solid fa-arrow-pointer fs-3"></i>
                </div>
                <br>
                <span class="fw-medium">No Element Selected</span><br>
                <span class="opacity-75">Click an item to edit its properties.</span>
            </div>

            <div id="propPanel" class="d-none flex-column">

                <div class="p-3 border-bottom border-theme d-flex gap-2 bg-light bg-opacity-50">
                    <button id="btnLock" onclick="toggleLock()" class="btn btn-sm bg-white border border-theme text-secondary flex-fill shadow-sm" title="Lock/Unlock">
                        <i class="fa-solid fa-lock-open"></i> Lock
                    </button>
                    <button onclick="duplicateSelected()" class="btn btn-sm bg-white border border-theme text-secondary flex-fill shadow-sm" title="Clone">
                        <i class="fa-solid fa-copy"></i> Clone
                    </button>
                    <button onclick="deleteSelected()" class="btn btn-sm btn-white border border-danger text-danger flex-fill shadow-sm" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>

                <div class="p-3 border-bottom border-theme row g-2 mx-0">
                    <div class="col-6 ps-0">
                        <button onclick="alignSelected('centerH')" class="btn btn-sm w-100 bg-white border-theme text-secondary hover-bg-theme">
                            <i class="fa-solid fa-arrows-left-right-to-line"></i> Center H
                        </button>
                    </div>
                    <div class="col-6 pe-0">
                        <button onclick="alignSelected('centerV')" class="btn btn-sm w-100 bg-white border-theme text-secondary hover-bg-theme">
                            Center V <i class="fa-solid fa-arrows-up-down"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3 border-bottom border-theme">
                    <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Geometry</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">X Position</label>
                            <input type="number" id="propX" class="form-control form-control-sm form-control-dark" onchange="updateSelected('pos')">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Y Position</label>
                            <input type="number" id="propY" class="form-control form-control-sm form-control-dark" onchange="updateSelected('pos')">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Width</label>
                            <input type="number" id="propW" class="form-control form-control-sm form-control-dark" onchange="updateSelected('dim')">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Height</label>
                            <input type="number" id="propH" class="form-control form-control-sm form-control-dark" onchange="updateSelected('dim')">
                        </div>
                    </div>
                    <div id="aspectRatioCtrl" class="d-none mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="propKeepRatio" onchange="updateSelected('dim')">
                            <label class="form-check-label small text-muted" for="propKeepRatio">Keep Aspect Ratio</label>
                        </div>
                    </div>
                </div>

                <div id="propText" class="p-3 border-bottom border-theme">
                    <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Typography</h6>

                    <div class="mb-2">
                         <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Font Family</label>
                        <select id="propFontFamily" class="form-select form-select-sm form-select-dark" onchange="updateSelected('style')">
                            <option value="sans-serif">System Sans</option>
                            <option value="'Inter', sans-serif">Inter</option>
                            <option value="'Times New Roman', serif">Serif</option>
                            <option value="'Courier New', monospace">Monospace</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <div class="w-25">
                            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Size</label>
                            <input type="number" id="propSize" class="form-control form-control-sm form-control-dark" onchange="updateSelected('style')">
                        </div>
                        <div class="flex-fill">
                             <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Weight</label>
                            <select id="propWeight" class="form-select form-select-sm form-select-dark" onchange="updateSelected('style')">
                                <option value="normal">Regular</option>
                                <option value="bold">Bold</option>
                                <option value="600">Semi-Bold</option>
                                <option value="300">Light</option>
                            </select>
                        </div>
                    </div>

                    <div class="btn-group w-100 mb-3" role="group">
                        <button onclick="toggleStyle('italic')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i class="fa-solid fa-italic"></i></button>
                        <button onclick="toggleStyle('underline')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i class="fa-solid fa-underline"></i></button>
                        <button onclick="toggleStyle('left')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i class="fa-solid fa-align-left"></i></button>
                        <button onclick="toggleStyle('center')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i class="fa-solid fa-align-center"></i></button>
                        <button onclick="toggleStyle('right')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i class="fa-solid fa-align-right"></i></button>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2 rounded border border-theme bg-light">
                        <label class="small text-muted text-uppercase mb-0" style="font-size: 10px;">Text Color</label>
                        <input type="color" id="propColor" class="form-control form-control-color border-0 p-0 bg-transparent" onchange="updateSelected('style')" title="Choose color">
                    </div>
                </div>

                <div class="p-3 border-bottom border-theme">
                    <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Appearance</h6>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 10px;">
                            <span>Opacity</span>
                            <span id="opacityVal">100%</span>
                        </div>
                        <input type="range" id="propOpacity" min="0" max="1" step="0.1" class="form-range" oninput="updateSelected('appearance')">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="small text-muted text-uppercase" style="font-size: 10px;">Fill Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input m-0" type="checkbox" id="hasBg" onchange="updateSelected('appearance')">
                            <input type="color" id="propBgColor" class="form-control form-control-color p-0 border-0 bg-transparent" onchange="updateSelected('appearance')">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="small text-muted text-uppercase" style="font-size: 10px;">Border</label>
                        <div class="d-flex align-items-center gap-1">
                            <select id="propBorderStyle" class="form-select form-select-sm py-0 px-1" style="height: 24px; font-size: 10px;" onchange="updateSelected('appearance')">
                                <option value="solid">Solid</option>
                                <option value="dashed">Dashed</option>
                                <option value="dotted">Dotted</option>
                            </select>
                            <input type="number" id="propBorderWidth" class="form-control form-control-sm p-0 text-center" style="width: 40px; height: 24px;" placeholder="0" onchange="updateSelected('appearance')">
                            <input type="color" id="propBorderColor" class="form-control form-control-color bg-transparent border-0 p-0" style="width: 24px; height: 24px;" onchange="updateSelected('appearance')">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <label class="small text-muted text-uppercase" style="font-size: 10px;">Corner Radius</label>
                        <input type="number" id="propRadius" class="form-control form-control-sm p-0 text-center" style="width: 40px; height: 24px;" placeholder="0" onchange="updateSelected('appearance')">
                    </div>
                </div>

            </div>
        </div>

        <div id="panel-layers" class="d-none flex-column h-100 overflow-y-auto pb-4 bg-white">
            <div id="layersList" class="d-flex flex-column">
                </div>
        </div>

    </aside>

    <div id="varModal" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 z-3 d-none align-items-center justify-content-center" style="backdrop-filter: blur(2px);">
        <div class="bg-white p-4 rounded-3 shadow-lg border border-theme" style="width: 350px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">Insert Variable</h5>
                <button onclick="document.getElementById('varModal').classList.add('d-none'); document.getElementById('varModal').classList.remove('d-flex')" class="btn-close"></button>
            </div>
            <p class="small text-muted mb-3">Variables are placeholders that will be replaced with patient data.</p>
            <div class="d-grid gap-2">
                <button onclick="addVar('{patient_name}')" class="btn btn-sm btn-light border text-start fw-medium text-primary hover-bg-theme px-3 py-2">
                    <i class="fa-solid fa-user me-2 opacity-50"></i> Patient Name
                </button>
                <button onclick="addVar('{appointment_date}')" class="btn btn-sm btn-light border text-start fw-medium text-primary hover-bg-theme px-3 py-2">
                    <i class="fa-regular fa-calendar me-2 opacity-50"></i> Appointment Date
                </button>
                <button onclick="addVar('{doctor_name}')" class="btn btn-sm btn-light border text-start fw-medium text-primary hover-bg-theme px-3 py-2">
                    <i class="fa-solid fa-user-doctor me-2 opacity-50"></i> Doctor Name
                </button>
                <button onclick="addVar('{medicines}')" class="btn btn-sm btn-light border text-start fw-medium text-primary hover-bg-theme px-3 py-2">
                    <i class="fa-solid fa-pills me-2 opacity-50"></i> Medicines List
                </button>
            </div>
        </div>
    </div>
</body>

<script src="{{ asset('js/document_editor.js') }}"></script>
<script>
    function showVarModal() {
        const m = document.getElementById('varModal');
        m.classList.remove('d-none');
        m.classList.add('d-flex');
    }

    // --- HELPER FOR JS COMPATIBILITY ---
    // Since the original JS uses "hidden" class (Tailwind) and Bootstrap uses "d-none",
    // and creates elements with "text-light", we add a style bridge.
    const style = document.createElement('style');
    style.innerHTML = `
        .hidden { display: none !important; }
    `;
    document.head.appendChild(style);
</script>

</html>