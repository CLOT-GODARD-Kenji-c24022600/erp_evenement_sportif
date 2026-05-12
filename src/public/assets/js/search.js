/**
 * YES - Your Event Solution
 * JS : Recherche dynamique dans le header
 *
 * @file search.js
 * @version 1.1
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
        close(dropdown, input);
      }
    });

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const term = input.value.trim();

      if (term.length < 2) {
        close(dropdown, input);
        return;
      }

      // Debounce 220ms pour ne pas spammer le serveur
      debounceTimer = setTimeout(() => fetchResults(term, input, dropdown), 220);
    });

    // Navigation clavier
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        close(dropdown, input);
        return;
      }
      if (e.key === 'Enter') {
        // Fallback : soumettre une recherche pleine page
        const term = input.value.trim();
        if (term.length >= 2) {
          window.location.href = `/recherche?q=${encodeURIComponent(term)}`;
        }
        return;
      }
      // Navigation ↑↓ dans les résultats
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        const items = [...dropdown.querySelectorAll('a[role="option"]')];
        const current = dropdown.querySelector('a[role="option"].active');
        let idx = items.indexOf(current);
        if (e.key === 'ArrowDown') idx = Math.min(idx + 1, items.length - 1);
        else idx = Math.max(idx - 1, 0);
        items.forEach(i => i.classList.remove('active'));
        if (items[idx]) {
          items[idx].classList.add('active');
          items[idx].focus();
        }
      }
    });
  }

  function close(dropdown, input) {
    dropdown.classList.add('d-none');
    input.setAttribute('aria-expanded', 'false');
  }

  async function fetchResults(term, input, dropdown) {
    // Indicateur de chargement
    dropdown.innerHTML = `<p class="px-3 py-2 mb-0 text-muted small text-center">
      <span class="spinner-border spinner-border-sm me-1"></span> Recherche…
    </p>`;
    dropdown.classList.remove('d-none');
    input.setAttribute('aria-expanded', 'true');

    try {
      const res = await fetch(`/ajax_search?q=${encodeURIComponent(term)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) { dropdown.classList.add('d-none'); return; }

      const data = await res.json();
      renderDropdown(data, dropdown, term, input);
    } catch (_) {
      dropdown.classList.add('d-none');
    }
  }

  function highlight(str, term) {
    if (!str) return '';
    const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return String(str).replace(
      new RegExp(`(${escaped})`, 'gi'),
      '<mark class="px-0 bg-warning-subtle fw-semibold">$1</mark>'
    );
  }

  /**
   * Matching intelligent : "samu" → "samuel"
   * Tokenise le terme et vérifie que chaque token apparaît dans le nom complet
   */
  function fuzzyMatch(label, term) {
    const tokens = term.toLowerCase().split(/\s+/);
    const haystack = label.toLowerCase();
    return tokens.every(tok => haystack.includes(tok));
  }

  function renderDropdown(data, dropdown, term, input) {
    let html = '';
    let total = 0;

    const addSection = (items, icon, colorClass, label, buildRow) => {
      // Filtre côté client pour matching intelligent (le serveur renvoie déjà LIKE %term%)
      const filtered = items.filter(item => {
        const fullLabel = `${item.prenom ?? ''} ${item.nom ?? ''} ${item.sub ?? ''}`.trim();
        return fuzzyMatch(fullLabel, term);
      });
      if (filtered.length === 0) return;
      total += filtered.length;
      html += `<p class="px-3 pt-2 pb-1 mb-0 small text-muted fw-bold text-uppercase border-top first:border-0"
                  style="font-size:.68rem;letter-spacing:.05em">${icon} ${label}</p>`;
      filtered.forEach(item => { html += buildRow(item); });
    };

    // --- Staff ---
    addSection(data.staff ?? [], '👤', 'text-primary', 'Staff', (u) => {
      const name = highlight(`${u.prenom} ${u.nom}`.trim(), term);
      const sub  = u.sub ? `<small class="text-muted ms-1">— ${highlight(u.sub, term)}</small>` : '';
      return `<a href="/staff" role="option" tabindex="-1"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
                <i class="bi bi-person-fill text-primary flex-shrink-0" aria-hidden="true"></i>
                <span>${name}${sub}</span>
              </a>`;
    });

    // --- Événements ---
    addSection(data.events ?? [], '📅', 'text-success', 'Événements', (ev) => {
      const sub = ev.sub ? `<small class="text-muted ms-1">— ${ev.sub}</small>` : '';
      return `<a href="/gerer_event?id=${ev.id}" role="option" tabindex="-1"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
                <i class="bi bi-calendar-event text-success flex-shrink-0" aria-hidden="true"></i>
                <span>${highlight(ev.nom, term)}${sub}</span>
              </a>`;
    });

    // --- Projets ---
    addSection(data.projets ?? [], '📁', 'text-warning', 'Projets', (p) => {
      const sub = p.sub ? `<small class="text-muted ms-1">— ${p.sub}</small>` : '';
      return `<a href="/projet_detail?id=${p.id}" role="option" tabindex="-1"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
                <i class="bi bi-folder2-open text-warning flex-shrink-0" aria-hidden="true"></i>
                <span>${highlight(p.nom, term)}${sub}</span>
              </a>`;
    });

    // Aucun résultat
    if (total === 0) {
      html = `<p class="px-3 py-3 mb-0 text-muted small text-center">
                <i class="bi bi-emoji-frown me-1"></i>Aucun résultat pour « ${term} »
              </p>`;
    } else {
      // Lien "Voir tous les résultats"
      html += `<div class="border-top px-3 py-2 text-center">
        <a href="/recherche?q=${encodeURIComponent(term)}"
           class="small text-primary text-decoration-none">
          Voir tous les résultats →
        </a>
      </div>`;
    }

    // Supprimer la bordure du premier séparateur
    dropdown.innerHTML = html;
    dropdown.querySelector('.border-top')?.classList.remove('border-top');

    // Clic sur un lien résultat
    dropdown.querySelectorAll('a[role="option"]').forEach(link => {
      link.addEventListener('mousedown', (e) => {
        // mousedown avant blur pour capturer le clic
        e.preventDefault();
      });
      link.addEventListener('click', (e) => {
        close(dropdown, input);
        input.value = '';
        // Si YesRouter est disponible (SPA), on l'utilise, sinon navigation normale
        if (typeof YesRouter !== 'undefined') {
          e.preventDefault();
          YesRouter.navigate(link.getAttribute('href'));
        }
      });
    });
  }

  return { init };
})();

document.addEventListener('DOMContentLoaded', () => YesSearch.init());