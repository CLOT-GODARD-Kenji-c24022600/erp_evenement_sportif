/**
 * YES - Your Event Solution
 * JS : Router SPA — navigation instantanée avec préchargement et cache
 *
 * @file routeur.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.2
 * @since 2026
 */

'use strict';

const YesRouter = (() => {

  const pageCache = new Map();
  const CACHE_TTL = 30_000; // 30s

  const getContentZone = () => document.getElementById('spa-content');

  /**
   * Récupère une page — depuis le cache si dispo et frais, sinon fetch AJAX.
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

  /**
   * Précharge une page en arrière-plan au survol (déclenché avant le clic).
   */
  function prefetch(url) {
    if (!url || pageCache.has(url)) return;
    fetchPage(url).catch(() => {});
  }

  /**
   * Injecte une feuille CSS si pas encore chargée dans le <head>.
   */
  const loadedCss = new Set();
  function ensureCss(href) {
    if (!href || loadedCss.has(href)) return;
    const link = document.createElement('link');
    link.rel   = 'stylesheet';
    link.href  = href;
    document.head.appendChild(link);
    loadedCss.add(href);
  }

  /**
   * Réexécute le script JS spécifique à la page.
   * Supprime le précédent pour éviter les doublons d'event listeners.
   */
  function rerunJs(src) {
    if (!src) return;
    document.querySelectorAll('script[data-spa-page]').forEach(s => s.remove());
    const s           = document.createElement('script');
    s.src             = src + '?v=' + Date.now(); // cache-bust
    s.dataset.spaPage = '1';
    document.body.appendChild(s);
  }

  /**
   * Met à jour le lien actif dans la sidebar et le breadcrumb.
   */
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

    // Breadcrumb dans le header
    const breadcrumb = document.querySelector('.breadcrumb-item.active');
    if (breadcrumb) {
      breadcrumb.textContent = page.charAt(0).toUpperCase() + page.slice(1).replace(/_/g, ' ');
    }
  }

  /**
   * Vérifie si un lien doit être intercepté par le SPA ou laissé en navigation normale.
   */
  function shouldIntercept(href) {
    if (!href) return false;
    if (href.startsWith('http') || href.startsWith('//')) return false;
    if (href.startsWith('#')) return false;
    if (href.startsWith('mailto') || href.startsWith('tel')) return false;
    if (href.includes('logout')) return false;
    if (href.includes('change_lang')) return false;
    if (href.includes('traitement_')) return false;
    if (/\.\w{2,4}$/.test(href)) return false; // fichiers (.pdf, .png…)
    return true;
  }

  /**
   * Navigue vers une URL — instantané si la page est dans le cache.
   */
  async function navigate(url, pushState = true) {
    const zone = getContentZone();
    if (!zone) {
      // Fallback : pas de zone SPA (page publique par ex.)
      window.location.href = url;
      return;
    }

    // Légère opacité seulement si pas en cache (évite tout flash)
    const isCached = pageCache.has(url) && (Date.now() - pageCache.get(url).ts) < CACHE_TTL;
    if (!isCached) {
      zone.style.opacity    = '0.5';
      zone.style.transition = 'opacity 0.08s ease';
    }

    try {
      const data = await fetchPage(url);
      if (!data) return;

      // Injection du contenu
      zone.innerHTML     = data.html;
      zone.style.opacity = '1';

      // CSS page-specific (chargé une seule fois)
      if (data.extraCss) {
        const match = data.extraCss.match(/href="([^"]+)"/);
        if (match) ensureCss(match[1]);
      }

      // JS page-specific (réexécuté à chaque navigation)
      if (data.extraJs) {
        const match = data.extraJs.match(/src="([^"]+)"/);
        if (match) rerunJs(match[1]);
      }

      // Historique navigateur + titre onglet
      const page = data.page || url.replace(/^\//, '').replace(/\?.*$/, '');
      if (pushState) history.pushState({ page, url }, '', url);
      document.title = 'YES – ' + page.charAt(0).toUpperCase() + page.slice(1).replace(/_/g, ' ');

      updateSidebarActive(page);

      // Réinitialise les tooltips Bootstrap sur le nouveau contenu
      zone.querySelectorAll('[data-bs-toggle="tooltip"]')
          .forEach(el => new bootstrap.Tooltip(el));

      // Scroll en haut
      window.scrollTo({ top: 0, behavior: 'instant' });

    } catch (_) {
      // Fallback navigation normale si erreur réseau
      window.location.href = url;
    } finally {
      zone.style.opacity = '1';
    }
  }

  /**
   * Intercepte les clics sur les liens internes + survols pour préchargement.
   */
  function interceptLinks() {
    // Clics
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (!shouldIntercept(href)) return;

      // Ne pas intercepter les liens Bootstrap (modales, dropdowns)
      if (link.dataset.bsDismiss || link.dataset.bsToggle) return;

      e.preventDefault();
      navigate(href);
    });

    // Survols → préchargement silencieux
    document.addEventListener('mouseover', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (!shouldIntercept(href)) return;

      prefetch(href);
    });
  }

  /**
   * Gère le bouton retour/avant du navigateur.
   */
  function handlePopState() {
    window.addEventListener('popstate', (e) => {
      const url = e.state?.url || window.location.pathname + window.location.search;
      navigate(url, false);
    });
  }

  function init() {
    // Ne pas initialiser sur les pages publiques (login, inscription…)
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