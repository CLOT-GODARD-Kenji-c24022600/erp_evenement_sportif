/**
 * YES - Your Event Solution
 * JS : Page Staff (filtrage temps réel)
 *
 * @file staff.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  const searchInput = document.getElementById('searchInput');
  const staffCards  = document.querySelectorAll('.staff-card');
  const noResultMsg = document.getElementById('noResultMsg');

  if (!searchInput) return;

  /**
   * Filtre les cartes staff selon le terme saisi.
   */
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

    noResultMsg?.classList.toggle('d-none', visible > 0);
  });

});
