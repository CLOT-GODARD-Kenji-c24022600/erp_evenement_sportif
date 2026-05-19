/**
 * YES - Your Event Solution
 * JS : Router SPA — navigation instantanée avec préchargement et cache
 *
 * @file routeur.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.3
 * @since 2026
 */

'use strict';

const YesRouter = (() => {

  const pageCache = new Map();
  const CACHE_TTL = 30_000; // 30s

  const getContentZone = () => document.getElementById('spa-content');

  async function fetchPage(url) {
    const cached = pageCache.get(url);
    if (cached && (Date.now() - cached.ts) < CACHE_TTL) {
      return cached.data;
    }

    const res = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (res.status === 401) {
      window.location.href = '/login';
      return null;
    }

    if (!res.ok) {
      window.location.href = url;
      return null;
    }

    const data = await res.json();
    pageCache.set(url, { data, ts: Date.now() });
    return data;
  }

  function prefetch(url) {
    if (!url || pageCache.has(url)) return;
    fetchPage(url).catch(() => {});
  }

  const loadedCss = new Set();
  function ensureCss(href) {
    if (!href || loadedCss.has(href)) return;
    const link = document.createElement('link');
    link.rel   = 'stylesheet';
    link.href  = href;
    document.head.appendChild(link);
    loadedCss.add(href);
  }

  function rerunJs(src) {
    document.querySelectorAll('script[data-spa-page]').forEach(s => s.remove());
    window.YesPageInit = null;

    if (!src) return;

    const s           = document.createElement('script');
    s.src             = src + '?v=' + Date.now();
    s.dataset.spaPage = '1';

    s.onload = () => {
      if (typeof window.YesPageInit === 'function') {
        window.YesPageInit();
      }
    };

    s.onerror = () => {
      console.warn('[YesRouter] Échec chargement script :', src);
    };

    document.body.appendChild(s);
  }

  function updateSidebarActive(page) {
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      const href     = link.getAttribute('href');
      const isActive = href === '/' + page || (page === 'dashboard' && href === '/dashboard');
      link.classList.toggle('active',     isActive);
      link.classList.toggle('bg-primary', isActive);
      link.classList.toggle('opacity-75', !isActive);
      if (isActive) link.setAttribute('aria-current', 'page');
      else          link.removeAttribute('aria-current');
    });

    const breadcrumb = document.querySelector('.breadcrumb-item.active');
    if (breadcrumb) {
      breadcrumb.textContent = page.charAt(0).toUpperCase() + page.slice(1).replace(/_/g, ' ');
    }
  }

  function shouldIntercept(href) {
    if (!href) return false;
    if (href.startsWith('http') || href.startsWith('//')) return false;
    if (href.startsWith('#')) return false;
    if (href.startsWith('mailto') || href.startsWith('tel')) return false;
    if (href.includes('logout')) return false;
    if (href.includes('change_lang')) return false;
    if (href.includes('traitement_')) return false;
    if (/\.\w{2,4}$/.test(href)) return false;
    return true;
  }

  async function navigate(url, pushState = true) {
    const zone = getContentZone();
    if (!zone) {
      window.location.href = url;
      return;
    }

    // ✅ Pages dont le contenu change fréquemment : on invalide le cache
    const noCache = ['/dashboard', '/annuaire', '/staff', '/operationnel'];
    if (noCache.some(p => url.startsWith(p))) {
      pageCache.delete(url);
    }

    const isCached = pageCache.has(url) && (Date.now() - pageCache.get(url).ts) < CACHE_TTL;
    if (!isCached) {
      zone.style.opacity    = '0.5';
      zone.style.transition = 'opacity 0.08s ease';
    }

    try {
      const data = await fetchPage(url);
      if (!data) return;

      // ✅ Détruire les tooltips Bootstrap avant d'écraser le HTML
      zone.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getInstance(el)?.dispose();
      });

      zone.innerHTML     = data.html;
      zone.style.opacity = '1';

      if (data.extraCss) {
        const match = data.extraCss.match(/href="([^"]+)"/);
        if (match) ensureCss(match[1]);
      }

      // ✅ Toujours appeler rerunJs (même sans extraJs) pour nettoyer YesPageInit
      let jsSrc = null;
      if (data.extraJs) {
        const match = data.extraJs.match(/src="([^"]+)"/);
        if (match) jsSrc = match[1];
      }
      rerunJs(jsSrc);

      const page = data.page || url.replace(/^\//, '').replace(/\?.*$/, '');
      if (pushState) history.pushState({ page, url }, '', url);
      document.title = 'YES – ' + page.charAt(0).toUpperCase() + page.slice(1).replace(/_/g, ' ');

      updateSidebarActive(page);

      zone.querySelectorAll('[data-bs-toggle="tooltip"]')
          .forEach(el => new bootstrap.Tooltip(el));

      window.scrollTo({ top: 0, behavior: 'instant' });

    } catch (_) {
      window.location.href = url;
    } finally {
      zone.style.opacity = '1';
    }
  }

  function interceptLinks() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (!shouldIntercept(href)) return;
      if (link.dataset.bsDismiss || link.dataset.bsToggle) return;

      e.preventDefault();
      navigate(href);
    });

    document.addEventListener('mouseover', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (!shouldIntercept(href)) return;

      prefetch(href);
    });
  }

  function handlePopState() {
    window.addEventListener('popstate', (e) => {
      const url = e.state?.url || window.location.pathname + window.location.search;
      navigate(url, false);
    });
  }

  function init() {
    if (!getContentZone()) return;

    interceptLinks();
    handlePopState();

    const path = window.location.pathname + window.location.search;
    const page = window.location.pathname.replace(/^\//, '') || 'dashboard';
    history.replaceState({ page, url: path }, '', path);
    updateSidebarActive(page);
  }

  return { init, navigate, prefetch };
})();

document.addEventListener('DOMContentLoaded', () => YesRouter.init());