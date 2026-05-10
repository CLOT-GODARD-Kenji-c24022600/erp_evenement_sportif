/**
 * YES - Your Event Solution
 * JS : Layout global (sidebar, dark mode)
 *
 * @file layout.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  // ── Sidebar toggle ─────────────────────────────────
  const sbBtn = document.getElementById('sidebarToggle');
  if (sbBtn) {
    sbBtn.addEventListener('click', (e) => {
      e.preventDefault();
      document.body.classList.toggle('collapsed');
      const isCollapsed = document.body.classList.contains('collapsed');
      document.cookie = `sidebar=${isCollapsed ? 'collapsed' : 'expanded'}; path=/; max-age=31536000`;
    });
  }

  // ── Dark mode toggle ───────────────────────────────
  const themeBtn   = document.getElementById('darkModeToggle');
  const htmlEl     = document.documentElement;

  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const current  = htmlEl.getAttribute('data-bs-theme');
      const newTheme = current === 'light' ? 'dark' : 'light';

      htmlEl.setAttribute('data-bs-theme', newTheme);
      themeBtn.innerHTML = newTheme === 'dark'
        ? '<i class="bi bi-moon-stars-fill text-warning"></i>'
        : '<i class="bi bi-sun-fill text-warning"></i>';

      document.cookie = `theme=${newTheme}; max-age=31536000; path=/`;
    });
  }

  // ── Bootstrap tooltips ────────────────────────────
  const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipEls.forEach(el => new bootstrap.Tooltip(el));

});
