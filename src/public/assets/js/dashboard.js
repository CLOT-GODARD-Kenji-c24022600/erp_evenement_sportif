/**
 * YES – Your Event Solution
 * @file dashboard.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.5
 * @since 2026
 *
 * FIX SPA définitif :
 * - Pas de cloneNode/replaceWith — on utilise des flags data-* pour éviter les doubles listeners
 * - YesPageInit() compatible SPA routeur
 * - Todolist : tri, pagination, filtres, recherche
 * - Planning global : Gantt + Calendrier (unifié avec planning_lignes)
 */

'use strict';

(function () {

  /* ════════════════════════════════════════════════════
   * 1. TODOLIST
   * ════════════════════════════════════════════════════ */
  const ITEMS_PER_PAGE = 8;

  const state = {
    categoryFilter : 'all',
    statusFilter   : 'all',
    search         : '',
    sort           : 'default',
    page           : 1,
  };

  window.openEditModal = function (todo) {
    const fields = {
      'edit-id'      : todo.id          ?? '',
      'edit-title'   : todo.title       ?? '',
      'edit-desc'    : todo.description ?? '',
      'edit-category': todo.category    ?? 'general',
      'edit-priority': todo.priority    ?? 1,
      'edit-status'  : todo.status      ?? 'en_attente',
      'edit-assigned': todo.assigned_to ?? '',
      'edit-duedate' : todo.due_date    ?? '',
      'edit-event'   : todo.event_id    ?? '',
      'edit-projet'  : todo.projet_id   ?? '',
    };
    for (const [id, value] of Object.entries(fields)) {
      const el = document.getElementById(id);
      if (el) el.value = value;
    }
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('modalEditTodo')
    ).show();
  };

  function _pageInit() {
    state.categoryFilter = 'all';
    state.statusFilter   = 'all';
    state.search         = '';
    state.sort           = 'default';
    state.page           = 1;

    const allItems      = Array.from(document.querySelectorAll('.todo-item:not(.todo-item-done)'));
    const doneItems     = Array.from(document.querySelectorAll('.todo-item-done'));
    const doneSection   = document.getElementById('todo-done-section');
    const noResults     = document.getElementById('todo-no-results');
    const paginationNav = document.getElementById('todo-pagination');
    const paginationInfo= document.getElementById('todo-pagination-info');
    const paginationPgs = document.getElementById('todo-pagination-pages');

    if (paginationNav) {
      paginationNav.setAttribute('style', 'display:none;');
    }
    if (doneSection) doneSection.style.display = 'none';

    function applyFilters() {
      const showingDone = state.statusFilter === 'termine';

      let visible = allItems.filter(item => {
        if (showingDone) return false;
        const matchCat    = state.categoryFilter === 'all' || item.dataset.category === state.categoryFilter;
        const matchStatus = state.statusFilter   === 'all' || item.dataset.status   === state.statusFilter;
        const searchVal   = state.search.toLowerCase();
        const matchSearch = searchVal === '' || (item.dataset.title || '').includes(searchVal);
        return matchCat && matchStatus && matchSearch;
      });

      visible = sortItems(visible);
      allItems.forEach(item => item.style.display = 'none');

      const total      = visible.length;
      const totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
      if (state.page > totalPages) state.page = totalPages;

      const start = (state.page - 1) * ITEMS_PER_PAGE;
      const end   = start + ITEMS_PER_PAGE;
      const pageItems = visible.slice(start, end);

      const todoList = document.getElementById('todo-list');
      if (todoList && state.sort !== 'default') {
        pageItems.forEach(item => todoList.appendChild(item));
      }

      pageItems.forEach(item => item.style.display = '');

      if (doneSection) {
        if (showingDone) {
          const visibleDone = doneItems.filter(item => {
            const matchCat    = state.categoryFilter === 'all' || item.dataset.category === state.categoryFilter;
            const searchVal   = state.search.toLowerCase();
            const matchSearch = searchVal === '' || (item.dataset.title || '').includes(searchVal);
            return matchCat && matchSearch;
          });
          doneItems.forEach(item => item.style.display = 'none');
          visibleDone.forEach(item => item.style.display = '');
          doneSection.style.display = visibleDone.length > 0 ? '' : 'none';
        } else {
          doneSection.style.display = 'none';
        }
      }

      const totalVisible = showingDone
        ? doneItems.filter(i => i.style.display !== 'none').length
        : total;
      if (noResults) noResults.style.display = totalVisible === 0 ? '' : 'none';

      renderPagination(total, totalPages, start, end);
    }

    function sortItems(items) {
      const sorted = [...items];
      switch (state.sort) {
        case 'priority-desc':
          return sorted.sort((a, b) =>
            parseInt(b.dataset.priority || '1') - parseInt(a.dataset.priority || '1'));
        case 'priority-asc':
          return sorted.sort((a, b) =>
            parseInt(a.dataset.priority || '1') - parseInt(b.dataset.priority || '1'));
        case 'date-asc':
          return sorted.sort((a, b) =>
            (a.dataset.due || '9999-12-31').localeCompare(b.dataset.due || '9999-12-31'));
        case 'date-desc':
          return sorted.sort((a, b) =>
            (b.dataset.due || '0000-01-01').localeCompare(a.dataset.due || '0000-01-01'));
        default:
          return sorted;
      }
    }

    function renderPagination(total, totalPages, start, end) {
      if (!paginationNav || !paginationInfo || !paginationPgs) return;
      if (total <= ITEMS_PER_PAGE) {
        paginationNav.style.display = 'none';
        return;
      }
      paginationNav.style.display = 'flex';
      paginationInfo.textContent  = `${start + 1}–${Math.min(end, total)} sur ${total} tâches`;
      paginationPgs.innerHTML     = '';

      paginationPgs.appendChild(mkPageBtn('‹', state.page - 1, state.page === 1));
      const delta = 2;
      for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= state.page - delta && i <= state.page + delta)) {
          paginationPgs.appendChild(mkPageBtn(i, i, false, i === state.page));
        } else if (
          (i === state.page - delta - 1 && i > 1) ||
          (i === state.page + delta + 1 && i < totalPages)
        ) {
          const li = document.createElement('li');
          li.className = 'page-item disabled';
          li.innerHTML = '<span class="page-link">…</span>';
          paginationPgs.appendChild(li);
        }
      }
      paginationPgs.appendChild(mkPageBtn('›', state.page + 1, state.page === totalPages));
    }

    function mkPageBtn(label, target, disabled, active = false) {
      const li  = document.createElement('li');
      li.className = `page-item${disabled?' disabled':''}${active?' active':''}`;
      const btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'page-link'; btn.textContent = label;
      if (!disabled) btn.addEventListener('click', () => { state.page = target; applyFilters(); });
      li.appendChild(btn);
      return li;
    }

    document.querySelectorAll('[data-todo-filter]').forEach(btn => {
      const fresh = btn.cloneNode(true);
      btn.parentNode.replaceChild(fresh, btn);
      fresh.addEventListener('click', () => {
        document.querySelectorAll('[data-todo-filter]').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        fresh.classList.add('active');
        fresh.setAttribute('aria-selected', 'true');
        state.categoryFilter = fresh.dataset.todoFilter;
        state.page = 1;
        applyFilters();
      });
    });

    document.querySelectorAll('[data-todo-status-filter]').forEach(btn => {
      const fresh = btn.cloneNode(true);
      btn.parentNode.replaceChild(fresh, btn);
      fresh.addEventListener('click', () => {
        document.querySelectorAll('[data-todo-status-filter]').forEach(b => {
          b.classList.remove('todo-stat-active');
          b.setAttribute('aria-pressed', 'false');
        });
        fresh.classList.add('todo-stat-active');
        fresh.setAttribute('aria-pressed', 'true');
        state.statusFilter = fresh.dataset.todoStatusFilter;
        state.page = 1;
        applyFilters();
      });
    });

    const searchEl = document.getElementById('todo-search');
    if (searchEl) {
      const freshSearch = searchEl.cloneNode(true);
      searchEl.parentNode.replaceChild(freshSearch, searchEl);
      freshSearch.addEventListener('input', function () {
        state.search = this.value.trim();
        state.page   = 1;
        applyFilters();
      });
    }

    const sortEl = document.getElementById('todo-sort');
    if (sortEl) {
      const freshSort = sortEl.cloneNode(true);
      sortEl.parentNode.replaceChild(freshSort, sortEl);
      freshSort.value = 'default';
      freshSort.addEventListener('change', function () {
        state.sort = this.value;
        state.page = 1;
        applyFilters();
      });
    }

    applyFilters();
  }

  document.addEventListener('DOMContentLoaded', _pageInit);
  window.YesPageInit = _pageInit;

  /* ════════════════════════════════════════════════════
   * 2. PLANNING GLOBAL UNIFIÉ — Liste / Gantt / Calendrier
   * ════════════════════════════════════════════════════ */

  const MOIS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin',
                   'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
  const PG_COLORS = { 
    wip:'#ffc107', en_cours:'#0d6efd', valide:'#198754', maj:'#0dcaf0', 
    devis:'#6c757d', visuels:'#6c757d', bat:'#adb5bd', prod:'#6c757d', annule:'#dc3545' 
  };
  const PG_LABELS = { 
    wip:'WIP', en_cours:'En cours', valide:'Validé', maj:'Maj',
    devis:'Devis', visuels:'Visuels', bat:'BAT', prod:'Prod', annule:'Annulé' 
  };

  window.switchPgView = function (view) {
    ['list','gantt','calendar'].forEach(v => {
      const el  = document.getElementById(`pg-${v}-view`);
      const btn = document.getElementById(`pg-btn-${v}`);
      if (el)  el.style.display  = v === view ? 'block' : 'none';
      if (btn) btn.classList.toggle('active', v === view);
    });
    if (view === 'gantt')    setTimeout(drawPgGantt, 80);
    if (view === 'calendar') { pgCal.rendered = false; renderPgCalendar(); }
  };

  window.openPgEdit = function (data) {
    const s = (id, val) => { const el = document.getElementById(id); if (el) el.value = val||''; };
    s('pge-id', data.id); s('pge-tache', data.tache); s('pge-statut', data.statut||'wip');
    s('pge-debut', data.date_debut ? data.date_debut.substring(0,10) : '');
    s('pge-fin',   data.date_fin   ? data.date_fin.substring(0,10)   : '');
    s('pge-event', data.event_id||''); s('pge-projet', data.projet_id||''); s('pge-note', data.note||'');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPgEdit')).show();
  };

  function drawPgGantt() {
    const container = document.getElementById('pg-gantt-container');
    if (!container) return;
    const tasks = (window.PG_DATA||[]).filter(t => t.date_debut && t.date_debut !== '0000-00-00');
    if (!tasks.length) { container.innerHTML='<p class="text-body-secondary text-center py-4">Aucune entrée avec dates.</p>'; return; }
    const allTs=[]; tasks.forEach(t=>{allTs.push(new Date(t.date_debut).getTime());if(t.date_fin)allTs.push(new Date(t.date_fin).getTime());});
    const minTs=Math.min(...allTs),maxTs=Math.max(...allTs),totalMs=Math.max(maxTs-minTs,86400000);
    const RH=38,LW=180,PAD=12,BH=22,W=Math.max(container.clientWidth||700,500),CW=W-LW-PAD*2,H=tasks.length*RH+56;
    container.innerHTML='';
    const cv=document.createElement('canvas'); cv.width=W; cv.height=H; cv.style.cssText='width:100%;display:block;'; container.appendChild(cv);
    const ctx=cv.getContext('2d'),isDark=document.documentElement.getAttribute('data-bs-theme')==='dark';
    ctx.fillStyle=isDark?'#1e1e2e':'#f8f9fa'; ctx.fillRect(0,0,W,H);
    const nd=Math.ceil(totalMs/86400000)+1,ti=Math.max(1,Math.ceil(nd/8));
    ctx.font='11px system-ui,sans-serif'; ctx.textAlign='center';
    for(let d=0;d<=nd;d+=ti){const x=LW+PAD+d*(CW/nd),dt=new Date(minTs+d*86400000);ctx.fillStyle=isDark?'#adb5bd':'#6c757d';ctx.fillText(dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'}),x,14);ctx.strokeStyle=isDark?'#2d2d3d':'#dee2e6';ctx.lineWidth=1;ctx.setLineDash([4,4]);ctx.beginPath();ctx.moveTo(x,20);ctx.lineTo(x,H);ctx.stroke();ctx.setLineDash([]);}
    const to=(Date.now()-minTs)/totalMs;if(to>=0&&to<=1){const tx=LW+PAD+to*CW;ctx.strokeStyle='#dc3545';ctx.lineWidth=2;ctx.setLineDash([5,3]);ctx.beginPath();ctx.moveTo(tx,20);ctx.lineTo(tx,H-12);ctx.stroke();ctx.setLineDash([]);ctx.fillStyle='#dc3545';ctx.font='bold 9px system-ui';ctx.fillText('Auj.',tx,H-2);}
    tasks.forEach((t,i)=>{
      const y=24+i*RH, c=PG_COLORS[t.statut]||'#0d6efd';
      ctx.fillStyle=isDark?(i%2===0?'#1e1e2e':'#252535'):(i%2===0?'#fff':'#f8f9fa');ctx.fillRect(0,y,W,RH);
      ctx.fillStyle=isDark?'#e9ecef':'#212529';ctx.font='12px system-ui,sans-serif';ctx.textAlign='left';ctx.textBaseline='middle';
      ctx.fillText(_tr(ctx,t.tache||'—',LW-PAD*2),PAD,y+RH/2);
      const db=new Date(t.date_debut).getTime(),fn=t.date_fin?new Date(t.date_fin).getTime():db+86400000,bx=LW+PAD+((db-minTs)/totalMs)*CW,bw=Math.max(4,((fn-db)/totalMs)*CW);
      ctx.shadowColor='rgba(0,0,0,.12)';ctx.shadowBlur=4;ctx.shadowOffsetY=2;_rr(ctx,bx,y+(RH-BH)/2,bw,BH,5,c);
      ctx.shadowColor='transparent';ctx.shadowBlur=0;ctx.shadowOffsetY=0;
      if(bw>40){ctx.fillStyle='#fff';ctx.font='bold 9px system-ui';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(PG_LABELS[t.statut]||t.statut,bx+bw/2,y+RH/2);}
    });
    ctx.strokeStyle=isDark?'#343a40':'#dee2e6';ctx.lineWidth=1;ctx.beginPath();ctx.moveTo(LW,0);ctx.lineTo(LW,H);ctx.stroke();
  }

  const pgCal={year:new Date().getFullYear(),month:new Date().getMonth(),rendered:false};
  window.pgCalNav=function(d){pgCal.month+=d;if(pgCal.month>11){pgCal.month=0;pgCal.year++;}if(pgCal.month<0){pgCal.month=11;pgCal.year--;}pgCal.rendered=false;renderPgCalendar();};

  function renderPgCalendar(){
    const grid=document.getElementById('pg-cal-grid'),titleEl=document.getElementById('pg-cal-title');
    if(!grid)return;const{year,month}=pgCal;if(titleEl)titleEl.textContent=`${MOIS_FR[month]} ${year}`;
    const tasks=window.PG_DATA||[],isDark=document.documentElement.getAttribute('data-bs-theme')==='dark',today=new Date();
    const fd=new Date(year,month,1).getDay(),off=fd===0?6:fd-1,dim=new Date(year,month+1,0).getDate();
    grid.innerHTML='';
    for(let i=0;i<off;i++){const e=document.createElement('div');e.style.cssText='min-height:80px;';grid.appendChild(e);}
    for(let day=1;day<=dim;day++){
      const ds=`${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`,dts=new Date(ds).getTime();
      const isT=today.getFullYear()===year&&today.getMonth()===month&&today.getDate()===day;
      const dt=tasks.filter(t=>{if(!t.date_debut)return false;const db=new Date(t.date_debut.substring(0,10)).getTime(),fn=t.date_fin?new Date(t.date_fin.substring(0,10)).getTime():db;return dts>=db&&dts<=fn;});
      const cell=document.createElement('div');
      cell.style.cssText=`min-height:80px;border-radius:8px;padding:5px;background:${isT?(isDark?'#1a3a5c':'#e8f4fd'):(isDark?'#252535':'#fff')};border:${isT?'2px solid #0d6efd':'1px solid '+(isDark?'#343a40':'#dee2e6')};cursor:${dt.length?'pointer':'default'};overflow:hidden;transition:background .15s;`;
      const num=document.createElement('div');num.style.cssText=`font-size:12px;font-weight:${isT?'bold':'500'};color:${isT?'#0d6efd':(isDark?'#e9ecef':'#212529')};margin-bottom:3px;`;num.textContent=day;cell.appendChild(num);
      dt.slice(0,2).forEach(t=>{
        const bg=PG_COLORS[t.statut]||'#0d6efd';
        const p=document.createElement('div');p.style.cssText=`background:${bg}33;border-left:3px solid ${bg};border-radius:3px;padding:1px 4px;margin-bottom:2px;font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:${isDark?'#e9ecef':'#212529'};font-weight:500;`;p.textContent=t.tache||'—';cell.appendChild(p);
      });
      if(dt.length>2){const m=document.createElement('div');m.style.cssText='font-size:9px;color:#6c757d;font-style:italic;';m.textContent=`+${dt.length-2} autre${dt.length-2>1?'s':''}`;cell.appendChild(m);}
      if(dt.length)cell.addEventListener('click',()=>showPgDay(day,ds,dt));
      grid.appendChild(cell);
    }
    const det=document.getElementById('pg-cal-detail');if(det)det.style.display='none';pgCal.rendered=true;
  }

  function showPgDay(day,ds,tasks){
    const box=document.getElementById('pg-cal-detail'),title=document.getElementById('pg-cal-detail-title'),list=document.getElementById('pg-cal-detail-list');
    if(!box||!title||!list)return;
    const d=new Date(ds),jours=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    title.innerHTML=`<i class="bi bi-calendar3 me-2 text-primary"></i>${jours[d.getDay()]} ${day} ${MOIS_FR[pgCal.month]} ${pgCal.year}`;
    list.innerHTML='';
    tasks.forEach(t=>{
      const c=PG_COLORS[t.statut]||'#0d6efd',l=PG_LABELS[t.statut]||t.statut;
      const li=document.createElement('li');li.className='list-group-item d-flex justify-content-between align-items-center py-2';
      li.innerHTML=`<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${c};margin-right:8px;"></span><strong>${_esc(t.tache||'—')}</strong>${t.note?`<br><small class="text-body-secondary ms-4">${_esc(t.note)}</small>`:''}</div><span class="badge rounded-pill" style="background:${c};">${_esc(l)}</span>`;
      list.appendChild(li);
    });
    box.style.display='block';box.scrollIntoView({behavior:'smooth',block:'nearest'});
  }

  function _rr(ctx,x,y,w,h,r,f){ctx.fillStyle=f;ctx.beginPath();ctx.moveTo(x+r,y);ctx.lineTo(x+w-r,y);ctx.quadraticCurveTo(x+w,y,x+w,y+r);ctx.lineTo(x+w,y+h-r);ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h);ctx.lineTo(x+r,y+h);ctx.quadraticCurveTo(x,y+h,x,y+h-r);ctx.lineTo(x,y+r);ctx.quadraticCurveTo(x,y,x+r,y);ctx.closePath();ctx.fill();}
  function _tr(ctx,text,maxW){if(ctx.measureText(text).width<=maxW)return text;while(text.length>0&&ctx.measureText(text+'…').width>maxW)text=text.slice(0,-1);return text+'…';}
  function _esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

  window.addEventListener('resize',()=>{const gv=document.getElementById('pg-gantt-view');if(gv&&gv.style.display!=='none')drawPgGantt();});

})();