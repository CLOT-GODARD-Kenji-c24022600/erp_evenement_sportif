/**
 * YES - Your Event Solution
 * JS : Recherche dynamique dans le header
 *
 * @file search.js
 * @version 2.0  –  2026
 * AJOUTS : todos, budget, contacts dans les résultats
 */

'use strict';

const YesSearch = (() => {

  let debounceTimer = null;

  function init() {
    const input    = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('searchDropdown');

    if (!input || !dropdown) return;

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        close(dropdown, input);
      }
    });

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const term = input.value.trim();
      if (term.length < 2) { close(dropdown, input); return; }
      debounceTimer = setTimeout(() => fetchResults(term, input, dropdown), 220);
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') { close(dropdown, input); return; }
      if (e.key === 'Enter') {
        const term = input.value.trim();
        if (term.length >= 2) window.location.href = `/recherche?q=${encodeURIComponent(term)}`;
        return;
      }
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        const items = [...dropdown.querySelectorAll('a[role="option"]')];
        const current = dropdown.querySelector('a[role="option"].active');
        let idx = items.indexOf(current);
        if (e.key === 'ArrowDown') idx = Math.min(idx + 1, items.length - 1);
        else idx = Math.max(idx - 1, 0);
        items.forEach(i => i.classList.remove('active'));
        if (items[idx]) { items[idx].classList.add('active'); items[idx].focus(); }
      }
    });
  }

  function close(dropdown, input) {
    dropdown.classList.add('d-none');
    input.setAttribute('aria-expanded', 'false');
  }

  async function fetchResults(term, input, dropdown) {
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
      renderDropdown(await res.json(), dropdown, term, input);
    } catch (_) {
      dropdown.classList.add('d-none');
    }
  }

  function highlight(str, term) {
    if (!str) return '';
    const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return String(str).replace(new RegExp(`(${escaped})`, 'gi'),
      '<mark class="px-0 bg-warning-subtle fw-semibold">$1</mark>');
  }

  function fuzzyMatch(label, term) {
    const tokens = term.toLowerCase().split(/\s+/);
    const haystack = label.toLowerCase();
    return tokens.every(tok => haystack.includes(tok));
  }

  function renderDropdown(data, dropdown, term, input) {
    let html  = '';
    let total = 0;

    const addSection = (items, icon, label, buildRow, filterFn) => {
      const list = (items ?? []).filter(filterFn ?? (() => true));
      if (!list.length) return;
      total += list.length;
      html += `<p class="px-3 pt-2 pb-1 mb-0 small text-muted fw-bold text-uppercase section-header"
                  style="font-size:.68rem;letter-spacing:.05em;">${icon} ${label}</p>`;
      list.forEach(item => { html += buildRow(item); });
    };

    const row = (href, icon, color, main, sub = '') =>
      `<a href="${href}" role="option" tabindex="-1"
          class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-body search-result-item">
         <i class="bi ${icon} ${color} flex-shrink-0" aria-hidden="true"></i>
         <span class="overflow-hidden">
           <span class="d-block text-truncate">${main}</span>
           ${sub ? `<small class="text-muted d-block text-truncate">${sub}</small>` : ''}
         </span>
       </a>`;

    // ── Staff ──────────────────────────────────────────────
    addSection(data.staff, '👤', 'Staff', (u) =>
      row('/staff', 'bi-person-fill', 'text-primary',
          highlight(`${u.prenom} ${u.nom}`.trim(), term),
          u.sub ? highlight(u.sub, term) : ''),
      (u) => fuzzyMatch(`${u.prenom} ${u.nom} ${u.sub ?? ''}`, term)
    );

    // ── Événements ────────────────────────────────────────
    addSection(data.events, '📅', 'Événements', (ev) =>
      row(`/gerer_event?id=${ev.id}`, 'bi-calendar-event', 'text-success',
          highlight(ev.nom, term), ev.sub ?? ''),
      (ev) => fuzzyMatch(`${ev.nom} ${ev.sub ?? ''}`, term)
    );

    // ── Projets ───────────────────────────────────────────
    addSection(data.projets, '📁', 'Projets', (p) =>
      row(`/projet_detail?id=${p.id}`, 'bi-folder2-open', 'text-warning',
          highlight(p.nom, term), p.sub ?? ''),
      (p) => fuzzyMatch(`${p.nom} ${p.sub ?? ''}`, term)
    );

    // ── Tâches ────────────────────────────────────────────
    addSection(data.todos, '✅', 'Tâches', (t) => {
      const statutColors = { en_attente: 'text-secondary', en_cours: 'text-primary', termine: 'text-success' };
      const icon = t.statut === 'termine' ? 'bi-check-circle-fill' : (t.statut === 'en_cours' ? 'bi-play-circle-fill' : 'bi-hourglass');
      return row('/dashboard', icon, statutColors[t.statut] ?? 'text-secondary',
        highlight(t.titre, term), t.sub ?? '');
    });

    // ── Budget ────────────────────────────────────────────
    addSection(data.budget, '💰', 'Budget', (b) => {
      const href = b.event_id
        ? `/operationnel?event_id=${b.event_id}&tab=pane-budget`
        : (b.projet_id ? `/operationnel?projet_id=${b.projet_id}&tab=pane-budget` : '/operationnel');
      const typeIcon = b.type === 'produit' ? 'bi-arrow-up-circle text-success' : 'bi-arrow-down-circle text-danger';
      return row(href, typeIcon, '',
        highlight(b.libelle, term),
        `${b.montant}${b.sub ? ' · ' + b.sub : ''}`);
    });

    // ── Contacts ──────────────────────────────────────────
    addSection(data.contacts, '📋', 'Contacts', (c) =>
      row('/annuaire', 'bi-person-lines-fill', 'text-info',
          highlight(c.nom, term),
          [c.sub, c.tel, c.mail].filter(Boolean).join(' · ')),
      (c) => fuzzyMatch(`${c.nom} ${c.sub ?? ''} ${c.mail ?? ''} ${c.tel ?? ''}`, term)
    );

    // ── Aucun résultat ────────────────────────────────────
    if (total === 0) {
      html = `<p class="px-3 py-3 mb-0 text-muted small text-center">
                <i class="bi bi-emoji-frown me-1"></i>Aucun résultat pour « ${term} »
              </p>`;
    } else {
      html += `<div class="border-top px-3 py-2 text-center">
        <a href="/recherche?q=${encodeURIComponent(term)}"
           class="small text-primary text-decoration-none">
          Voir tous les résultats →
        </a>
      </div>`;
    }

    dropdown.innerHTML = html;
    // Retirer la bordure du premier séparateur
    dropdown.querySelector('.section-header')?.style.setProperty('border-top', 'none');

    dropdown.querySelectorAll('a[role="option"]').forEach(link => {
      link.addEventListener('mousedown', (e) => e.preventDefault());
      link.addEventListener('click', (e) => {
        close(dropdown, input);
        input.value = '';
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