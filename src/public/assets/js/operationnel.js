/**
 * YES – Your Event Solution
 * @file operationnel.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 *
 * Fonctionnalités :
 * - Mémorisation onglet actif (localStorage)
 * - Vue Liste / Calendrier / Gantt dans le planning
 * - Calendrier mensuel interactif style emploi du temps
 * - Diagramme de Gantt (canvas)
 * - Remplissage auto contact depuis select
 * - Calcul total facturation temps réel
 * - Modals edit : Budget / Planning / Matériel / Facturation
 */

(function () {
  'use strict';

  /* ── Constantes ──────────────────────────────────── */
  const TAB_KEY = 'ops_active_tab';

  /* ─────────────────────────────────────────────────────
   * 1. MÉMORISATION ONGLET ACTIF
   * ───────────────────────────────────────────────────── */
  function initTabMemory() {
    const tabEls = document.querySelectorAll('#opsTabs [data-bs-toggle="tab"]');
    if (!tabEls.length) return;

    // Priorité : data-restore-tab (session PHP) > localStorage
    const container  = document.getElementById('ops-container');
    const restoreTab = container ? container.dataset.restoreTab : '';
    const stored     = localStorage.getItem(TAB_KEY);
    const target     = restoreTab || stored || '#pane-budget';

    // Persister immédiatement en localStorage (cas data-restore-tab)
    if (target) localStorage.setItem(TAB_KEY, target);

    const triggerEl = document.querySelector(`#opsTabs [data-bs-target="${target}"]`);
    if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();

    tabEls.forEach(el => {
      el.addEventListener('shown.bs.tab', e => {
        const t = e.target.getAttribute('data-bs-target');
        localStorage.setItem(TAB_KEY, t);
        updateActiveTabInputs(t);
        if (t === '#pane-planning') setTimeout(onPlanningTabShown, 80);
      });
    });
  }

  function updateActiveTabInputs(tab) {
    document.querySelectorAll('.js-active-tab').forEach(i => i.value = tab);
  }

  function getCurrentTab() {
    const active = document.querySelector('#opsTabs .nav-link.active');
    return active ? active.getAttribute('data-bs-target') : '#pane-budget';
  }

  /* Mettre à jour active_tab juste avant chaque soumission de formulaire */
  function hookFormSubmits() {
    document.querySelectorAll('form[method="POST"]').forEach(form => {
      if (form.dataset.tabHooked) return;
      form.dataset.tabHooked = '1';
      form.addEventListener('submit', () => {
        const tab = getCurrentTab();
        localStorage.setItem(TAB_KEY, tab);
        // Mettre à jour tous les champs js-active-tab dans TOUS les formulaires
        document.querySelectorAll('.js-active-tab').forEach(i => i.value = tab);
      });
    });
  }

  /* ─────────────────────────────────────────────────────
   * 2. BASCULE VUE PLANNING  (liste / calendrier / gantt)
   * ───────────────────────────────────────────────────── */
  const PLANNING_VIEWS = ['list', 'calendar', 'gantt'];

  window.switchPlanningView = function (view) {
    PLANNING_VIEWS.forEach(v => {
      const el  = document.getElementById(`planning-${v}-view`);
      const btn = document.getElementById(`btn-view-${v}`);
      if (!el) return;
      const active = v === view;
      el.style.display = active ? 'block' : 'none';
      btn && btn.classList.toggle('active', active);
    });
    if (view === 'gantt')     setTimeout(drawGantt, 80);
    if (view === 'calendar')  { calState.rendered = false; renderCalendar(); }
  };

  function onPlanningTabShown() {
    // Vérifier quelle vue est active
    const calView = document.getElementById('planning-calendar-view');
    if (calView && calView.style.display !== 'none') renderCalendar();
    const ganttView = document.getElementById('planning-gantt-view');
    if (ganttView && ganttView.style.display !== 'none') drawGantt();
  }

  /* ─────────────────────────────────────────────────────
   * 3. CALENDRIER MENSUEL INTERACTIF
   * ───────────────────────────────────────────────────── */
  const MOIS_FR   = ['Janvier','Février','Mars','Avril','Mai','Juin',
                     'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

  /* Couleurs par statut */
  const STATUT_BG = {
    wip:      '#ffc107', en_cours: '#0d6efd', valide:  '#198754',
    maj:      '#0dcaf0', devis:    '#6c757d', visuels: '#6c757d',
    bat:      '#adb5bd', prod:     '#fd7e14', annule:  '#dc3545',
  };
  const STATUT_LABEL = {
    wip:'WIP', en_cours:'En cours', valide:'Validé', maj:'Maj',
    devis:'Devis', visuels:'Visuels', bat:'BAT', prod:'Prod', annule:'Annulé',
  };

  const calState = {
    year:     new Date().getFullYear(),
    month:    new Date().getMonth(),   // 0-based
    rendered: false,
    selected: null,
  };

  window.calNav = function (delta) {
    calState.month += delta;
    if (calState.month > 11) { calState.month = 0;  calState.year++; }
    if (calState.month < 0)  { calState.month = 11; calState.year--; }
    calState.rendered = false;
    renderCalendar();
  };

  function renderCalendar() {
    const grid      = document.getElementById('cal-grid');
    const titleEl   = document.getElementById('cal-title');
    const detailBox = document.getElementById('cal-day-detail');
    if (!grid) return;

    const { year, month } = calState;
    if (titleEl) titleEl.textContent = `${MOIS_FR[month]} ${year}`;

    const tasks = window.OPS_PLANNING_DATA || [];
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    /* 1er jour du mois (0=dim, 1=lun…) → offset lundi-based */
    const firstDay = new Date(year, month, 1).getDay();   // 0-6 (dim=0)
    const offset   = firstDay === 0 ? 6 : firstDay - 1;  // lundi = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();

    grid.innerHTML = '';

    /* Cellules vides avant le 1er */
    for (let i = 0; i < offset; i++) {
      const empty = document.createElement('div');
      empty.style.cssText = 'min-height:90px;border-radius:8px;';
      grid.appendChild(empty);
    }

    /* Cellules de chaque jour */
    for (let day = 1; day <= daysInMonth; day++) {
      const cell     = document.createElement('div');
      const dateStr  = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
      const isToday  = today.getFullYear()===year && today.getMonth()===month && today.getDate()===day;

      // Tâches dont la date de début ou la plage tombe sur ce jour
      const dayTs   = new Date(dateStr).getTime();
      const dayTasks = tasks.filter(t => {
        if (!t.date_debut) return false;
        const deb = new Date(t.date_debut.substring(0,10)).getTime();
        const fin = t.date_fin ? new Date(t.date_fin.substring(0,10)).getTime() : deb;
        return dayTs >= deb && dayTs <= fin;
      });

      cell.style.cssText = `
        min-height:90px; border-radius:8px; padding:6px;
        background:${isToday
          ? (isDark ? '#1a3a5c' : '#e8f4fd')
          : (isDark ? '#252535' : '#fff')};
        border:${isToday ? '2px solid #0d6efd' : '1px solid ' + (isDark ? '#343a40' : '#dee2e6')};
        cursor:${dayTasks.length ? 'pointer' : 'default'};
        transition:background .15s;
        overflow:hidden;
      `;

      /* Numéro du jour */
      const numEl = document.createElement('div');
      numEl.style.cssText = `font-weight:${isToday?'bold':'500'};font-size:13px;margin-bottom:4px;color:${isToday?'#0d6efd':(isDark?'#e9ecef':'#212529')}`;
      numEl.textContent = day;
      cell.appendChild(numEl);

      /* Pastilles de tâches (max 3 visibles) */
      const maxVisible = 3;
      dayTasks.slice(0, maxVisible).forEach(t => {
        const pill = document.createElement('div');
        const bg   = STATUT_BG[t.statut] || '#6c757d';
        pill.style.cssText = `
          background:${bg}22; border-left:3px solid ${bg};
          border-radius:4px; padding:2px 5px; margin-bottom:2px;
          font-size:10px; white-space:nowrap; overflow:hidden;
          text-overflow:ellipsis; color:${isDark?'#e9ecef':'#212529'};
          font-weight:500;
        `;
        pill.textContent = t.tache || '—';
        pill.title = `${t.tache} (${STATUT_LABEL[t.statut] || t.statut})`;
        cell.appendChild(pill);
      });

      if (dayTasks.length > maxVisible) {
        const more = document.createElement('div');
        more.style.cssText = 'font-size:10px;color:#6c757d;font-style:italic;';
        more.textContent = `+${dayTasks.length - maxVisible} autre${dayTasks.length - maxVisible > 1 ? 's' : ''}`;
        cell.appendChild(more);
      }

      /* Clic → détail du jour */
      if (dayTasks.length > 0) {
        cell.addEventListener('click', () => showDayDetail(day, dateStr, dayTasks));
        cell.addEventListener('mouseenter', () => cell.style.background = isDark ? '#2a2a4a' : '#f0f7ff');
        cell.addEventListener('mouseleave', () => {
          if (calState.selected !== dateStr) {
            cell.style.background = isToday
              ? (isDark ? '#1a3a5c' : '#e8f4fd')
              : (isDark ? '#252535' : '#fff');
          }
        });
      }

      grid.appendChild(cell);
    }

    /* Masquer le détail si le mois change */
    if (detailBox) detailBox.style.display = 'none';
    calState.rendered = true;
    calState.selected = null;
  }

  function showDayDetail(day, dateStr, tasks) {
    calState.selected = dateStr;
    const detailBox   = document.getElementById('cal-day-detail');
    const titleEl     = document.getElementById('cal-day-detail-title');
    const listEl      = document.getElementById('cal-day-tasks');
    if (!detailBox || !titleEl || !listEl) return;

    const d = new Date(dateStr);
    const jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    titleEl.innerHTML = `<i class="bi bi-calendar3 me-2 text-primary"></i>${jours[d.getDay()]} ${day} ${MOIS_FR[calState.month]} ${calState.year}`;

    listEl.innerHTML = '';
    tasks.forEach(t => {
      const bg    = STATUT_BG[t.statut]    || '#6c757d';
      const label = STATUT_LABEL[t.statut] || t.statut;
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
      li.innerHTML = `
        <div>
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${bg};margin-right:8px;"></span>
          <strong>${escHtml(t.tache || '—')}</strong>
          ${t.note ? `<br><small class="text-body-secondary ms-4">${escHtml(t.note)}</small>` : ''}
        </div>
        <div class="text-end small text-body-secondary">
          <span class="badge rounded-pill" style="background:${bg};">${escHtml(label)}</span>
          <br>
          ${t.date_debut ? fmtDate(t.date_debut) : ''}
          ${t.date_fin && t.date_fin !== t.date_debut ? ' → ' + fmtDate(t.date_fin) : ''}
        </div>
      `;
      listEl.appendChild(li);
    });

    detailBox.style.display = 'block';
    detailBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function fmtDate(str) {
    if (!str) return '';
    const p = str.substring(0, 10).split('-');
    return `${p[2]}/${p[1]}/${p[0]}`;
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ─────────────────────────────────────────────────────
   * 4. DIAGRAMME DE GANTT (Canvas)
   * ───────────────────────────────────────────────────── */
  window.drawGantt = function () {
    const container = document.getElementById('gantt-container');
    if (!container) return;

    const tasks   = (window.OPS_PLANNING_DATA || []).filter(t => t.date_debut && t.date_debut !== '0000-00-00');
    const statuts = window.OPS_PLANNING_STATUTS || {};

    if (!tasks.length) {
      container.innerHTML = '<p class="text-body-secondary text-center py-4">Aucune tâche avec dates.</p>';
      return;
    }

    const allTs = [];
    tasks.forEach(t => {
      allTs.push(new Date(t.date_debut).getTime());
      if (t.date_fin) allTs.push(new Date(t.date_fin).getTime());
    });
    const minTs   = Math.min(...allTs);
    const maxTs   = Math.max(...allTs);
    const totalMs = Math.max(maxTs - minTs, 86400000);

    const ROW_H   = 36, LABEL_W = 200, PAD = 16, BAR_H = 22;
    const W       = Math.max(container.clientWidth || 800, 600);
    const CHART_W = W - LABEL_W - PAD * 2;
    const H       = tasks.length * ROW_H + 60;

    container.innerHTML = '';
    const canvas  = document.createElement('canvas');
    canvas.width  = W; canvas.height = H;
    canvas.style.cssText = 'width:100%;display:block;';
    container.appendChild(canvas);

    const ctx    = canvas.getContext('2d');
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const colorMap = {
      wip:'#ffc107', en_cours:'#0d6efd', valide:'#198754', maj:'#0dcaf0',
      devis:'#6c757d', visuels:'#6c757d', bat:'#adb5bd', prod:'#fd7e14', annule:'#dc3545',
    };

    ctx.fillStyle = isDark ? '#1e1e2e' : '#f8f9fa';
    ctx.fillRect(0, 0, W, H);

    /* Axe */
    const nbDays   = Math.ceil(totalMs / 86400000) + 1;
    const tickInt  = Math.max(1, Math.ceil(nbDays / 10));
    ctx.font       = '11px system-ui,sans-serif';
    ctx.textAlign  = 'center';

    for (let d = 0; d <= nbDays; d += tickInt) {
      const x    = LABEL_W + PAD + d * (CHART_W / nbDays);
      const date = new Date(minTs + d * 86400000);
      ctx.fillStyle = isDark ? '#adb5bd' : '#6c757d';
      ctx.fillText(date.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'}), x, 14);
      ctx.strokeStyle = isDark ? '#2d2d3d' : '#dee2e6';
      ctx.lineWidth = 1; ctx.setLineDash([4,4]);
      ctx.beginPath(); ctx.moveTo(x, 20); ctx.lineTo(x, H); ctx.stroke();
      ctx.setLineDash([]);
    }

    /* Aujourd'hui */
    const todayOff = (Date.now() - minTs) / totalMs;
    if (todayOff >= 0 && todayOff <= 1) {
      const tx = LABEL_W + PAD + todayOff * CHART_W;
      ctx.strokeStyle = '#dc3545'; ctx.lineWidth = 2; ctx.setLineDash([6,3]);
      ctx.beginPath(); ctx.moveTo(tx, 20); ctx.lineTo(tx, H); ctx.stroke();
      ctx.setLineDash([]);
      ctx.fillStyle = '#dc3545'; ctx.font = 'bold 10px system-ui'; ctx.textAlign = 'center';
      ctx.fillText('Auj.', tx, H - 4);
    }

    /* Barres */
    tasks.forEach((task, i) => {
      const y     = 24 + i * ROW_H;
      const color = colorMap[task.statut] || '#6c757d';

      ctx.fillStyle = isDark ? (i%2===0?'#1e1e2e':'#252535') : (i%2===0?'#fff':'#f8f9fa');
      ctx.fillRect(0, y, W, ROW_H);

      ctx.fillStyle = isDark ? '#e9ecef' : '#212529';
      ctx.font = '13px system-ui,sans-serif'; ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
      ctx.fillText(truncate(ctx, task.tache||'—', LABEL_W-PAD*2), PAD, y + ROW_H/2);

      const deb  = new Date(task.date_debut).getTime();
      const fin  = task.date_fin ? new Date(task.date_fin).getTime() : deb + 86400000;
      const bx   = LABEL_W + PAD + ((deb - minTs)/totalMs)*CHART_W;
      const bw   = Math.max(4, ((fin - deb)/totalMs)*CHART_W);

      ctx.shadowColor = 'rgba(0,0,0,.15)'; ctx.shadowBlur = 4; ctx.shadowOffsetY = 1;
      rrect(ctx, bx, y+(ROW_H-BAR_H)/2, bw, BAR_H, 5, color);
      ctx.shadowColor = 'transparent'; ctx.shadowBlur = 0; ctx.shadowOffsetY = 0;

      if (bw > 50) {
        ctx.fillStyle = '#fff'; ctx.font = 'bold 10px system-ui'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        const lbl = (statuts[task.statut]&&statuts[task.statut].label)||task.statut;
        ctx.fillText(lbl, bx+bw/2, y+ROW_H/2);
      }
    });

    ctx.strokeStyle = isDark?'#343a40':'#dee2e6'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(LABEL_W, 0); ctx.lineTo(LABEL_W, H); ctx.stroke();
  };

  function rrect(ctx, x, y, w, h, r, fill) {
    ctx.fillStyle = fill; ctx.beginPath();
    ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y); ctx.quadraticCurveTo(x+w,y,x+w,y+r);
    ctx.lineTo(x+w,y+h-r); ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h);
    ctx.lineTo(x+r,y+h); ctx.quadraticCurveTo(x,y+h,x,y+h-r);
    ctx.lineTo(x,y+r); ctx.quadraticCurveTo(x,y,x+r,y);
    ctx.closePath(); ctx.fill();
  }

  function truncate(ctx, text, maxW) {
    if (ctx.measureText(text).width <= maxW) return text;
    while (text.length > 0 && ctx.measureText(text+'…').width > maxW) text = text.slice(0,-1);
    return text + '…';
  }

  /* ─────────────────────────────────────────────────────
   * 5. REMPLISSAGE CONTACT DEPUIS SELECT
   * ───────────────────────────────────────────────────── */
  window.fillContactFromSelect = function (sel, prefix) {
    const opt  = sel.options[sel.selectedIndex];
    const set  = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    set(prefix+'-contact', opt ? (opt.dataset.nom  || '') : '');
    set(prefix+'-tel',     opt ? (opt.dataset.tel  || '') : '');
    set(prefix+'-mail',    opt ? (opt.dataset.mail || '') : '');
  };

  /* ─────────────────────────────────────────────────────
   * 6. TOTAUX FACTURATION
   * ───────────────────────────────────────────────────── */
  function fmtEur(v) {
    return Number(v||0).toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2})+' €';
  }
  window.updateFcTotal = function () {
    const pu  = parseFloat(document.getElementById('fc-pu')?.value  || 0);
    const qte = parseFloat(document.getElementById('fc-qte')?.value || 1);
    const el  = document.getElementById('fc-total');
    if (el) el.value = fmtEur(pu * qte);
  };
  window.updateFeTotal = function () {
    const pu  = parseFloat(document.getElementById('fe-pu')?.value  || 0);
    const qte = parseFloat(document.getElementById('fe-qte')?.value || 1);
    const el  = document.getElementById('fe-total');
    if (el) el.value = fmtEur(pu * qte);
  };

  /* ─────────────────────────────────────────────────────
   * 7. MODALS — OPEN / FILL
   * ───────────────────────────────────────────────────── */
  const set = (id, val) => { const el=document.getElementById(id); if(el) el.value=val??''; };
  const chk = (id, val) => { const el=document.getElementById(id); if(el) el.checked=!!parseInt(val||0); };
  const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

  window.openBudgetEdit = function (d) {
    set('be-id',d.id); set('be-type',d.type); set('be-cat',d.categorie);
    set('be-scat',d.sous_categorie); set('be-lib',d.libelle);
    set('be-prev',d.previsionnel); set('be-comp',d.comparatif);
    set('be-note',d.note); set('be-four',d.fournisseur); set('be-spon',d.sponsor);
    modal('modalBudgetEdit').show();
  };

  window.openPlanningEdit = function (d) {
    set('pe-id',d.id); set('pe-tache',d.tache); set('pe-statut',d.statut||'wip');
    set('pe-ordre',d.ordre||0);
    set('pe-debut', d.date_debut ? d.date_debut.substring(0,10) : '');
    set('pe-fin',   d.date_fin   ? d.date_fin.substring(0,10)   : '');
    set('pe-note',d.note);
    set('pe-contact',d.contact_id); // <--- LIGNE AJOUTEE ICI !
    modal('modalPlanningEdit').show();
  };

  window.openMaterielEdit = function (d) {
    set('me-id',d.id); set('me-nom',d.nom); set('me-qte',d.quantite);
    set('me-cat',d.categorie_achat||''); set('me-four',d.fournisseur);
    set('me-budget', d.budget!=null ? d.budget : '');
    set('me-din',  d.date_in  ? d.date_in.substring(0,10)  : '');
    set('me-dout', d.date_out ? d.date_out.substring(0,10) : '');
    set('me-comm',d.commentaire);
    modal('modalMaterielEdit').show();
  };

  window.openFacturationEdit = function (d) {
    set('fe-id',d.id); set('fe-cat',d.categorie); set('fe-poste',d.poste);
    set('fe-prest',d.prestataire); set('fe-cont',d.contact);
    set('fe-tel',d.telephone); set('fe-mail',d.mail);
    set('fe-pu',d.prix_unitaire); set('fe-qte',d.quantite); set('fe-note',d.note);
    chk('fe-devis',d.statut_devis); chk('fe-facture',d.statut_facture); chk('fe-virement',d.statut_virement);

    const fichierEx = document.getElementById('fe-fichier-existing');
    const fichierCur = document.getElementById('fe-fichier-current');
    if (fichierEx) fichierEx.value = d.fichier||'';
    if (fichierCur) {
      if (d.fichier) {
        const fname = d.fichier.split('/').pop();
        fichierCur.innerHTML = `<i class="bi bi-paperclip me-1"></i><a href="/${d.fichier}" target="_blank">${fname}</a>`;
      } else {
        fichierCur.innerHTML = '<span class="text-body-secondary">Aucun fichier</span>';
      }
    }
    const csel = document.getElementById('fe-contact-select');
    if (csel) csel.value = d.contact_id || '';

    updateFeTotal();
    modal('modalFacturationEdit').show();
  };

  /* ─────────────────────────────────────────────────────
   * 8. INIT
   * ───────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', () => {
    initTabMemory();
    updateFcTotal();
    updateFeTotal();

    // Initialiser immédiatement avec l'onglet actif (data-restore-tab prioritaire, sinon localStorage, sinon budget)
    const container  = document.getElementById('ops-container');
    const restoreTab = container ? container.dataset.restoreTab : '';
    const cur = restoreTab || localStorage.getItem(TAB_KEY) || '#pane-budget';
    updateActiveTabInputs(cur);

    hookFormSubmits();

    // Observer pour les nouveaux formulaires dans les modals
    const observer = new MutationObserver(hookFormSubmits);
    observer.observe(document.body, { childList: true, subtree: true });

    window.addEventListener('resize', () => {
      if (document.getElementById('planning-gantt-view')?.style.display !== 'none') drawGantt();
      if (document.getElementById('planning-calendar-view')?.style.display !== 'none') { calState.rendered=false; renderCalendar(); }
    });
  });

})();