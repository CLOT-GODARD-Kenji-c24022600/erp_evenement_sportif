/**
 * YES - Your Event Solution
 * JS : Layout global (sidebar, dark mode, hamburger mobile)
 *
 * @file layout.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.0
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  const isMobile = () => window.innerWidth < 768;

  // ── Hamburger (mobile) ─────────────────────────────
  const hamburgerBtn  = document.getElementById('hamburgerBtn');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  function openMobileSidebar() {
    document.body.classList.add('sidebar-open');
    hamburgerBtn?.setAttribute('aria-expanded', 'true');
    hamburgerBtn?.querySelector('i')?.classList.replace('bi-list', 'bi-x-lg');
  }

  function closeMobileSidebar() {
    document.body.classList.remove('sidebar-open');
    hamburgerBtn?.setAttribute('aria-expanded', 'false');
    hamburgerBtn?.querySelector('i')?.classList.replace('bi-x-lg', 'bi-list');
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', () => {
      const isOpen = document.body.classList.contains('sidebar-open');
      isOpen ? closeMobileSidebar() : openMobileSidebar();
    });
  }

  // Clic sur l'overlay → ferme le menu
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeMobileSidebar);
  }

  // Fermer le menu quand on clique sur un lien nav (mobile)
  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', () => {
      if (isMobile()) closeMobileSidebar();
    });
  });

  // Touche Échap ferme la sidebar mobile
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
      closeMobileSidebar();
    }
  });

  // ── Sidebar toggle (desktop : réduire/agrandir) ────
  const sbBtn = document.getElementById('sidebarToggle');
  if (sbBtn) {
    sbBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (isMobile()) {
        // Sur mobile, le bouton × ferme la sidebar
        closeMobileSidebar();
      } else {
        // Sur desktop, collapse/expand
        document.body.classList.toggle('collapsed');
        const isCollapsed = document.body.classList.contains('collapsed');
        document.cookie = `sidebar=${isCollapsed ? 'collapsed' : 'expanded'}; path=/; max-age=31536000`;
      }
    });
  }

  // ── Fonction commune pour changer le thème ─────────
  function applyTheme(newTheme) {
    const htmlEl = document.documentElement;
    htmlEl.setAttribute('data-bs-theme', newTheme);

    const iconHtml = newTheme === 'dark'
      ? '<i class="bi bi-moon-stars-fill text-warning"></i>'
      : '<i class="bi bi-sun-fill text-warning"></i>';

    // Mettre à jour les deux boutons (desktop + mobile)
    const desktopBtn = document.getElementById('darkModeToggle');
    const mobileBtn  = document.getElementById('darkModeToggleMobile');
    if (desktopBtn) desktopBtn.innerHTML = iconHtml;
    if (mobileBtn)  mobileBtn.innerHTML  = iconHtml;

    document.cookie = `theme=${newTheme}; max-age=31536000; path=/`;
  }

  // ── Dark mode toggle (desktop) ─────────────────────
  const themeBtn = document.getElementById('darkModeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const current  = document.documentElement.getAttribute('data-bs-theme');
      applyTheme(current === 'light' ? 'dark' : 'light');
    });
  }

  // ── Dark mode toggle (mobile, dans la sidebar) ─────
  const themeBtnMobile = document.getElementById('darkModeToggleMobile');
  if (themeBtnMobile) {
    themeBtnMobile.addEventListener('click', () => {
      const current  = document.documentElement.getAttribute('data-bs-theme');
      applyTheme(current === 'light' ? 'dark' : 'light');
    });
  }

  // ── Bootstrap tooltips ────────────────────────────
  const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipEls.forEach(el => new bootstrap.Tooltip(el));

  // ── Fermer sidebar si redimensionnement vers desktop ─
  window.addEventListener('resize', () => {
    if (!isMobile() && document.body.classList.contains('sidebar-open')) {
      closeMobileSidebar();
    }
  });

});
// ── Fix bfcache : forcer le rechargement si la page vient du cache ──
// Quand l'utilisateur navigue en arrière/avant, le navigateur peut
// restaurer la page depuis son cache sans ré-exécuter le JS.
// On détecte ça avec l'event "pageshow" et on force un reload propre.
window.addEventListener('pageshow', (event) => {
  if (event.persisted) {
    // La page a été restaurée depuis le bfcache → reload
    window.location.reload();
  }
});