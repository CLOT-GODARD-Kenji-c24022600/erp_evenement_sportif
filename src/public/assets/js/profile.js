/**
 * YES - Your Event Solution
 * JS : Page profil (aperçu avatar avant upload)
 *
 * @file profile.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

(() => {
  'use strict';

  function _init() {
    const avatarInput = document.getElementById('avatar-upload');
    if (!avatarInput || avatarInput.dataset.bound) return;

    /**
     * Affiche un aperçu de l'image sélectionnée avant l'upload.
     */
    avatarInput.addEventListener('change', () => {
      const file = avatarInput.files?.[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        const preview = document.querySelector('.profile-avatar, .profile-avatar-placeholder');
        if (preview) {
          const img = document.createElement('img');
          img.src             = e.target.result;
          img.alt             = 'Aperçu avatar';
          img.className       = 'rounded-circle shadow profile-avatar';
          img.style.objectFit = 'cover';
          preview.replaceWith(img);
        }
      };
      reader.readAsDataURL(file);
    });

    avatarInput.dataset.bound = '1';
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