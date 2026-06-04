'use strict';

/* ════════════════════════════════════════════════════
 * Pré-remplissage modals Modifier / Supprimer
 * ════════════════════════════════════════════════════ */
function _pageInit() {
  const modalEdit = document.getElementById('modalEditProject');
  if (modalEdit) {
    modalEdit.addEventListener('show.bs.modal', (e) => {
      const btn = e.relatedTarget;
      if (!btn) return;
      modalEdit.querySelector('#ep-id').value     = btn.dataset.id          ?? '';
      modalEdit.querySelector('#ep-nom').value    = btn.dataset.nom         ?? '';
      modalEdit.querySelector('#ep-desc').value   = btn.dataset.description ?? '';
      modalEdit.querySelector('#ep-budget').value = btn.dataset.budget      ?? '';
      modalEdit.querySelector('#ep-debut').value  = btn.dataset.date_debut  ?? '';
      modalEdit.querySelector('#ep-fin').value    = btn.dataset.date_fin    ?? '';
      const sel = modalEdit.querySelector('#ep-statut');
      if (sel) sel.value = btn.dataset.statut ?? 'en_cours';
    });
  }

  const modalDelete = document.getElementById('modalDeleteProject');
  if (modalDelete) {
    modalDelete.addEventListener('show.bs.modal', (e) => {
      const btn = e.relatedTarget;
      if (!btn) return;
      modalDelete.querySelector('#dp-id').value = btn.dataset.id ?? '';
      const nameEl = modalDelete.querySelector('#dp-nom');
      if (nameEl) nameEl.textContent = btn.dataset.nom ?? '';
    });
  }

  /* ── Gantt des événements du projet ─────────────── */
  drawProjetGantt();

  /* ── Recherche AJAX ──────────────────────────────── */
  initSearch();
}

document.addEventListener('DOMContentLoaded', _pageInit);
window.YesPageInit = _pageInit;

/* ════════════════════════════════════════════════════
 * GANTT DES ÉVÉNEMENTS DU PROJET
 * ════════════════════════════════════════════════════ */
function drawProjetGantt() {
  const container = document.getElementById('projet-gantt-container');
  if (!container) return;

  const events = window.PROJET_GANTT_DATA || [];
  if (!events.length) return;

  const allTs = [];
  events.forEach(e => {
    allTs.push(new Date(e.date_debut).getTime());
    if (e.date_fin) allTs.push(new Date(e.date_fin).getTime());
  });

  const minTs   = Math.min(...allTs);
  const maxTs   = Math.max(...allTs);
  const totalMs = Math.max(maxTs - minTs, 86400000);

  const ROW_H = 38, LABEL_W = 200, PAD = 12, BAR_H = 22;
  const W     = Math.max(container.clientWidth || 700, 500);
  const CW    = W - LABEL_W - PAD * 2;
  const H     = events.length * ROW_H + 56;

  container.innerHTML = '';
  const canvas = document.createElement('canvas');
  canvas.width = W; canvas.height = H;
  canvas.style.cssText = 'width:100%;display:block;';
  container.appendChild(canvas);

  const ctx    = canvas.getContext('2d');
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

  ctx.fillStyle = isDark ? '#1e1e2e' : '#f8f9fa';
  ctx.fillRect(0, 0, W, H);

  // Axe temporel
  const nbDays  = Math.ceil(totalMs / 86400000) + 1;
  const tickInt = Math.max(1, Math.ceil(nbDays / 10));
  ctx.font = '11px system-ui,sans-serif'; ctx.textAlign = 'center';

  for (let d = 0; d <= nbDays; d += tickInt) {
    const x    = LABEL_W + PAD + d * (CW / nbDays);
    const date = new Date(minTs + d * 86400000);
    ctx.fillStyle   = isDark ? '#adb5bd' : '#6c757d';
    ctx.fillText(date.toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit'}), x, 14);
    ctx.strokeStyle = isDark ? '#2d2d3d' : '#dee2e6';
    ctx.lineWidth = 1; ctx.setLineDash([4,4]);
    ctx.beginPath(); ctx.moveTo(x,20); ctx.lineTo(x,H); ctx.stroke(); ctx.setLineDash([]);
  }

  // Aujourd'hui
  const todayOff = (Date.now() - minTs) / totalMs;
  if (todayOff >= 0 && todayOff <= 1) {
    const tx = LABEL_W + PAD + todayOff * CW;
    ctx.strokeStyle = '#dc3545'; ctx.lineWidth = 2; ctx.setLineDash([5,3]);
    ctx.beginPath(); ctx.moveTo(tx,20); ctx.lineTo(tx,H-12); ctx.stroke(); ctx.setLineDash([]);
    ctx.fillStyle = '#dc3545'; ctx.font = 'bold 9px system-ui'; ctx.fillText('Auj.', tx, H-2);
  }

  // Barres événements
  const colors = ['#0d6efd','#198754','#dc3545','#ffc107','#0dcaf0','#fd7e14','#6f42c1'];
  events.forEach((ev, i) => {
    const y     = 24 + i * ROW_H;
    const color = colors[i % colors.length];

    ctx.fillStyle = isDark ? (i%2===0?'#1e1e2e':'#252535') : (i%2===0?'#fff':'#f8f9fa');
    ctx.fillRect(0, y, W, ROW_H);

    ctx.fillStyle = isDark ? '#e9ecef' : '#212529';
    ctx.font = '12px system-ui,sans-serif'; ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
    ctx.fillText(_trunc(ctx, ev.nom || '—', LABEL_W - PAD*2), PAD, y + ROW_H/2);

    const deb = new Date(ev.date_debut).getTime();
    const fin = ev.date_fin ? new Date(ev.date_fin).getTime() : deb + 86400000;
    const bx  = LABEL_W + PAD + ((deb - minTs) / totalMs) * CW;
    const bw  = Math.max(4, ((fin - deb) / totalMs) * CW);

    ctx.shadowColor = 'rgba(0,0,0,.15)'; ctx.shadowBlur = 4; ctx.shadowOffsetY = 2;
    _rrect(ctx, bx, y + (ROW_H-BAR_H)/2, bw, BAR_H, 5, color);
    ctx.shadowColor = 'transparent'; ctx.shadowBlur = 0; ctx.shadowOffsetY = 0;

    if (bw > 60) {
      ctx.fillStyle = '#fff'; ctx.font = 'bold 10px system-ui';
      ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      const label = ev.lieu ? ev.lieu : (ev.sport || '');
      if (label) ctx.fillText(label, bx + bw/2, y + ROW_H/2);
    }
  });

  ctx.strokeStyle = isDark?'#343a40':'#dee2e6'; ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(LABEL_W,0); ctx.lineTo(LABEL_W,H); ctx.stroke();

  window.addEventListener('resize', () => {
    if (document.getElementById('projet-gantt-container')) drawProjetGantt();
  });
}

function _rrect(ctx,x,y,w,h,r,f){ctx.fillStyle=f;ctx.beginPath();ctx.moveTo(x+r,y);ctx.lineTo(x+w-r,y);ctx.quadraticCurveTo(x+w,y,x+w,y+r);ctx.lineTo(x+w,y+h-r);ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h);ctx.lineTo(x+r,y+h);ctx.quadraticCurveTo(x,y+h,x,y+h-r);ctx.lineTo(x,y+r);ctx.quadraticCurveTo(x,y,x+r,y);ctx.closePath();ctx.fill();}
function _trunc(ctx,text,maxW){if(ctx.measureText(text).width<=maxW)return text;while(text.length>0&&ctx.measureText(text+'…').width>maxW)text=text.slice(0,-1);return text+'…';}

/* ════════════════════════════════════════════════════
 * RECHERCHE AJAX
 * ════════════════════════════════════════════════════ */
function initSearch() {
  const searchInput = document.querySelector('.search-input');
  if (!searchInput) return;

  let suggestionsEl = null;
  let debounceTimer = null;

  function removeSuggestions() {
    if (suggestionsEl) { suggestionsEl.remove(); suggestionsEl = null; }
  }

  function buildSuggestions(data) {
    removeSuggestions();
    const total = (data.projets?.length ?? 0) + (data.events?.length ?? 0) + (data.staff?.length ?? 0);
    if (total === 0) return;

    const wrap = document.createElement('div');
    wrap.className = 'search-suggestions bg-body border rounded-3';
    wrap.setAttribute('role', 'listbox');

    const addSection = (label, icon, color, items, urlFn) => {
      if (!items?.length) return;
      const cat = document.createElement('p');
      cat.className = `suggestion-category text-${color} mb-0`;
      cat.innerHTML = `<i class="bi bi-${icon} me-1"></i>${label}`;
      wrap.appendChild(cat);
      items.forEach((item) => {
        const a = document.createElement('a');
        a.className = 'suggestion-item'; a.href = urlFn(item);
        a.setAttribute('role', 'option');
        a.innerHTML = `<i class="bi bi-${icon} text-${color}"></i>
                       <span class="fw-medium small">${item.nom ?? (item.prenom + ' ' + item.nom_famille)}</span>
                       ${item.sub ? `<span class="text-body-secondary small ms-auto">${item.sub}</span>` : ''}`;
        a.addEventListener('mousedown', ev => ev.preventDefault());
        wrap.appendChild(a);
      });
    };

    addSection('Projets',    'kanban-fill',         'primary', data.projets, p  => `/projet_detail?id=${p.id}`);
    addSection('Événements', 'calendar-event-fill', 'warning', data.events,  ev => `/gerer_event?id=${ev.id}`);
    addSection('Staff',      'person-fill',         'info',    data.staff,   () => `/staff`);

    const container = searchInput.closest('search') ?? searchInput.parentElement;
    container.style.position = 'relative';
    container.appendChild(wrap);
    suggestionsEl = wrap;
  }

  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) { removeSuggestions(); return; }
    debounceTimer = setTimeout(async () => {
      try {
        const res = await fetch(`/ajax_search&q=${encodeURIComponent(q)}`);
        if (!res.ok) return;
        buildSuggestions(await res.json());
      } catch { /* réseau */ }
    }, 220);
  });

  searchInput.addEventListener('blur',    () => setTimeout(removeSuggestions, 150));
  searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') { removeSuggestions(); searchInput.blur(); } });
}