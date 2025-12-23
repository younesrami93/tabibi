// MediDesign Pro v3.5 - Final Fix for Text Editing
let docs = [], curId = null, selId = null, z = 1, drag = null, rsz = null;
const D = document, W = window, L = localStorage, J = JSON, M = Math,
    ID = i => D.getElementById(i), CE = t => D.createElement(t),
    SV = () => { L.setItem('mediDesignDocs', J.stringify(docs)) },
    GD = () => docs.find(d => d.id == curId),

    // --- RENDER LOGIC ---
    RFR = i => {
        const s = ID('docSelector'); s.innerHTML = '<option value="new">+ New Document</option>';
        docs.forEach(d => s.add(new Option(d.name, d.id)));
        if (i) load(i);
    },
    load = i => {
        curId = i; ID('docSelector').value = i; const d = GD(); ID('docName').value = d.name;
        Object.assign(ID('canvas').style, { backgroundImage: d.bgImage ? `url(${d.bgImage})` : 'none', backgroundSize: '100% 100%' });
        ren();
    },
    sel = (i, skipRen) => {
        selId = i;
        Array.from(D.getElementsByClassName('doc-element')).forEach(e => {
            e.classList.toggle('selected', e.id == selId);
            // Safety: Ensure we turn off edit mode if we click away
            if (e.id != selId) {
                const inr = e.querySelector('.element-content');
                if (inr && inr.isContentEditable) inr.blur();
            }
        });
        Array.from(D.getElementsByClassName('layer-item')).forEach(e => e.classList.toggle('active', e.dataset.id == selId));
        if (!skipRen) renLayers();
        updUI();
    },
    ren = () => {
        const c = ID('canvas'), d = GD(); c.innerHTML = '';
        d.elements.forEach((e, i) => {
            let b = CE('div'), inr = CE('div');
            b.id = e.id; b.className = `doc-element ${e.id == selId ? 'selected' : ''} ${e.locked ? 'locked' : ''}`;
            Object.assign(b.style, { left: e.x + 'px', top: e.y + 'px', width: e.w + 'px', zIndex: i + 10, height: e.h + 'px' });
            
            // Critical: Disable native browser drag to prevent conflicts
            b.ondragstart = () => false;

            inr.className = 'element-content'; Object.assign(inr.style, e.style);
            if (e.type == 'text') { inr.innerText = e.content; inr.style.whiteSpace = 'pre-wrap'; }
            else {
                let img = CE('img');
                img.src = e.content;
                img.style.width = '100%';
                img.style.height = '100%';
                inr.appendChild(img);
            }
            b.append(inr);
            ['nw', 'ne', 'sw', 'se'].map(h => {
                let sp = CE('span'); sp.className = 'resize-handle handle-' + h;
                sp.onmousedown = ev => initRsz(ev, e.id, h); b.append(sp)
            });
            
            // Attach interactions
            b.onmousedown = ev => initDrg(ev, b);
            
            // Double click handler for Text
            if (e.type == 'text') {
                b.ondblclick = (ev) => { 
                    ev.stopPropagation(); // Stop bubbling
                    edit(inr, b, e); 
                };
            }
            
            c.append(b);
        });
        renLayers();
        updUI();
    },
    renLayers = () => {
        const l = ID('layersList'), d = GD(); l.innerHTML = '';
        [...d.elements].reverse().forEach(e => {
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
    },
    updUI = () => {
        const p = ID('propPanel'), em = ID('propEmpty'), el = selId ? GD().elements.find(e => e.id == selId) : null;
        if (!el) { p.classList.add('d-none'); p.classList.remove('d-flex'); em.classList.remove('d-none'); return; }
        p.classList.remove('d-none'); p.classList.add('d-flex'); em.classList.add('d-none');

        ['X', 'Y', 'W', 'H'].map(k => ID('prop' + k).value = M.round(el[k.toLowerCase()]));
        ID('propOpacity').value = el.style.opacity; ID('opacityVal').innerText = M.round(el.style.opacity * 100) + '%';
        ID('propColor').value = el.style.color;
        const hasBg = el.style.backgroundColor != 'transparent';
        ID('hasBg').checked = hasBg; ID('propBgColor').value = hasBg ? el.style.backgroundColor : '#ffffff';
        
        ID('propText').classList.toggle('d-none', el.type != 'text');
        if (el.type == 'text') { ID('propFontFamily').value = el.style.fontFamily; ID('propSize').value = parseInt(el.style.fontSize); ID('propWeight').value = el.style.fontWeight; }
        
        ID('aspectRatioCtrl').classList.toggle('d-none', el.type != 'image');
        if (el.type == 'image') ID('propKeepRatio').checked = el.keepRatio;
        
        ID('propBorderWidth').value = parseInt(el.style.borderWidth) || 0; ID('propBorderColor').value = el.style.borderColor;
        ID('propRadius').value = parseInt(el.style.borderRadius) || 0; ID('propBorderStyle').value = el.style.borderStyle || 'solid';
        
        ID('btnLock').innerHTML = el.locked ? '<i class="fa-solid fa-lock"></i> Unlock' : '<i class="fa-solid fa-lock-open"></i> Lock';
        ID('btnLock').className = `btn btn-sm flex-fill ${el.locked ? 'btn-danger text-light' : 'bg-white border-theme text-secondary shadow-sm'}`;
    },

    // --- INTERACTIONS ---
    initDrg = (e, div) => {
        // STOP if clicking resize handle OR if already in edit mode
        if (e.target.className.includes('resize') || e.target.isContentEditable) return;
        
        // STOP if this is part of a double-click (browser reports click count in e.detail)
        if (e.detail > 1) return; 

        sel(div.id, true);
        const el = GD().elements.find(x => x.id == div.id); if (el.locked) return;
        
        e.stopPropagation(); 
        // DO NOT PREVENT DEFAULT here (it kills focus)
        
        drag = { el: div, sx: e.clientX, sy: e.clientY, ex: el.x, ey: el.y };
        D.onmousemove = onMove; D.onmouseup = end;
    },
    initRsz = (e, id, h) => {
        e.stopPropagation(); e.preventDefault();
        const el = GD().elements.find(x => x.id == id); if (el.locked) return;
        rsz = { id, h, sx: e.clientX, sy: e.clientY, ex: el.x, ey: el.y, ew: el.w, eh: el.h };
        D.onmousemove = onMove; D.onmouseup = end;
    },
    onMove = e => {
        if (drag) {
            const dx = (e.clientX - drag.sx) / z, dy = (e.clientY - drag.sy) / z;
            drag.el.style.left = (drag.ex + dx) + 'px'; drag.el.style.top = (drag.ey + dy) + 'px';
            ID('propX').value = M.round(drag.ex + dx); ID('propY').value = M.round(drag.ey + dy);
        } else if (rsz) {
            const el = GD().elements.find(x => x.id == rsz.id), dx = (e.clientX - rsz.sx) / z, dy = (e.clientY - rsz.sy) / z;
            let nw = rsz.ew, nh = rsz.eh, nx = rsz.ex, ny = rsz.ey;
            if (rsz.h.includes('e')) nw += dx; if (rsz.h.includes('w')) { nw -= dx; nx += dx; }
            if (rsz.h.includes('s')) nh += dy; if (rsz.h.includes('n')) { nh -= dy; ny += dy; }
            if (el.keepRatio && el.type == 'image') { let r = rsz.ew / rsz.eh; rsz.h.includes('e') || rsz.h.includes('w') ? nh = nw / r : nw = nh * r; }
            if (nw < 10) nw = 10; if (nh < 10) nh = 10;
            const d = ID(rsz.id); d.style.width = nw + 'px'; d.style.left = nx + 'px'; d.style.top = ny + 'px'; d.style.height = nh + 'px';
            ID('propW').value = M.round(nw); ID('propH').value = M.round(nh);
        }
    },
    end = e => {
        if (drag) { const el = GD().elements.find(x => x.id == drag.el.id), dx = (e.clientX - drag.sx) / z, dy = (e.clientY - drag.sy) / z; el.x = M.round(drag.ex + dx); el.y = M.round(drag.ey + dy); drag = null; }
        if (rsz) { const d = ID(rsz.id), el = GD().elements.find(x => x.id == rsz.id); el.w = parseFloat(d.style.width); el.h = parseFloat(d.style.height || d.offsetHeight); el.x = parseFloat(d.style.left); el.y = parseFloat(d.style.top); rsz = null; }
        SV(); ren();
        D.onmousemove = D.onmouseup = null;
    },
    
    // --- EDITING LOGIC (Fixed) ---
    edit = (inr, wrapper, e) => {
        if (e.locked) return;
        
        // 1. Force the PARENT wrapper to allow text selection (overriding user-select: none)
        wrapper.style.userSelect = 'text'; 
        wrapper.style.cursor = 'text';
        
        // 2. Enable editing
        inr.contentEditable = true;
        inr.classList.add('editing');
        inr.focus();

        // 3. Force Focus & Selection
        // We use a slight timeout to ensure the browser processes the 'contentEditable=true' state
        setTimeout(() => {
            inr.focus();
            if (document.activeElement !== inr) inr.focus(); // Retry
        }, 0);

        // 4. On Blur: Save and Revert styles
        inr.onblur = () => { 
            inr.contentEditable = false; 
            inr.classList.remove('editing'); 
            
            // Revert styles to allow dragging again
            wrapper.style.userSelect = ''; 
            wrapper.style.cursor = '';
            
            e.content = inr.innerText; 
            SV(); ren(); 
        }
    };

// --- INIT ---
D.addEventListener('DOMContentLoaded', () => {
    try { docs = J.parse(L.getItem('mediDesignDocs')) || [] } catch (e) { docs = [] }
    if (!docs.length) { docs = [{ id: 'doc_' + Date.now(), name: 'New Doc', bgImage: null, elements: [] }]; SV(); }
    RFR(docs[0].id);
    ID('workspace').onmousedown = e => { if (e.target.id == 'workspace' || e.target.id == 'canvas') sel(null) };
    const f = (e, c) => { let fl = e.target.files[0], r = new FileReader(); r.onload = v => c(v.target.result); if (fl) r.readAsDataURL(fl); e.target.value = '' };
    ID('imgUploadBtn').onchange = e => f(e, v => W.addElement('image', v));
    ID('bgUploadBtn').onchange = e => f(e, v => { const d = GD(); d.bgImage = v; SV(); load(d.id) });
    const upd = (f) => { if (!selId) return; f(GD().elements.find(e => e.id == selId)); SV(); ren(); };
    const ips = {
        propX: (e, v) => e.x = parseInt(v), propY: (e, v) => e.y = parseInt(v), propW: (e, v) => e.w = parseInt(v), propH: (e, v) => e.h = parseInt(v),
        propSize: (e, v) => e.style.fontSize = v + 'px', propColor: (e, v) => e.style.color = v, propOpacity: (e, v) => e.style.opacity = v,
        propBorderWidth: (e, v) => e.style.borderWidth = v + 'px', propBorderColor: (e, v) => e.style.borderColor = v, propRadius: (e, v) => e.style.borderRadius = v + 'px',
        propWeight: (e, v) => e.style.fontWeight = v, propFontFamily: (e, v) => e.style.fontFamily = v, propBorderStyle: (e, v) => e.style.borderStyle = v,
        propKeepRatio: (e, v, t) => e.keepRatio = t.checked
    };
    Object.keys(ips).forEach(k => { let el = ID(k); if (el) el.addEventListener(el.type == 'checkbox' ? 'change' : 'input', function () { upd(e => ips[k](e, this.value, this)) }) });
    const bg = () => { let v = ID('hasBg').checked ? ID('propBgColor').value : 'transparent'; upd(e => e.style.backgroundColor = v) };
    ID('hasBg').onchange = ID('propBgColor').oninput = bg;
});

// --- EXPORTS ---
W.switchDoc = v => v == 'new' ? (() => { let n = { id: 'doc_' + Date.now(), name: 'New Doc', bgImage: null, elements: [] }; docs.push(n); SV(); RFR(n.id) })() : load(v);
W.saveDoc = () => { GD().name = ID('docName').value; SV(); RFR(curId) };
W.addElement = (t, s) => {
    const d = GD(); let n = { id: 'el_' + Date.now(), type: t, name: t[0].toUpperCase() + t.slice(1), x: 50, y: 50, w: 100, h: 100, locked: false, content: '', keepRatio: t == 'image', style: { fontSize: '16px', fontWeight: 'normal', textAlign: 'left', color: '#000', backgroundColor: 'transparent', borderWidth: '0', borderColor: '#000', borderStyle: 'solid', borderRadius: '0', opacity: 1, fontFamily: 'sans-serif' } };
    if (t == 'text') { n.w = 250; n.content = 'Double click to edit'; } if (t == 'image') { n.w = 200; n.h = 200; n.content = s || 'https://via.placeholder.com/150'; }
    if (t == 'shape') { n.style.backgroundColor = '#e2e8f0'; if (s == 'rect') n.name = 'Rectangle'; if (s == 'circle') { n.name = 'Circle'; n.style.borderRadius = '50%'; } if (s == 'line') { n.name = 'Line'; n.h = 2; n.w = 300; n.style.backgroundColor = '#000'; } }
    d.elements.push(n); SV(); ren(); sel(n.id);
};
W.selectElement = sel;
W.deleteSelected = () => { if (!selId) return; GD().elements = GD().elements.filter(e => e.id != selId); sel(null); SV(); ren(); };
W.toggleLock = () => { if (!selId) return; let e = GD().elements.find(x => x.id == selId); e.locked = !e.locked; SV(); ren(); };
W.duplicateSelected = () => { if (!selId) return; let e = GD().elements.find(x => x.id == selId), c = J.parse(J.stringify(e)); c.id = 'el_' + Date.now(); c.x += 20; c.y += 20; c.name += ' (Copy)'; GD().elements.push(c); SV(); ren(); sel(c.id); };
W.mvLay = (id, d) => { let es = GD().elements, i = es.findIndex(e => e.id == id), n = i + d; if (n >= 0 && n < es.length) { [es[i], es[n]] = [es[n], es[i]]; SV(); ren(); } };
W.renLay = (id, v) => { let e = GD().elements.find(x => x.id == id); if (e) { e.name = v; SV(); } };
W.alignSelected = m => { if (!selId) return; let e = GD().elements.find(x => x.id == selId); if (m == 'centerH') e.x = (794 - e.w) / 2; if (m == 'centerV') e.y = (1123 - e.h) / 2; SV(); ren(); };
W.zoom = d => { z = M.max(.5, M.min(2, z + d)); ID('canvas').style.transform = `scale(${z})`; ID('zoomLevel').innerText = M.round(z * 100) + '%'; };

W.switchTab = t => {
    ['props', 'layers'].map(k => {
        let b = ID('tab-' + k), p = ID('panel-' + k);
        if (k == t) {
            b.classList.add('border-primary', 'text-primary', 'bg-white', 'border-bottom', 'border-2');
            b.classList.remove('text-muted', 'hover-bg-theme');
            p.classList.remove('d-none');
            p.classList.add('d-flex');
        } else {
            b.classList.remove('border-primary', 'text-primary', 'bg-white', 'border-bottom', 'border-2');
            b.classList.add('text-muted', 'hover-bg-theme');
            p.classList.add('d-none');
            p.classList.remove('d-flex');
        }
    })
};
W.toggleStyle = p => { if (!selId) return; let e = GD().elements.find(x => x.id == selId), s = e.style; if (p == 'bold') s.fontWeight = s.fontWeight == 'bold' ? 'normal' : 'bold'; if (p == 'italic') s.fontStyle = s.fontStyle == 'italic' ? 'normal' : 'italic'; if (p == 'underline') s.textDecoration = s.textDecoration.includes('underline') ? 'none' : 'underline'; if (['left', 'center', 'right'].includes(p)) s.textAlign = p; SV(); ren(); };

W.showVarModal = () => { ID('varModal').classList.remove('d-none'); ID('varModal').classList.add('d-flex'); }
W.addVar = v => {
    ID('varModal').classList.remove('d-flex');
    ID('varModal').classList.add('d-none');
    W.addElement('text');
    setTimeout(() => { let e = GD().elements.slice(-1)[0]; e.content = v; e.style.color = 'blue'; SV(); ren(); }, 50)
};