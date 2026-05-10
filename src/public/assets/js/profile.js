/**
 * YES - Your Event Solution
 * JS : Page profil (aperçu avatar avant upload)
 *
 * @file profile.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  const avatarInput = document.getElementById('avatar-upload');

  if (!avatarInput) return;

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

});
