/**
 * YES - Your Event Solution
 * JS : Recherche dynamique dans le header
 *
 * @file search.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

const YesSearch = (() => {

  let debounceTimer = null;

  function init() {
    const input    = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('searchDropdown');

    if (!input || !dropdown) return;

    // Ferme le dropdown si on clique ailleurs
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('d-none');
        input.setAttribute('aria-expanded', 'false');
      }
    });

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const term = input.value.trim();

      if (term.length < 1) {
        dropdown.classList.add('d-none');
        input.setAttribute('aria-expanded', 'false');
        return;
      }

      // Debounce 220ms pour ne pas spammer le serveur
      debounceTimer = setTimeout(() => fetchResults(term, input, dropdown), 220);
    });

    // Navigation clavier dans le dropdown
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        dropdown.classList.add('d-none');
        input.setAttribute('aria-expanded', 'false');
      }
    });
  }

  async function fetchResults(term, input, dropdown) {
    try {
      const res  = await fetch(`/api_search?q=${encodeURIComponent(term)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) return;

      const data = await res.json();
      renderDropdown(data, dropdown, term);
      dropdown.classList.remove('d-none');
      input.setAttribute('aria-expanded', 'true');
    } catch (_) {
      // Silencieux
    }
  }

  function renderDropdown(data, dropdown, term) {
    const highlight = (str) => {
      const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      return str.replace(new RegExp(`(${escaped})`, 'gi'),
        '<mark class="px-0 bg-warning-subtle">$1</mark>');
    };

    let html = '';

    if (data.staff.length > 0) {
      html += `<p class="px-3 pt-2 pb-1 mb-0 small text-muted fw-bold text-uppercase" style="font-size:.7rem">Staff</p>`;
      data.staff.forEach(u => {
        const name = highlight(`${u.prenom} ${u.nom}`);
        const poste = u.poste ? `<small class="text-muted"> — ${highlight(u.poste)}</small>` : '';
        html += `
          <a href="/staff" role="option"
             class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
            <i class="bi bi-person-fill text-primary" aria-hidden="true"></i>
            <span>${name}${poste}</span>
          </a>`;
      });
    }

    if (data.events.length > 0) {
      html += `<p class="px-3 pt-2 pb-1 mb-0 small text-muted fw-bold text-uppercase border-top" style="font-size:.7rem">Événements</p>`;
      data.events.forEach(ev => {
        html += `
          <a href="/gerer_event?id=${ev.id}" role="option"
             class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
            <i class="bi bi-calendar-event text-success" aria-hidden="true"></i>
            <span>${highlight(ev.nom)}<small class="text-muted"> — ${highlight(ev.lieu)}</small></span>
          </a>`;
      });
    }

    if (data.projets && data.projets.length > 0) {
      html += `<p class="px-3 pt-2 pb-1 mb-0 small text-muted fw-bold text-uppercase border-top" style="font-size:.7rem">Projets</p>`;
      data.projets.forEach(p => {
        html += `
          <a href="/dashboard" role="option"
             class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
            <i class="bi bi-folder2-open text-warning" aria-hidden="true"></i>
            <span>${highlight(p.nom)}</span>
          </a>`;
      });
    }

    if (!html) {
      html = `<p class="px-3 py-3 mb-0 text-muted small text-center">Aucun résultat pour « ${term} »</p>`;
    }

    dropdown.innerHTML = html;

    // Les clics dans le dropdown naviguent via le SPA router
    dropdown.querySelectorAll('a[href]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        dropdown.classList.add('d-none');
        document.getElementById('globalSearchInput').value = '';
        YesRouter.navigate(link.getAttribute('href'));
      });
    });
  }

  return { init };
})();

document.addEventListener('DOMContentLoaded', () => YesSearch.init());