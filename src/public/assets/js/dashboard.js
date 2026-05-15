/**
 * YES - Your Event Solution
 * JS : Dashboard (filtres todo, tri, recherche, pagination, modale édition)
 *
 * @file dashboard.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.0
 * @since 2026
 */

'use strict';

// ── Constantes ────────────────────────────────────────────
const ITEMS_PER_PAGE = 8;

// ── État global des filtres ────────────────────────────────
const state = {
  categoryFilter : 'all',
  statusFilter   : 'all',
  search         : '',
  sort           : 'default',
  page           : 1,
};

// ── Ouvre la modale d'édition ──────────────────────────────
function openEditModal(todo) {
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
}

// ── Logique principale ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

  const allItems      = Array.from(document.querySelectorAll('.todo-item:not(.todo-item-done)'));
  const doneItems     = Array.from(document.querySelectorAll('.todo-item-done'));
  const doneSection   = document.getElementById('todo-done-section');
  const noResults     = document.getElementById('todo-no-results');
  const paginationNav = document.getElementById('todo-pagination');
  const paginationInfo  = document.getElementById('todo-pagination-info');
  const paginationPages = document.getElementById('todo-pagination-pages');

  // La section terminées est masquée par défaut au chargement
  if (doneSection) doneSection.style.display = 'none';

  // ── Applique tous les filtres + tri + pagination ──────────
  function applyFilters() {
    const showingDone = state.statusFilter === 'termine';

    // ── Tâches actives (non terminées) ──────────────────────
    let visible = allItems.filter(item => {
      // Si on filtre sur "terminées", on masque tout le bloc actif
      if (showingDone) return false;
      const matchCat    = state.categoryFilter === 'all' || item.dataset.category === state.categoryFilter;
      const matchStatus = state.statusFilter   === 'all' || item.dataset.status   === state.statusFilter;
      const matchSearch = state.search === ''  || item.dataset.title.includes(state.search.toLowerCase());
      return matchCat && matchStatus && matchSearch;
    });

    // 2. Trier
    visible = sortItems(visible, state.sort);

    // 3. Masquer tout d'abord
    allItems.forEach(item => item.style.display = 'none');

    // 4. Pagination
    const total      = visible.length;
    const totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
    if (state.page > totalPages) state.page = totalPages;

    const start     = (state.page - 1) * ITEMS_PER_PAGE;
    const end       = start + ITEMS_PER_PAGE;
    const pageItems = visible.slice(start, end);

    pageItems.forEach(item => item.style.display = '');

    // ── Tâches terminées ────────────────────────────────────
    if (doneSection) {
      if (showingDone) {
        // Filtrer les terminées par catégorie + recherche
        let visibleDone = doneItems.filter(item => {
          const matchCat    = state.categoryFilter === 'all' || item.dataset.category === state.categoryFilter;
          const matchSearch = state.search === ''  || item.dataset.title.includes(state.search.toLowerCase());
          return matchCat && matchSearch;
        });

        doneItems.forEach(item => item.style.display = 'none');
        visibleDone.forEach(item => item.style.display = '');

        doneSection.style.display = visibleDone.length > 0 ? '' : 'none';
      } else {
        // Masquer la section terminées quand on n'est pas sur ce filtre
        doneSection.style.display = 'none';
      }
    }

    // 5. Message aucun résultat
    const totalVisible = showingDone
      ? doneItems.filter(i => i.style.display !== 'none').length
      : total;
    if (noResults) noResults.style.display = totalVisible === 0 ? '' : 'none';

    // 6. Pagination UI (uniquement pour les tâches actives)
    renderPagination(total, totalPages, start, end);
  }

  // ── Tri ───────────────────────────────────────────────────
  function sortItems(items, sortValue) {
    const sorted = [...items];
    switch (sortValue) {
      case 'priority-desc':
        return sorted.sort((a, b) => parseInt(b.dataset.priority) - parseInt(a.dataset.priority));
      case 'priority-asc':
        return sorted.sort((a, b) => parseInt(a.dataset.priority) - parseInt(b.dataset.priority));
      case 'date-asc':
        return sorted.sort((a, b) => {
          const da = a.dataset.due || '9999-12-31';
          const db = b.dataset.due || '9999-12-31';
          return da.localeCompare(db);
        });
      case 'date-desc':
        return sorted.sort((a, b) => {
          const da = a.dataset.due || '0000-01-01';
          const db = b.dataset.due || '0000-01-01';
          return db.localeCompare(da);
        });
      default:
        return sorted;
    }
  }

  // ── Rendu pagination ──────────────────────────────────────
  function renderPagination(total, totalPages, start, end) {
    if (!paginationNav || !paginationInfo || !paginationPages) return;

    if (total <= ITEMS_PER_PAGE) {
      paginationNav.style.display = 'none';
      return;
    }

    paginationNav.style.display = '';
    paginationInfo.textContent  = `${start + 1}–${Math.min(end, total)} sur ${total} tâches`;

    paginationPages.innerHTML = '';

    // Bouton précédent
    const prev = createPageBtn('‹', state.page - 1, state.page === 1);
    paginationPages.appendChild(prev);

    // Pages numérotées (max 5 boutons autour de la page courante)
    const delta = 2;
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= state.page - delta && i <= state.page + delta)) {
        paginationPages.appendChild(createPageBtn(i, i, false, i === state.page));
      } else if (
        (i === state.page - delta - 1 && i > 1) ||
        (i === state.page + delta + 1 && i < totalPages)
      ) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        li.innerHTML = '<span class="page-link">…</span>';
        paginationPages.appendChild(li);
      }
    }

    // Bouton suivant
    const next = createPageBtn('›', state.page + 1, state.page === totalPages);
    paginationPages.appendChild(next);
  }

  function createPageBtn(label, targetPage, disabled, active = false) {
    const li  = document.createElement('li');
    li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
    const btn = document.createElement('button');
    btn.type      = 'button';
    btn.className = 'page-link';
    btn.textContent = label;
    if (!disabled) {
      btn.addEventListener('click', () => {
        state.page = targetPage;
        applyFilters();
      });
    }
    li.appendChild(btn);
    return li;
  }

  // ── Filtres catégorie (nav pills) ─────────────────────────
  document.querySelectorAll('[data-todo-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-todo-filter]').forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      state.categoryFilter = btn.dataset.todoFilter;
      state.page = 1;
      applyFilters();
    });
  });

  // ── Filtres statut (cartes cliquables) ────────────────────
  document.querySelectorAll('[data-todo-status-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-todo-status-filter]').forEach(b => {
        b.classList.remove('todo-stat-active');
        b.setAttribute('aria-pressed', 'false');
      });
      btn.classList.add('todo-stat-active');
      btn.setAttribute('aria-pressed', 'true');
      state.statusFilter = btn.dataset.todoStatusFilter;
      state.page = 1;
      applyFilters();
    });
  });

  // ── Recherche ─────────────────────────────────────────────
  const searchInput = document.getElementById('todo-search');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      state.search = searchInput.value.trim();
      state.page   = 1;
      applyFilters();
    });
  }

  // ── Tri ───────────────────────────────────────────────────
  const sortSelect = document.getElementById('todo-sort');
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      state.sort = sortSelect.value;
      state.page = 1;
      applyFilters();
    });
  }

  // ── Init ──────────────────────────────────────────────────
  applyFilters();

});