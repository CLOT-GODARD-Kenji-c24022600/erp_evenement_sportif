/**
 * YES - Your Event Solution
 * JS : Formulaire événement (validation dates, Bootstrap validation)
 *
 * @file events.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

(() => {
  'use strict';

  function _init() {
    // ── Validation Bootstrap ────────────────────────────
    const form = document.querySelector('.needs-validation');
    if (form && !form.dataset.bound) {
      form.addEventListener('submit', (e) => {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
      form.dataset.bound = '1';
    }

    // ── Logique intelligente des dates ─────────────────
    const dateDebut = document.getElementById('date_debut');
    const dateFin   = document.getElementById('date_fin');

    if (dateDebut && dateFin && !dateDebut.dataset.bound) {
      dateDebut.addEventListener('change', () => {
        if (dateDebut.value) {
          dateFin.disabled = false;
          dateFin.min      = dateDebut.value;

          // Réinitialise la date de fin si elle est avant la date de début
          if (dateFin.value && dateFin.value < dateDebut.value) {
            dateFin.value = '';
          }
        } else {
          dateFin.disabled = true;
          dateFin.value    = '';
        }
      });
      dateDebut.dataset.bound = '1';
    }
  }

  // ── SPA entry-point & Chargement sécurisé ────────────────────
  window.YesPageInit = _init;

  const currentScript = document.currentScript;
  if (!currentScript || currentScript.dataset.spaPage !== '1') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', window.YesPageInit);
    } else {
      requestAnimationFrame(window.YesPageInit);
    }
  }

})();