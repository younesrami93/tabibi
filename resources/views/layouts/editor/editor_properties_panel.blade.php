<div id="propPanel" class="d-none flex-column">

    <div class="p-3 border-bottom border-theme d-flex gap-2 bg-light bg-opacity-50">
        <button id="btnLock" onclick="toggleLock()"
            class="btn btn-sm bg-white border border-theme text-secondary flex-fill shadow-sm" title="Lock/Unlock">
            <i class="fa-solid fa-lock-open"></i> Lock
        </button>
        <button onclick="duplicateSelected()"
            class="btn btn-sm bg-white border border-theme text-secondary flex-fill shadow-sm" title="Clone">
            <i class="fa-solid fa-copy"></i> Clone
        </button>
        <button onclick="deleteSelected()"
            class="btn btn-sm btn-white border border-danger text-danger flex-fill shadow-sm" title="Delete">
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>

    <div class="p-3 border-bottom border-theme row g-2 mx-0">
        <div class="col-6 ps-0">
            <button onclick="alignSelected('centerH')"
                class="btn btn-sm w-100 bg-white border-theme text-secondary hover-bg-theme">
                <i class="fa-solid fa-arrows-left-right-to-line"></i> Center H
            </button>
        </div>
        <div class="col-6 pe-0">
            <button onclick="alignSelected('centerV')"
                class="btn btn-sm w-100 bg-white border-theme text-secondary hover-bg-theme">
                Center V <i class="fa-solid fa-arrows-up-down"></i>
            </button>
        </div>
    </div>

    <div id="propImage" class="p-3 border-bottom border-theme d-none">
        <h6 class="small text-uppercase fw-bold text-muted mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
            Image Source</h6>
        <button onclick="changeImage()" class="btn btn-sm btn-outline-primary w-100 bg-white">
            <i class="fa-regular fa-images me-2"></i> Replace Image
        </button>
    </div>

    <div class="p-3 border-bottom border-theme">
        <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
            Geometry</h6>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">X Position</label>
                <input type="number" id="propX" class="form-control form-control-sm form-control-dark"
                    onchange="updateSelected('pos')">
            </div>
            <div class="col-6">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Y Position</label>
                <input type="number" id="propY" class="form-control form-control-sm form-control-dark"
                    onchange="updateSelected('pos')">
            </div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Width</label>
                <input type="number" id="propW" class="form-control form-control-sm form-control-dark"
                    onchange="updateSelected('dim')">
            </div>
            <div class="col-6">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Height</label>
                <input type="number" id="propH" class="form-control form-control-sm form-control-dark"
                    onchange="updateSelected('dim')">
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
        <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
            Typography</h6>

        <div class="mb-2">
            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Font Family</label>
            <select id="propFontFamily" class="form-select form-select-sm form-select-dark"
                onchange="updateSelected('style')">
                <option value="sans-serif">System Sans</option>
                <option value="'Inter', sans-serif">Inter</option>
                <option value="'Times New Roman', serif">Serif</option>
                <option value="'Courier New', monospace">Monospace</option>
            </select>
        </div>

        <div class="d-flex gap-2 mb-2">
            <div class="w-25">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Size</label>
                <input type="number" id="propSize" class="form-control form-control-sm form-control-dark"
                    onchange="updateSelected('style')">
            </div>
            <div class="flex-fill">
                <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Weight</label>
                <select id="propWeight" class="form-select form-select-sm form-select-dark"
                    onchange="updateSelected('style')">
                    <option value="normal">Regular</option>
                    <option value="bold">Bold</option>
                    <option value="600">Semi-Bold</option>
                    <option value="300">Light</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="small text-muted text-uppercase mb-1" style="font-size: 9px;">Line Height</label>
            <input type="number" id="propLineHeight" step="0.1" min="0.5" max="3.0" value="1.5"
                class="form-control form-control-sm form-control-dark" onchange="updateSelected('style')">
        </div>

        <div class="btn-group w-100 mb-3" role="group">
            <button onclick="toggleStyle('italic')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i
                    class="fa-solid fa-italic"></i></button>
            <button onclick="toggleStyle('underline')"
                class="btn btn-sm btn-outline-secondary border-theme text-dark"><i
                    class="fa-solid fa-underline"></i></button>
            <button onclick="toggleStyle('left')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i
                    class="fa-solid fa-align-left"></i></button>
            <button onclick="toggleStyle('center')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i
                    class="fa-solid fa-align-center"></i></button>
            <button onclick="toggleStyle('right')" class="btn btn-sm btn-outline-secondary border-theme text-dark"><i
                    class="fa-solid fa-align-right"></i></button>
        </div>

        <div class="d-flex align-items-center justify-content-between p-2 rounded border border-theme bg-light">
            <label class="small text-muted text-uppercase mb-0" style="font-size: 10px;">Text Color</label>
            <input type="color" id="propColor" class="form-control form-control-color border-0 p-0 bg-transparent"
                onchange="updateSelected('style')" title="Choose color">
        </div>
    </div>

    <div class="p-3 border-bottom border-theme">
        <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
            Appearance</h6>

        <div class="mb-3">
            <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 10px;">
                <span>Opacity</span>
                <span id="opacityVal">100%</span>
            </div>
            <input type="range" id="propOpacity" min="0" max="1" step="0.1" class="form-range"
                oninput="updateSelected('appearance')">
        </div>

        <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="small text-muted text-uppercase" style="font-size: 10px;">Fill Color</label>
            <div class="d-flex align-items-center gap-2">
                <input class="form-check-input m-0" type="checkbox" id="hasBg" onchange="updateSelected('appearance')">
                <input type="color" id="propBgColor" class="form-control form-control-color p-0 border-0 bg-transparent"
                    onchange="updateSelected('appearance')">
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="small text-muted text-uppercase" style="font-size: 10px;">Border</label>
            <div class="d-flex align-items-center gap-1">
                <select id="propBorderStyle" class="form-select form-select-sm py-0 px-1"
                    style="height: 24px; font-size: 10px;" onchange="updateSelected('appearance')">
                    <option value="solid">Solid</option>
                    <option value="dashed">Dashed</option>
                    <option value="dotted">Dotted</option>
                </select>
                <input type="number" id="propBorderWidth" class="form-control form-control-sm p-0 text-center"
                    style="width: 40px; height: 24px;" placeholder="0" onchange="updateSelected('appearance')">
                <input type="color" id="propBorderColor"
                    class="form-control form-control-color bg-transparent border-0 p-0"
                    style="width: 24px; height: 24px;" onchange="updateSelected('appearance')">
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <label class="small text-muted text-uppercase" style="font-size: 10px;">Corner Radius</label>
            <input type="number" id="propRadius" class="form-control form-control-sm p-0 text-center"
                style="width: 40px; height: 24px;" placeholder="0" onchange="updateSelected('appearance')">
        </div>
    </div>

</div>