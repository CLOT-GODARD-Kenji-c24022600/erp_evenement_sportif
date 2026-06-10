/**
 * YES - Your Event Solution
 * JS : Layout global (sidebar, dark mode, hamburger mobile)
 *
 * @file layout.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  // ── Fix hauteur viewport (Lenovo Y13, zoom 125 %, mobile) ─────
  // Sur les écrans basse résolution ou avec scaling OS/navigateur,
  // 100vh ≠ hauteur visible réelle. On calcule la vraie hauteur
  // avec window.innerHeight et on l'injecte dans --app-height.
  // Le CSS utilise cette variable partout à la place de 100vh.
  function setAppHeight() {
    document.documentElement.style.setProperty(
      '--app-height',
      window.innerHeight + 'px'
    );
  }
  setAppHeight();
  window.addEventListener('resize', setAppHeight);
  // ─────────────────────────────────────────────────────────────

  const isMobile = () => window.innerWidth < 768;

  // ── Retirer "collapsed" sur mobile (cookie desktop résiduel) ──
  if (isMobile()) {
    document.body.classList.remove('collapsed');
  }

  // ── Hamburger (mobile) ─────────────────────────────
  const hamburgerBtn   = document.getElementById('hamburgerBtn');
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
      const current = document.documentElement.getAttribute('data-bs-theme');
      applyTheme(current === 'light' ? 'dark' : 'light');
    });
  }

  // ── Dark mode toggle (mobile, dans la sidebar) ─────
  const themeBtnMobile = document.getElementById('darkModeToggleMobile');
  if (themeBtnMobile) {
    themeBtnMobile.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-bs-theme');
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

  // ── Swipe gauche pour fermer la sidebar (mobile) ───
  const sidebar = document.getElementById('mobileSidebar');
  if (sidebar) {
    let touchStartX = 0;
    let touchStartY = 0;

    sidebar.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
    }, { passive: true });

    sidebar.addEventListener('touchend', (e) => {
      if (!document.body.classList.contains('sidebar-open')) return;
      const dx = e.changedTouches[0].clientX - touchStartX;
      const dy = e.changedTouches[0].clientY - touchStartY;
      // Swipe gauche (dx < -60) et plus horizontal que vertical
      if (dx < -60 && Math.abs(dx) > Math.abs(dy)) {
        closeMobileSidebar();
      }
    }, { passive: true });
  }

  // ── Swipe droit sur le bord gauche de l'écran pour ouvrir ──
  document.addEventListener('touchstart', (e) => {
    if (e.touches[0].clientX < 20) {
      touchEdgeStart = e.touches[0].clientX;
    } else {
      touchEdgeStart = null;
    }
  }, { passive: true });

  let touchEdgeStart = null;
  document.addEventListener('touchend', (e) => {
    if (touchEdgeStart === null) return;
    const dx = e.changedTouches[0].clientX - touchEdgeStart;
    if (dx > 60 && !document.body.classList.contains('sidebar-open')) {
      openMobileSidebar();
    }
    touchEdgeStart = null;
  }, { passive: true });

  // ── Notifications ────────────────────────────────
  initNotifications();

});

// ── Fix bfcache : forcer le rechargement si la page vient du cache ──
window.addEventListener('pageshow', (event) => {
  if (event.persisted) {
    window.location.reload();
  }
});

// ── Notifications ─────────────────────────────────────────────
function initNotifications() {
  const markAllBtn = document.getElementById('notif-mark-all-btn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', async (e) => {
      e.stopPropagation();
      try {
        await fetch('/ajax_notif_read_all', { method: 'POST' });
        document.querySelectorAll('.notif-item').forEach(el => {
          el.style.background = 'transparent';
        });
        updateNotifBadge(0);
        markAllBtn.style.display = 'none';
      } catch {}
    });
  }
}

// Clic sur une notification → marquer lue + naviguer
window.notifClick = async function(id, lien) {
  try {
    const fd = new FormData();
    fd.append('id', id);
    await fetch('/ajax_notif_read', { method: 'POST', body: fd });
    const item = document.querySelector(`.notif-item[data-id="${id}"]`);
    if (item) item.style.background = 'transparent';
    const unread = document.querySelectorAll('.notif-item[style*="primary-bg-subtle"]').length;
    updateNotifBadge(unread);
  } catch {}
  if (lien && lien !== '') {
    const dropdown = document.getElementById('notif-dropdown-wrapper');
    if (dropdown) bootstrap.Dropdown.getOrCreateInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'))?.hide();
    setTimeout(() => { window.location.href = lien; }, 150);
  }
};

// Supprimer une notification
window.notifDelete = async function(e, id) {
  e.stopPropagation();
  const item = document.querySelector(`.notif-item[data-id="${id}"]`);
  if (!item) return;
  try {
    const fd = new FormData();
    fd.append('id', id);
    const res = await fetch('/ajax_notif_delete', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      item.style.opacity = '0';
      item.style.transition = 'opacity .2s';
      setTimeout(() => {
        item.remove();
        const remaining = document.querySelectorAll('.notif-item').length;
        updateNotifBadge(remaining);
        if (remaining === 0) {
          const list = document.getElementById('notif-list');
          if (list) list.innerHTML = '<li class="text-center text-muted small py-4"><i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>Aucune nouvelle notification</li>';
        }
      }, 200);
    }
  } catch {}
};

function updateNotifBadge(count) {
  const badge      = document.getElementById('notif-badge');
  const header     = document.getElementById('notif-count-header');
  const markAllBtn = document.getElementById('notif-mark-all-btn');
  if (badge)      { badge.textContent      = count > 99 ? '99+' : count; badge.style.display      = count > 0 ? '' : 'none'; }
  if (header)     { header.textContent     = count > 99 ? '99+' : count; header.style.display     = count > 0 ? '' : 'none'; }
  if (markAllBtn) { markAllBtn.style.display = count > 0 ? '' : 'none'; }
}