// MediDesign Pro - Responsive & Paper Size Aware
let curDoc = null, selId = null, z = 1, drag = null, rsz = null;
let saveTimer = null, isReplacingImage = false;

// Configuration
const DPI = 96;
const MM_TO_PX = 3.7795; // (96 / 25.4)

// Paper Presets (Consolidated)
const PAPER_PRESETS = {
    'a3': { w: 297, h: 420, name: 'A3 (297 x 420 mm)' },
    'a4': { w: 210, h: 297, name: 'A4 (210 x 297 mm)' },
    'a5': { w: 148, h: 210, name: 'A5 (148 x 210 mm)' },
    'letter': { w: 216, h: 279, name: 'Letter (216 x 279 mm)' },
    'legal': { w: 216, h: 356, name: 'Legal (216 x 356 mm)' },
    'tabloid': { w: 279, h: 432, name: 'Tabloid (279 x 432 mm)' },
    'b4': { w: 250, h: 353, name: 'B4 (250 x 353 mm)' },
    'b5': { w: 176, h: 250, name: 'B5 (176 x 250 mm)' },
    'custom': { w: 210, h: 297, name: 'Custom' }
};

const D = document, W = window, M = Math,
    ID = i => D.getElementById(i), CE = t => D.createElement(t);

// --- HELPERS: Pixels <-> Percentages ---
// Fallback to curDoc.paper if DOM is not ready
const getCW = () => (curDoc && curDoc.paper ? curDoc.paper.w : (ID('canvas') ? ID('canvas').offsetWidth : 794));
const getCH = () => (curDoc && curDoc.paper ? curDoc.paper.h : (ID('canvas') ? ID('canvas').offsetHeight : 1123));

const toPctX = (px) => (px / getCW()) * 100;
const toPctY = (px) => (px / getCH()) * 100;
const toPxX = (pct) => (pct / 100) * getCW();
const toPxY = (pct) => (pct / 100) * getCH();


// --- KEYBOARD SHORTCUTS ---
D.addEventListener('keydown', e => {
    if (e.key === 'Delete' && selId) {
        // Prevent deletion if user is typing in an input or editing text inside an element
        const tag = e.target.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || e.target.isContentEditable) return;

        W.deleteSelected();
    }
});


// --- SERVER SYNC ---
const performSave = (manual) => {
    if (!curDoc) return;
    const status = ID('saveStatus');
    if (status) status.innerText = 'Saving...';

    const payload = {
        name: ID('docName').value,
        bgImage: curDoc.bgImage,
        elements: curDoc.elements,
        paper: curDoc.paper || { type: 'a4', w: 794, h: 1123 }
    };

    fetch(W.saveUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': D.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(d => {
            if (status) { status.innerText = 'Saved'; setTimeout(() => status.innerText = '', 2000); }
            if (manual) alert('Document saved successfully!');
        })
        .catch(e => { console.error(e); if (status) status.innerText = 'Error Saving!'; });
};

const SV = (manual = false) => {
    if (!curDoc) return;
    if (manual) { if (saveTimer) clearTimeout(saveTimer); performSave(true); return; }
    const status = ID('saveStatus'); if (status) status.innerText = '...';
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => { performSave(false); }, 2000);
};

const GD = () => curDoc;

// --- RENDER LOGIC ---
const ren = () => {
    const ws = ID('workspace');
    if (ws) {
        ws.classList.remove('justify-content-center', 'align-items-center');
        ws.classList.add('d-flex');
    }

    const c = ID('canvas');
    c.innerHTML = '';

    // Default Paper
    if (!curDoc.paper) curDoc.paper = { type: 'a4', orientation: 'portrait', w: 794, h: 1123, mmW: 210, mmH: 297 };

    c.style.width = curDoc.paper.w + 'px';
    c.style.height = curDoc.paper.h + 'px';
    c.style.margin = 'auto';

    Object.assign(c.style, { backgroundImage: curDoc.bgImage ? `url(${curDoc.bgImage})` : 'none', backgroundSize: '100% 100%' });

    curDoc.elements.forEach((e, i) => {
        // [REMOVED] Legacy Pixel Auto-Conversion Logic
        // This was causing large shapes (>100px) to be re-converted to percentages repeatedly.
        // Elements are now trusted to be in % already.

        let b = CE('div'), inr = CE('div');
        b.id = e.id;
        b.className = `doc-element ${e.id == selId ? 'selected' : ''} ${e.locked ? 'locked' : ''}`;

        Object.assign(b.style, {
            left: e.x + '%', top: e.y + '%',
            width: e.w + '%', height: e.h + '%',
            zIndex: i + 10
        });

        b.ondragstart = () => false;

        inr.className = 'element-content';
        Object.assign(inr.style, e.style);

        // Content Rendering
        if (e.type == 'text') {
            inr.innerText = e.content;
            inr.style.whiteSpace = 'pre-wrap';
        }
        else if (e.type == 'image') {
            let img = CE('img');
            img.src = e.content;
            img.style.width = '100%';
            img.style.height = '100%';
            inr.appendChild(img);
        }
        // Shapes use CSS backgrounds/borders

        b.append(inr);

        ['nw', 'ne', 'sw', 'se'].map(h => {
            let sp = CE('span'); sp.className = 'resize-handle handle-' + h;
            sp.onmousedown = ev => initRsz(ev, e.id, h);
            b.append(sp);
        });

        b.onmousedown = ev => initDrg(ev, b);
        if (e.type == 'text') b.ondblclick = (ev) => { ev.stopPropagation(); edit(inr, b, e); };
        c.append(b);
    });
    renLayers();
    updUI();
};

const renLayers = () => {
    const l = ID('layersList'); if (!l) return; l.innerHTML = '';
    [...curDoc.elements].reverse().forEach(e => {
        let li = CE('div'), ic = e.type == 'image' ? 'image' : e.type == 'text' ? 'font' : 'square';
        li.className = `layer-item ${e.id == selId ? 'active' : ''}`; li.dataset.id = e.id;
        li.innerHTML = `
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <i class="fa-solid fa-${ic} opacity-50 text-center" style="width:16px"></i>
            ${e.locked ? '<i class="fa-solid fa-lock text-danger" style="font-size:10px; margin-right:4px;"></i>' : ''}
            <input class="bg-transparent border-0 outline-none w-100 small text-dark fw-bold" value="${e.name || e.type}" onchange="renLay('${e.id}',this.value)">
        </div>
        <div class="d-flex align-items-center gap-2 opacity-50 hover-opacity-100">
            <button class="btn btn-sm p-0 text-dark" onclick="mvLay('${e.id}',1)"><i class="fa-solid fa-chevron-up"></i></button>
            <button class="btn btn-sm p-0 text-dark" onclick="mvLay('${e.id}',-1)"><i class="fa-solid fa-chevron-down"></i></button>
        </div>`;
        li.onclick = ev => { if (!['INPUT', 'BUTTON', 'I'].includes(ev.target.tagName)) sel(e.id) };
        l.appendChild(li);
    });
};

const sel = (i, skipRen) => {
    selId = i;
    Array.from(D.getElementsByClassName('doc-element')).forEach(e => {
        e.classList.toggle('selected', e.id == selId);
        if (e.id != selId) {
            const inr = e.querySelector('.element-content'); if (inr && inr.isContentEditable) inr.blur();
        }
    });
    if (!skipRen) renLayers();
    updUI();
};

const updUI = () => {
    const p = ID('propPanel'), em = ID('propEmpty'), el = selId ? curDoc.elements.find(e => e.id == selId) : null;
    if (p) {
        if (!el) { p.classList.add('d-none'); p.classList.remove('d-flex'); em.classList.remove('d-none'); return; }
        p.classList.remove('d-none'); p.classList.add('d-flex'); em.classList.add('d-none');
    } else { if (!el) return; }

    const setV = (id, v) => { if (ID(id)) ID(id).value = v; };
    const setT = (id, v) => { if (ID(id)) ID(id).innerText = v; };

    // Display in Pixels for User
    setV('propX', M.round(toPxX(el.x))); setV('propY', M.round(toPxY(el.y)));
    setV('propW', M.round(toPxX(el.w))); setV('propH', M.round(toPxY(el.h)));

    setV('propOpacity', el.style.opacity); setT('opacityVal', M.round(el.style.opacity * 100) + '%');
    setV('propColor', el.style.color);

    const hasBg = el.style.backgroundColor != 'transparent';
    if (ID('hasBg')) ID('hasBg').checked = hasBg;
    setV('propBgColor', hasBg ? el.style.backgroundColor : '#ffffff');

    if (ID('propText')) ID('propText').classList.toggle('d-none', el.type != 'text');
    if (el.type == 'text') {
        setV('propFontFamily', el.style.fontFamily); setV('propSize', parseInt(el.style.fontSize));
        setV('propWeight', el.style.fontWeight); setV('propLineHeight', parseFloat(el.style.lineHeight) || 1.5);
    }

    const isImg = el.type == 'image';
    if (ID('propImage')) ID('propImage').classList.toggle('d-none', !isImg);
    if (ID('aspectRatioCtrl')) ID('aspectRatioCtrl').classList.toggle('d-none', !isImg);
    if (isImg && ID('propKeepRatio')) ID('propKeepRatio').checked = el.keepRatio;

    setV('propBorderWidth', parseInt(el.style.borderWidth) || 0); setV('propBorderColor', el.style.borderColor);
    setV('propRadius', parseInt(el.style.borderRadius) || 0); setV('propBorderStyle', el.style.borderStyle || 'solid');

    if (ID('btnLock')) {
        ID('btnLock').innerHTML = el.locked ? '<i class="fa-solid fa-lock"></i> Unlock' : '<i class="fa-solid fa-lock-open"></i> Lock';
        ID('btnLock').className = `btn btn-sm flex-fill ${el.locked ? 'btn-danger text-light' : 'bg-white border-theme text-secondary shadow-sm'}`;
    }
};

const initDrg = (e, div) => {
    if (e.target.className.includes('resize') || e.target.isContentEditable) return;
    if (e.detail > 1) return;
    sel(div.id, true);
    const el = curDoc.elements.find(x => x.id == div.id); if (el.locked) return;
    e.stopPropagation();
    const rect = div.getBoundingClientRect();
    const parentRect = ID('canvas').getBoundingClientRect();
    drag = {
        el: div,
        sx: e.clientX, sy: e.clientY,
        ex: rect.left - parentRect.left,
        ey: rect.top - parentRect.top
    };
    D.onmousemove = onMove; D.onmouseup = end;
};

const initRsz = (e, id, h) => {
    e.stopPropagation(); e.preventDefault();
    const el = curDoc.elements.find(x => x.id == id); if (el.locked) return;
    // Calc initial pixels
    const pxW = toPxX(el.w), pxH = toPxY(el.h), pxX = toPxX(el.x), pxY = toPxY(el.y);
    rsz = { id, h, sx: e.clientX, sy: e.clientY, ex: pxX, ey: pxY, ew: pxW, eh: pxH };
    D.onmousemove = onMove; D.onmouseup = end;
};

const onMove = e => {
    if (drag) {
        const dx = (e.clientX - drag.sx) / z, dy = (e.clientY - drag.sy) / z;
        const newX = drag.ex + dx, newY = drag.ey + dy;
        drag.el.style.left = newX + 'px';
        drag.el.style.top = newY + 'px';
        if (ID('propX')) { ID('propX').value = M.round(newX); ID('propY').value = M.round(newY); }
    } else if (rsz) {
        const el = curDoc.elements.find(x => x.id == rsz.id);
        const dx = (e.clientX - rsz.sx) / z, dy = (e.clientY - rsz.sy) / z;
        let nw = rsz.ew, nh = rsz.eh, nx = rsz.ex, ny = rsz.ey;

        if (rsz.h.includes('e')) nw += dx; if (rsz.h.includes('w')) { nw -= dx; nx += dx; }
        if (rsz.h.includes('s')) nh += dy; if (rsz.h.includes('n')) { nh -= dy; ny += dy; }

        if (el.keepRatio && el.type == 'image') { let r = rsz.ew / rsz.eh; rsz.h.includes('e') || rsz.h.includes('w') ? nh = nw / r : nw = nh * r; }
        if (nw < 10) nw = 10; if (nh < 10) nh = 10;

        const d = ID(rsz.id);
        d.style.width = nw + 'px'; d.style.height = nh + 'px';
        d.style.left = nx + 'px'; d.style.top = ny + 'px';

        if (ID('propW')) { ID('propW').value = M.round(nw); ID('propH').value = M.round(nh); }
    }
};

const end = e => {
    if (drag) {
        const el = curDoc.elements.find(x => x.id == drag.el.id);
        const dx = (e.clientX - drag.sx) / z, dy = (e.clientY - drag.sy) / z;
        el.x = toPctX(drag.ex + dx); el.y = toPctY(drag.ey + dy);
        drag.el.style.left = el.x + '%'; drag.el.style.top = el.y + '%';
        drag = null;
    }
    if (rsz) {
        const d = ID(rsz.id), el = curDoc.elements.find(x => x.id == rsz.id);

        // FIX: Check units to avoid double conversion
        const getVal = (prop) => {
            const val = d.style[prop];
            if (!val) return 0;
            return val.includes('%') ? parseFloat(val) : (prop === 'width' || prop === 'left' ? toPctX(parseFloat(val)) : toPctY(parseFloat(val)));
        };

        el.w = getVal('width'); el.h = getVal('height');
        el.x = getVal('left'); el.y = getVal('top');

        d.style.width = el.w + '%'; d.style.height = el.h + '%';
        d.style.left = el.x + '%'; d.style.top = el.y + '%';
        rsz = null;
    }
    SV(); D.onmousemove = D.onmouseup = null;
};

const edit = (inr, wrapper, e) => {
    if (e.locked) return;
    wrapper.style.userSelect = 'text'; wrapper.style.cursor = 'text';
    inr.contentEditable = true; inr.classList.add('editing'); inr.focus();
    inr.onblur = () => {
        inr.contentEditable = false; inr.classList.remove('editing');
        wrapper.style.userSelect = ''; wrapper.style.cursor = '';
        e.content = inr.innerText;
        SV();
    }
};

D.addEventListener('DOMContentLoaded', () => {
    if (typeof W.docData !== 'undefined') { curDoc = W.docData; }
    else { curDoc = { name: 'Error', elements: [] }; }
    ren();

    ID('workspace').onmousedown = e => { if (e.target.id == 'workspace' || e.target.id == 'canvas') sel(null) };
    const f = (e, c) => { let fl = e.target.files[0], r = new FileReader(); r.onload = v => c(v.target.result); if (fl) r.readAsDataURL(fl); e.target.value = '' };
    if (ID('imgUploadBtn')) ID('imgUploadBtn').onchange = e => f(e, v => W.addElement('image', v));
    if (ID('bgUploadBtn')) ID('bgUploadBtn').onchange = e => f(e, v => { curDoc.bgImage = v; SV(); ren(); });

    const upd = (f) => { if (!selId) return; f(GD().elements.find(e => e.id == selId)); SV(); ren(); };

    // Helper to safely parse numbers
    const safeP = (v) => { const n = parseFloat(v); return isNaN(n) ? '' : n; };

    const ips = {
        propX: (e, v) => { let n = safeP(v); if (n !== '') e.x = toPctX(n); },
        propY: (e, v) => { let n = safeP(v); if (n !== '') e.y = toPctY(n); },
        propW: (e, v) => { let n = safeP(v); if (n !== '') e.w = toPctX(n); },
        propH: (e, v) => { let n = safeP(v); if (n !== '') e.h = toPctY(n); },

        propSize: (e, v) => e.style.fontSize = v + 'px',
        propColor: (e, v) => e.style.color = v, propOpacity: (e, v) => e.style.opacity = v,
        propBorderWidth: (e, v) => e.style.borderWidth = v + 'px', propBorderColor: (e, v) => e.style.borderColor = v,
        propRadius: (e, v) => e.style.borderRadius = v + 'px', propWeight: (e, v) => e.style.fontWeight = v,
        propFontFamily: (e, v) => e.style.fontFamily = v, propBorderStyle: (e, v) => e.style.borderStyle = v,
        propKeepRatio: (e, v, t) => e.keepRatio = t.checked, propLineHeight: (e, v) => e.style.lineHeight = v
    };
    Object.keys(ips).forEach(k => { let el = ID(k); if (el) el.addEventListener(el.type == 'checkbox' ? 'change' : 'input', function () { upd(e => ips[k](e, this.value, this)) }) });

    const bg = () => { let v = ID('hasBg').checked ? ID('propBgColor').value : 'transparent'; upd(e => e.style.backgroundColor = v) };
    if (ID('hasBg')) ID('hasBg').onchange = bg;
    if (ID('propBgColor')) ID('propBgColor').oninput = bg;
});

W.saveDoc = (m) => SV(m);

W.changeImage = () => {
    if (selId && GD().elements.find(e => e.id == selId).type == 'image') {
        isReplacingImage = true;
        if (typeof W.openMediaLibrary === 'function') W.openMediaLibrary(); else alert('Media Library missing');
    }
};

W.openPageSetup = () => {
    const p = curDoc.paper;
    ID('paperPreset').value = p.type === 'custom' ? 'custom' : p.type;
    if (p.orientation === 'landscape') ID('orientLandscape').checked = true; else ID('orientPortrait').checked = true;
    const mmW = p.mmW || Math.round(p.w / MM_TO_PX);
    const mmH = p.mmH || Math.round(p.h / MM_TO_PX);
    ID('paperWidth').value = mmW; ID('paperHeight').value = mmH;
    W.toggleCustomInputs();
    const modal = new bootstrap.Modal(ID('pageSetupModal'));
    modal.show();
};

W.toggleCustomInputs = () => {
    const isCustom = ID('paperPreset').value === 'custom';
    ID('paperWidth').disabled = !isCustom; ID('paperHeight').disabled = !isCustom;
    if (!isCustom) {
        const dim = PAPER_PRESETS[ID('paperPreset').value];
        const isLand = ID('orientLandscape').checked;
        ID('paperWidth').value = isLand ? dim.h : dim.w;
        ID('paperHeight').value = isLand ? dim.w : dim.h;
    }
};

W.applyPageSetup = () => {
    const type = ID('paperPreset').value;
    const orient = ID('orientLandscape').checked ? 'landscape' : 'portrait';
    let mmW, mmH;
    if (type === 'custom') { mmW = parseFloat(ID('paperWidth').value) || 210; mmH = parseFloat(ID('paperHeight').value) || 297; }
    else { const dim = PAPER_PRESETS[type]; mmW = orient === 'landscape' ? dim.h : dim.w; mmH = orient === 'landscape' ? dim.w : dim.h; }
    const pxW = Math.round(mmW * MM_TO_PX); const pxH = Math.round(mmH * MM_TO_PX);
    curDoc.paper = { type: type, orientation: orient, mmW: mmW, mmH: mmH, w: pxW, h: pxH };
    ren(); SV();
    bootstrap.Modal.getInstance(ID('pageSetupModal')).hide();
};

W.setPaperSize = (size) => {
    if (!curDoc) return;
    if (PAPER_SIZES[size]) { curDoc.paper = { size: size, ...PAPER_SIZES[size] }; ren(); SV(); }
};

W.updateSelected = (t) => { };

// --- EXPORTED FUNCTIONS ---
W.addElement = (t, s) => {
    if (isReplacingImage && t === 'image' && selId) {
        let el = curDoc.elements.find(e => e.id == selId);
        if (el && el.type === 'image') { el.content = s; SV(); ren(); isReplacingImage = false; return; }
    }
    isReplacingImage = false;
    let n = {
        id: 'el_' + Date.now(), type: t, name: t[0].toUpperCase() + t.slice(1),
        x: 10, y: 10, w: 20, h: 10,
        locked: false, content: '', keepRatio: t == 'image',
        style: { fontSize: '16px', fontWeight: 'normal', textAlign: 'left', color: '#000', backgroundColor: 'transparent', borderWidth: '0', borderColor: '#000', borderStyle: 'solid', borderRadius: '0', opacity: 1, fontFamily: 'sans-serif', lineHeight: '1.5' }
    };
    if (t == 'text') { n.w = 30; n.h = 5; n.content = 'Double click to edit'; }
    if (t == 'image') { n.w = 30; n.h = 20; n.content = s || 'https://via.placeholder.com/150'; }
    if (t == 'shape') { n.style.backgroundColor = '#e2e8f0'; if (s == 'rect') n.name = 'Rectangle'; if (s == 'circle') { n.name = 'Circle'; n.style.borderRadius = '50%'; } if (s == 'line') { n.name = 'Line'; n.h = 0.2; n.w = 40; n.style.backgroundColor = '#000'; } }
    curDoc.elements.push(n); SV(); ren(); sel(n.id);
};

W.selectElement = sel;
W.deleteSelected = () => { if (!selId) return; curDoc.elements = curDoc.elements.filter(e => e.id != selId); sel(null); SV(); ren(); };
W.toggleLock = () => { if (!selId) return; let e = curDoc.elements.find(x => x.id == selId); e.locked = !e.locked; SV(); ren(); };
W.duplicateSelected = () => { if (!selId) return; let e = curDoc.elements.find(x => x.id == selId), c = JSON.parse(JSON.stringify(e)); c.id = 'el_' + Date.now(); c.x += 2; c.y += 2; c.name += ' (Copy)'; curDoc.elements.push(c); SV(); ren(); sel(c.id); };
W.mvLay = (id, d) => { let es = curDoc.elements, i = es.findIndex(e => e.id == id), n = i + d; if (n >= 0 && n < es.length) { [es[i], es[n]] = [es[n], es[i]]; SV(); ren(); } };
W.renLay = (id, v) => { let e = curDoc.elements.find(x => x.id == id); if (e) { e.name = v; SV(); } };
W.alignSelected = m => { if (!selId) return; let e = curDoc.elements.find(x => x.id == selId); if (m == 'centerH') e.x = (50 - (e.w / 2)); if (m == 'centerV') e.y = (50 - (e.h / 2)); SV(); ren(); };
W.zoom = d => { z = M.max(.5, M.min(2, z + d)); ID('canvas').style.transform = `scale(${z})`; ID('zoomLevel').innerText = M.round(z * 100) + '%'; };
W.switchTab = t => { ['props', 'layers'].map(k => { let b = ID('tab-' + k), p = ID('panel-' + k); if (!b || !p) return; if (k == t) { b.classList.add('border-primary', 'text-primary', 'bg-white', 'border-bottom', 'border-2'); b.classList.remove('text-muted', 'hover-bg-theme'); p.classList.remove('d-none'); p.classList.add('d-flex'); } else { b.classList.remove('border-primary', 'text-primary', 'bg-white', 'border-bottom', 'border-2'); b.classList.add('text-muted', 'hover-bg-theme'); p.classList.add('d-none'); p.classList.remove('d-flex'); } }) };
W.toggleStyle = p => { if (!selId) return; let e = curDoc.elements.find(x => x.id == selId), s = e.style; if (p == 'bold') s.fontWeight = s.fontWeight == 'bold' ? 'normal' : 'bold'; if (p == 'italic') s.fontStyle = s.fontStyle == 'italic' ? 'normal' : 'italic'; if (p == 'underline') s.textDecoration = s.textDecoration.includes('underline') ? 'none' : 'underline'; if (['left', 'center', 'right'].includes(p)) s.textAlign = p; SV(); ren(); };
W.showVarModal = () => { ID('varModal').classList.remove('d-none'); ID('varModal').classList.add('d-flex'); };
W.addVar = v => { ID('varModal').classList.remove('d-flex'); ID('varModal').classList.add('d-none'); if (selId) { let e = curDoc.elements.find(x => x.id == selId); if (e && e.type === 'text') { e.content = e.content + ' ' + v; SV(); ren(); return; } } W.addElement('text'); setTimeout(() => { let e = curDoc.elements.slice(-1)[0]; e.content = v; e.style.color = 'blue'; SV(); ren(); }, 50); };