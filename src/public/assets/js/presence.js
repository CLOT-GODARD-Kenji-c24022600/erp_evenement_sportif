/**
 * YES - Your Event Solution
 * Présence temps réel — polling toutes les 30s
 *
 * @file presence.js
 * @version 1.0
 */

'use strict';

const YesPresence = (() => {

  const POLL_INTERVAL = 30_000; // 30 secondes
  const DOT_CLASSES   = ['bg-success', 'bg-warning', 'bg-danger', 'bg-secondary'];

  function isStaffPage() {
    return document.querySelector('[data-user-id]') !== null;
  }

  async function poll() {
    try {
      const res = await fetch('/ajax_presence', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) return;

      const data = await res.json(); // { "12": { dot: "bg-success", label: "En ligne" }, … }

      Object.entries(data).forEach(([userId, info]) => {
        const card = document.querySelector(`[data-user-id="${userId}"]`);
        if (!card) return;

        const dot = card.querySelector('.presence-dot');
        if (!dot) return;

        // Mettre à jour la couleur
        dot.classList.remove(...DOT_CLASSES);
        dot.classList.add(info.dot);

        // Mettre à jour le label accessibilité
        dot.setAttribute('title', info.label);
        dot.setAttribute('aria-label', `Statut : ${info.label}`);
      });

    } catch (_) {
      // Silencieux — perte réseau momentanée
    }
  }

  function init() {
    if (!isStaffPage()) return; // N'activer que sur la page staff

    // Premier appel immédiat (les données PHP sont déjà fraîches au chargement,
    // donc on attend le premier intervalle pour ne pas faire une requête inutile)
    setTimeout(poll, POLL_INTERVAL);
    setInterval(poll, POLL_INTERVAL);
  }

  return { init };
})();

document.addEventListener('DOMContentLoaded', () => YesPresence.init());