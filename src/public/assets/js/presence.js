/**
 * YES - Your Event Solution
 * Présence temps réel — polling toutes les 30s (Compatible SPA)
 *
 * @file presence.js
 * @version 1.1
 */

(() => {
  'use strict';

  const POLL_INTERVAL = 30_000;
  const DOT_CLASSES   = ['bg-success', 'bg-warning', 'bg-danger', 'bg-secondary'];
  let _pollTimer = null; // Stocke l'ID du timer

  function isStaffPage() {
    return document.querySelector('[data-user-id]') !== null;
  }

  async function poll() {
    if (!isStaffPage()) {
      // Si on a quitté la page staff, on coupe le timer silencieusement
      clearInterval(_pollTimer);
      return;
    }
    try {
      const res = await fetch('/ajax_presence', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) return;

      const data = await res.json();
      Object.entries(data).forEach(([userId, info]) => {
        const card = document.querySelector(`[data-user-id="${userId}"]`);
        if (!card) return;
        const dot = card.querySelector('.presence-dot');
        if (!dot) return;
        dot.classList.remove(...DOT_CLASSES);
        dot.classList.add(info.dot);
        dot.setAttribute('title', info.label);
        dot.setAttribute('aria-label', `Statut : ${info.label}`);
      });
    } catch (_) {}
  }

  function init() {
    // Nettoie l'ancien timer si la page est rechargée via SPA
    clearInterval(_pollTimer);
    if (!isStaffPage()) return;
    
    // Attente initiale puis boucle propre
    _pollTimer = setInterval(poll, POLL_INTERVAL);
  }

  // ── SPA entry-point & Chargement sécurisé ────────────────────
  window.YesPresenceInit = init;

  const currentScript = document.currentScript;
  if (!currentScript || currentScript.dataset.spaPage !== '1') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      requestAnimationFrame(init);
    }
  }

})();