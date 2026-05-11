/**
 * YES - Your Event Solution
 * JS : Présence utilisateurs — SSE temps réel + fallback polling 5s
 *
 * @file presence.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

'use strict';

const YesPresence = (() => {

  let source        = null;
  let fallbackTimer = null;
  let lastCount     = -1; // Pour ne re-render que si ça a changé

  /**
   * Démarre la connexion SSE (Server-Sent Events).
   * Le serveur pousse une mise à jour dès qu'un utilisateur
   * se connecte / déconnecte — pas besoin de poller.
   */
  function startSSE() {
    if (!window.EventSource) {
      // Navigateur trop vieux : fallback polling
      startPolling();
      return;
    }

    source = new EventSource('/api_presence_sse', { withCredentials: true });

    source.addEventListener('presence', (e) => {
      try {
        const users = JSON.parse(e.data);
        render(users);
      } catch (_) {}
    });

    source.addEventListener('error', () => {
      // Connexion SSE perdue → fallback polling toutes les 5s
      source.close();
      source = null;
      startPolling();
    });
  }

  /**
   * Fallback : polling toutes les 5s si SSE non dispo.
   */
  function startPolling() {
    if (fallbackTimer) return;
    refresh();
    fallbackTimer = setInterval(refresh, 5_000);
  }

  async function refresh() {
    try {
      const res   = await fetch('/api_presence', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) return;
      render(await res.json());
    } catch (_) {}
  }

  /**
   * Met à jour le badge — uniquement si le nombre a changé.
   */
  function render(users) {
    const nb = Array.isArray(users) ? users.length : 0;
    if (nb === lastCount) return; // Rien de nouveau
    lastCount = nb;

    const badge = document.getElementById('presenceBadge');
    const count = document.getElementById('presenceCount');
    if (!badge) return;

    if (count) count.textContent = nb;

    const names   = users.slice(0, 5).map(u => u.prenom + ' ' + u.nom).join(', ');
    const extra   = nb > 5 ? ` +${nb - 5}` : '';
    const label   = nb > 0 ? names + extra : 'Personne en ligne';

    badge.querySelector('i').classList.toggle('text-success', nb > 0);
    badge.querySelector('i').classList.toggle('text-muted',   nb === 0);

    // Met à jour le tooltip Bootstrap
    const tip = bootstrap.Tooltip.getInstance(badge);
    if (tip) tip.setContent({ '.tooltip-inner': label });
    else     new bootstrap.Tooltip(badge, { title: label });
  }

  function start() {
    startSSE();
  }

  function stop() {
    if (source)        source.close();
    if (fallbackTimer) clearInterval(fallbackTimer);
  }

  return { start, stop };
})();

document.addEventListener('DOMContentLoaded', () => YesPresence.start());