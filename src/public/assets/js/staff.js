/**
 * YES - Your Event Solution
 * JS : Page Staff (filtrage temps réel)
 *
 * @file staff.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

(() => {
  'use strict';

  function _pageInit() {
    const searchInput = document.getElementById('searchInput');
    const staffCards  = document.querySelectorAll('.staff-card');
    const noResultMsg = document.getElementById('noResultMsg');

    if (!searchInput || searchInput.dataset.bound) return;

    searchInput.addEventListener('input', () => {
      const term    = searchInput.value.toLowerCase().trim();
      let   visible = 0;

      staffCards.forEach(card => {
        const name  = card.querySelector('.staff-name')?.textContent.toLowerCase()  ?? '';
        const poste = card.querySelector('.staff-poste')?.textContent.toLowerCase() ?? '';
        const match = name.includes(term) || poste.includes(term);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      noResultMsg?.classList.toggle('d-none', visible === 0);
    });
    
    searchInput.dataset.bound = '1';
  }

  // ── SPA entry-point & Chargement sécurisé ────────────────────
  window.YesPageInit = _pageInit;

  const currentScript = document.currentScript;
  if (!currentScript || currentScript.dataset.spaPage !== '1') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', window.YesPageInit);
    } else {
      requestAnimationFrame(window.YesPageInit);
    }
  }

})();