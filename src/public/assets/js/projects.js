'use strict';

document.addEventListener('DOMContentLoaded', () => {

  // ── Pré-remplissage modale Modifier ──────────────────────────
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

  // ── Pré-remplissage modale Supprimer ─────────────────────────
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

  // ── Recherche dynamique AJAX ──────────────────────────────────
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
        a.className = 'suggestion-item';
        a.href = urlFn(item);
        a.setAttribute('role', 'option');
        a.innerHTML = `<i class="bi bi-${icon} text-${color}"></i>
                       <span class="fw-medium small">${item.nom ?? (item.prenom + ' ' + item.nom_famille)}</span>
                       ${item.sub ? `<span class="text-body-secondary small ms-auto">${item.sub}</span>` : ''}`;
        a.addEventListener('mousedown', (ev) => ev.preventDefault());
        wrap.appendChild(a);
      });
    };

    addSection('Projets',    'kanban-fill',         'primary', data.projets, (p)  => `/projet_detail?id=${p.id}`);
    addSection('Événements', 'calendar-event-fill', 'warning', data.events,  (ev) => `/gerer_event?id=${ev.id}`);
    addSection('Staff',      'person-fill',         'info',    data.staff,   ()   => `/staff`);

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
        const res  = await fetch(`/ajax_search&q=${encodeURIComponent(q)}`);
        if (!res.ok) return;
        buildSuggestions(await res.json());
      } catch { /* réseau indisponible */ }
    }, 220);
  });

  searchInput.addEventListener('blur',    () => setTimeout(removeSuggestions, 150));
  searchInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') { removeSuggestions(); searchInput.blur(); } });
});