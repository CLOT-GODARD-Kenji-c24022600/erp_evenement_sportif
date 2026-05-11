/**
 * YES - Your Event Solution
 * JS : Router SPA — navigation instantanée avec préchargement et cache
 *
 * @file router.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

'use strict';

const YesRouter = (() => {

  // Cache en mémoire des pages déjà chargées
  const pageCache   = new Map();
  const CACHE_TTL   = 30_000; // 30s avant de considérer le cache périmé

  const getContentZone = () => document.getElementById('spa-content');

  /**
   * Récupère une page — depuis le cache si dispo et frais, sinon fetch.
   */
  async function fetchPage(url) {
    const cached = pageCache.get(url);
    if (cached && (Date.now() - cached.ts) < CACHE_TTL) {
      return cached.data;
    }

    const res = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (res.status === 401) {
      const data = await res.json();
      window.location.href = data.redirect || '/login';
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

  /**
   * Précharge une page en arrière-plan (déclenché au survol d'un lien).
   */
  function prefetch(url) {
    if (!url || pageCache.has(url)) return;
    // On lance le fetch mais on ne bloque rien — résultat mis en cache
    fetchPage(url).catch(() => {});
  }

  /**
   * Injecte une feuille CSS si pas encore chargée.
   */
  const loadedCss = new Set();
  function ensureCss(href) {
    if (!href || loadedCss.has(href)) return;
    const link   = document.createElement('link');
    link.rel      = 'stylesheet';
    link.href     = href;
    document.head.appendChild(link);
    loadedCss.add(href);
  }

  /**
   * Réexécute le script JS spécifique à la page.
   */
  function rerunJs(src) {
    if (!src) return;
    document.querySelectorAll('script[data-spa-page]').forEach(s => s.remove());
    const s          = document.createElement('script');
    s.src            = src + '?v=' + Date.now();
    s.dataset.spaPage = '1';
    document.body.appendChild(s);
  }

  /**
   * Met à jour le lien actif dans la sidebar et le breadcrumb.
   */
  function updateSidebarActive(page) {
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      const href     = link.getAttribute('href');
      const isActive = href === '/' + page;
      link.classList.toggle('active',     isActive);
      link.classList.toggle('bg-primary', isActive);
      link.classList.toggle('opacity-75', !isActive);
      if (isActive) link.setAttribute('aria-current', 'page');
      else          link.removeAttribute('aria-current');
    });

    const breadcrumb = document.getElementById('breadcrumb-page');
    if (breadcrumb) {
      breadcrumb.textContent = page.charAt(0).toUpperCase() + page.slice(1);
    }
  }

  /**
   * Navigue vers une URL — quasi instantané si préchargé.
   */
  async function navigate(url, pushState = true) {
    const zone = getContentZone();
    if (!zone) return;

    // Transition visuelle légère seulement si pas dans le cache (évite le flash)
    const isCached = pageCache.has(url) &&
                     (Date.now() - pageCache.get(url).ts) < CACHE_TTL;
    if (!isCached) {
      zone.style.opacity    = '0.4';
      zone.style.transition = 'opacity 0.1s';
    }

    try {
      const data = await fetchPage(url);
      if (!data) return;

      // Injection du contenu
      zone.innerHTML        = data.html;
      zone.style.opacity    = '1';
      zone.style.transition = 'opacity 0.1s';

      // CSS et JS spécifiques à la page
      if (data.extraCss) {
        const match = data.extraCss.match(/href="([^"]+)"/);
        if (match) ensureCss(match[1]);
      }
      if (data.extraJs) {
        const match = data.extraJs.match(/src="([^"]+)"/);
        if (match) rerunJs(match[1]);
      }

      // Historique + titre
      const page = data.page || url.replace(/^\//, '');
      if (pushState) history.pushState({ page }, '', url);
      document.title = 'YES – ' + page.charAt(0).toUpperCase() + page.slice(1);

      updateSidebarActive(page);

      // Réinitialise les tooltips Bootstrap sur le nouveau contenu
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el));

      window.scrollTo(0, 0);

    } catch (_) {
      window.location.href = url;
    } finally {
      zone.style.opacity = '1';
    }
  }

  /**
   * Intercepte les clics et survols sur les liens internes.
   */
  function interceptLinks() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (
        !href ||
        href.startsWith('http') ||
        href.startsWith('#') ||
        href.startsWith('mailto') ||
        href.includes('logout') ||
        href.includes('change_lang') ||
        href.match(/\.\w{2,4}$/)
      ) return;

      if (link.dataset.bsDismiss || link.dataset.bsToggle) return;

      e.preventDefault();
      navigate(href);
    });

    // Préchargement au survol — la page est fetché AVANT le clic
    document.addEventListener('mouseover', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (
        !href ||
        href.startsWith('http') ||
        href.startsWith('#') ||
        href.startsWith('mailto') ||
        href.includes('logout') ||
        href.includes('change_lang') ||
        href.match(/\.\w{2,4}$/)
      ) return;

      prefetch(href);
    });
  }

  function handlePopState() {
    window.addEventListener('popstate', () => {
      navigate(window.location.pathname, false);
    });
  }

  function init() {
    interceptLinks();
    handlePopState();
    const page = window.location.pathname.replace(/^\//, '') || 'dashboard';
    history.replaceState({ page }, '', window.location.pathname);
    updateSidebarActive(page);
  }

  return { init, navigate, prefetch };
})();

document.addEventListener('DOMContentLoaded', () => YesRouter.init());