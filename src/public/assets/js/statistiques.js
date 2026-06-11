/**
 * YES – Your Event Solution
 * JS : Statistiques & Reporting — graphiques Canvas natifs
 *
 * @file statistiques.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 *
 * Architecture :
 *  - Les données sont lues depuis <script id="stats-data" type="application/json">
 *    (injectées côté PHP au premier rendu → 0 requête AJAX au chargement).
 *  - Le bouton "Actualiser" fait un fetch /statistiques/ajax pour rafraîchir.
 *  - Tous les graphiques sont redessinés au resize et au changement de thème.
 *  - Compatible SPA : window.YesPageInit() est appelé par le routeur.
 */

'use strict';

// ── Palette ──────────────────────────────────────────────────
const C = {
  primary:   '#0d6efd',
  success:   '#198754',
  warning:   '#ffc107',
  danger:    '#dc3545',
  info:      '#0dcaf0',
  teal:      '#20c997',
  purple:    '#6f42c1',
  gray:      '#6c757d',
  lightGray: '#dee2e6',
};

const PALETTE = [C.primary, C.success, C.warning, C.danger, C.info, C.teal, C.purple, C.gray];

// ── Helpers thème ────────────────────────────────────────────

function isDark() {
  return document.documentElement.getAttribute('data-bs-theme') === 'dark';
}

function gridColor()  { return isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.07)'; }
function labelColor() { return isDark() ? '#94a3b8' : '#6c757d'; }
function bgColor()    { return isDark() ? '#1e293b' : '#ffffff'; }

// ── Lecture des données JSON injectées par PHP ───────────────

function getData() {
  const el = document.getElementById('stats-data');
  if (!el) return {};
  try { return JSON.parse(el.textContent); }
  catch { return {}; }
}

// ── Utilitaires ──────────────────────────────────────────────

function fmtEuro(n) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
  }).format(n);
}

/**
 * Remplit les 12 derniers mois à partir d'un tableau [{mois:'2025-04', total:3}, …]
 * Retourne [{label:'2025-04', short:'avr. 25', value:3}, …]
 */
function fillMonths(rows, valueKey = 'total') {
  const map = {};
  rows.forEach(r => { map[r.mois] = parseFloat(r[valueKey]) || 0; });

  const result = [];
  const now = new Date();
  for (let i = 11; i >= 0; i--) {
    const d     = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const label = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    const short = d.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
    result.push({ label, short, value: map[label] || 0 });
  }
  return result;
}

// ── Moteur graphique Canvas ──────────────────────────────────

/**
 * Prépare le canvas pour un rendu net (DPR-aware).
 * Retourne le contexte 2D.
 */
function setupCanvas(canvas) {
  const dpr  = window.devicePixelRatio || 1;
  const w    = canvas.offsetWidth  || canvas.parentElement?.offsetWidth || 400;
  const h    = canvas.offsetHeight || 220;
  canvas.width  = w * dpr;
  canvas.height = h * dpr;
  const ctx = canvas.getContext('2d');
  ctx.scale(dpr, dpr);
  ctx._w = w;
  ctx._h = h;
  return ctx;
}

/**
 * Bar chart vertical — supporte plusieurs datasets (groupés).
 * @param {HTMLCanvasElement} canvas
 * @param {string[]} labels
 * @param {Array<{label:string, color:string, data:number[]}>} datasets
 * @param {{money?:boolean}} opts
 */
function drawBarChart(canvas, labels, datasets, opts = {}) {
  const ctx = setupCanvas(canvas);
  const W = ctx._w, H = ctx._h;
  const pad = { top: 24, right: 16, bottom: 52, left: opts.money ? 74 : 42 };
  const cw  = W - pad.left - pad.right;
  const ch  = H - pad.top  - pad.bottom;

  ctx.clearRect(0, 0, W, H);

  const allVals = datasets.flatMap(ds => ds.data);
  const maxVal  = Math.max(...allVals, 1);
  const nLines  = 4;
  const nDS     = datasets.length;
  const groupW  = cw / (labels.length || 1);
  const barW    = (groupW * 0.7) / nDS;

  // Grilles horizontales
  for (let i = 0; i <= nLines; i++) {
    const y = pad.top + ch - (i / nLines) * ch;
    ctx.strokeStyle = gridColor();
    ctx.lineWidth   = 1;
    ctx.beginPath();
    ctx.moveTo(pad.left, y);
    ctx.lineTo(pad.left + cw, y);
    ctx.stroke();

    const v = (maxVal / nLines) * i;
    ctx.fillStyle = labelColor();
    ctx.font      = '10px system-ui';
    ctx.textAlign = 'right';
    ctx.fillText(opts.money ? fmtEuro(v) : Math.round(v), pad.left - 5, y + 4);
  }

  // Barres
  datasets.forEach((ds, di) => {
    ds.data.forEach((val, i) => {
      const barH = (val / maxVal) * ch;
      const x    = pad.left + i * groupW + (groupW * 0.15) + di * (barW + 2);
      const y    = pad.top + ch - barH;

      const grad = ctx.createLinearGradient(0, y, 0, y + barH);
      grad.addColorStop(0, ds.color + 'ee');
      grad.addColorStop(1, ds.color + '88');
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.roundRect(x, y, barW, barH, [3, 3, 0, 0]);
      ctx.fill();
    });
  });

  // Labels X
  ctx.fillStyle = labelColor();
  ctx.font      = '10px system-ui';
  ctx.textAlign = 'center';
  labels.forEach((lbl, i) => {
    ctx.fillText(lbl, pad.left + i * groupW + groupW / 2, H - pad.bottom + 18);
  });

  // Légende (si plusieurs datasets)
  if (nDS > 1) {
    let lx = pad.left;
    datasets.forEach(ds => {
      ctx.fillStyle = ds.color;
      ctx.fillRect(lx, H - 16, 12, 10);
      ctx.fillStyle = labelColor();
      ctx.textAlign = 'left';
      ctx.font      = '10px system-ui';
      ctx.fillText(ds.label, lx + 16, H - 7);
      lx += ctx.measureText(ds.label).width + 34;
    });
  }
}

/**
 * Line chart avec aire dégradée et points.
 */
function drawLineChart(canvas, labels, data, color = C.primary, opts = {}) {
  const ctx = setupCanvas(canvas);
  const W = ctx._w, H = ctx._h;
  const pad = { top: 20, right: 16, bottom: 46, left: opts.money ? 74 : 42 };
  const cw  = W - pad.left - pad.right;
  const ch  = H - pad.top  - pad.bottom;
  const n   = data.length;

  ctx.clearRect(0, 0, W, H);

  const maxVal = Math.max(...data, 1);
  const nLines = 4;

  // Grilles
  for (let i = 0; i <= nLines; i++) {
    const y = pad.top + ch - (i / nLines) * ch;
    ctx.strokeStyle = gridColor();
    ctx.lineWidth   = 1;
    ctx.beginPath();
    ctx.moveTo(pad.left, y);
    ctx.lineTo(pad.left + cw, y);
    ctx.stroke();

    const v = (maxVal / nLines) * i;
    ctx.fillStyle = labelColor();
    ctx.font      = '10px system-ui';
    ctx.textAlign = 'right';
    ctx.fillText(opts.money ? fmtEuro(v) : Math.round(v), pad.left - 5, y + 4);
  }

  const xOf = i => pad.left + (i / (n - 1 || 1)) * cw;
  const yOf = v => pad.top + ch - (v / maxVal) * ch;

  // Aire dégradée
  const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + ch);
  grad.addColorStop(0, color + '40');
  grad.addColorStop(1, color + '00');
  ctx.beginPath();
  data.forEach((v, i) => { i === 0 ? ctx.moveTo(xOf(i), yOf(v)) : ctx.lineTo(xOf(i), yOf(v)); });
  ctx.lineTo(xOf(n - 1), pad.top + ch);
  ctx.lineTo(pad.left, pad.top + ch);
  ctx.closePath();
  ctx.fillStyle = grad;
  ctx.fill();

  // Ligne principale
  ctx.beginPath();
  ctx.strokeStyle = color;
  ctx.lineWidth   = 2.5;
  ctx.lineJoin    = 'round';
  data.forEach((v, i) => { i === 0 ? ctx.moveTo(xOf(i), yOf(v)) : ctx.lineTo(xOf(i), yOf(v)); });
  ctx.stroke();

  // Points
  data.forEach((v, i) => {
    ctx.beginPath();
    ctx.arc(xOf(i), yOf(v), 4, 0, Math.PI * 2);
    ctx.fillStyle   = bgColor();
    ctx.strokeStyle = color;
    ctx.lineWidth   = 2;
    ctx.fill();
    ctx.stroke();
  });

  // Labels X
  ctx.fillStyle = labelColor();
  ctx.font      = '10px system-ui';
  ctx.textAlign = 'center';
  labels.forEach((lbl, i) => { ctx.fillText(lbl, xOf(i), H - pad.bottom + 18); });
}

/**
 * Donut chart.
 * @param {string} centerLabel  Texte affiché au centre (ex : "72%")
 */
function drawDonut(canvas, values, colors, centerLabel = '') {
  const size = canvas.offsetWidth || parseInt(canvas.getAttribute('width')) || 130;
  const dpr  = window.devicePixelRatio || 1;
  canvas.width  = size * dpr;
  canvas.height = size * dpr;
  const ctx = canvas.getContext('2d');
  ctx.scale(dpr, dpr);

  const cx    = size / 2;
  const cy    = size / 2;
  const outer = size / 2 - 6;
  const inner = outer * 0.6;
  const total = values.reduce((a, b) => a + b, 0) || 1;

  let start = -Math.PI / 2;
  values.forEach((val, i) => {
    const angle = (val / total) * Math.PI * 2;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, outer, start, start + angle);
    ctx.closePath();
    ctx.fillStyle = colors[i] || '#ccc';
    ctx.fill();
    start += angle;
  });

  // Trou intérieur (couleur fond)
  ctx.beginPath();
  ctx.arc(cx, cy, inner, 0, Math.PI * 2);
  ctx.fillStyle = bgColor();
  ctx.fill();

  // Texte centre
  if (centerLabel) {
    ctx.fillStyle    = isDark() ? '#f1f5f9' : '#212529';
    ctx.font         = `bold ${Math.round(size * 0.19)}px system-ui`;
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(centerLabel, cx, cy);
  }
}

/**
 * Bar chart horizontal (pour prestataires).
 */
function drawHBarChart(canvas, labels, data, colors) {
  const ctx = setupCanvas(canvas);
  const W = ctx._w, H = ctx._h;
  ctx.clearRect(0, 0, W, H);

  const pad     = { top: 8, right: 90, bottom: 8, left: 12 };
  const chartW  = W - pad.left - pad.right;
  const chartH  = H - pad.top  - pad.bottom;
  const maxVal  = Math.max(...data, 1);
  const spacing = chartH / (data.length || 1);
  const barH    = spacing * 0.55;

  data.forEach((val, i) => {
    const y    = pad.top + i * spacing + spacing * 0.2;
    const barW = (val / maxVal) * chartW;

    // Fond track
    ctx.fillStyle = isDark() ? 'rgba(255,255,255,0.06)' : '#f1f5f9';
    ctx.beginPath();
    ctx.roundRect(pad.left, y, chartW, barH, 4);
    ctx.fill();

    // Barre colorée
    const grad = ctx.createLinearGradient(pad.left, 0, pad.left + barW, 0);
    grad.addColorStop(0, colors[i % colors.length] + 'cc');
    grad.addColorStop(1, colors[i % colors.length]);
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.roundRect(pad.left, y, Math.max(barW, 4), barH, 4);
    ctx.fill();

    // Label dans la barre
    const fontSize = Math.min(12, barH * 0.7);
    ctx.fillStyle  = '#fff';
    ctx.font       = `${fontSize}px system-ui`;
    ctx.textAlign  = 'left';
    ctx.textBaseline = 'middle';
    if (barW > 40) {
      ctx.fillText(labels[i], pad.left + 8, y + barH / 2);
    } else {
      ctx.fillStyle = labelColor();
      ctx.fillText(labels[i], pad.left + barW + 6, y + barH / 2);
    }

    // Montant à droite
    ctx.fillStyle    = labelColor();
    ctx.textAlign    = 'right';
    ctx.textBaseline = 'middle';
    ctx.font         = `bold ${fontSize}px system-ui`;
    ctx.fillText(fmtEuro(val), W - 4, y + barH / 2);
  });
}

// ── Rendu de tous les graphiques ─────────────────────────────

function renderCharts(data) {
  // 1. Bar chart — événements par mois
  const evtCanvas = document.getElementById('chart-events-mois');
  if (evtCanvas) {
    const months = fillMonths(data.evenementsParMois || []);
    drawBarChart(evtCanvas,
      months.map(m => m.short),
      [{ label: 'Événements', color: C.primary, data: months.map(m => m.value) }]
    );
  }

  // 2. Line chart — facturation par mois
  const factCanvas = document.getElementById('chart-fact-mois');
  if (factCanvas) {
    const months = fillMonths(data.factParMois || []);
    drawLineChart(factCanvas,
      months.map(m => m.short),
      months.map(m => m.value),
      C.success,
      { money: true }
    );
  }

  // 3. Bar chart groupé — budget produits/charges par événement
  const budgetCanvas = document.getElementById('chart-budget');
  if (budgetCanvas) {
    const items = data.budgetParEvent || [];
    drawBarChart(budgetCanvas,
      items.map(e => String(e.label).length > 12 ? String(e.label).slice(0, 11) + '…' : e.label),
      [
        { label: 'Produits', color: C.success, data: items.map(e => parseFloat(e.produits) || 0) },
        { label: 'Charges',  color: C.danger,  data: items.map(e => parseFloat(e.charges)  || 0) },
      ],
      { money: true }
    );
  }

  // 4. Donut — taux de complétion todos
  // Statuts réels en BDD : en_attente / en_cours / termine
  const donutTodo = document.getElementById('chart-donut-todo');
  if (donutTodo) {
    const t     = data.tauxCompletion?.todos || { done: 0, doing: 0, todo: 0, total: 1 };
    const total = parseInt(t.total) || 1;
    const pct   = Math.round((parseInt(t.done) || 0) / total * 100);
    drawDonut(
      donutTodo,
      [parseInt(t.done) || 0, parseInt(t.doing) || 0, parseInt(t.todo) || 0],
      [C.success, C.warning, isDark() ? '#334155' : C.lightGray],
      pct + '%'
    );
  }

  // 5. Donut — taux de complétion planning
  // Statuts réels en BDD : wip / valide / en_cours
  const donutPlanning = document.getElementById('chart-donut-planning');
  if (donutPlanning) {
    const p     = data.tauxCompletion?.planning || { done: 0, doing: 0, todo: 0, total: 1 };
    const total = parseInt(p.total) || 1;
    const pct   = Math.round((parseInt(p.done) || 0) / total * 100);
    drawDonut(
      donutPlanning,
      [parseInt(p.done) || 0, parseInt(p.doing) || 0, parseInt(p.todo) || 0],
      [C.teal, C.info, isDark() ? '#334155' : C.lightGray],
      pct + '%'
    );
  }

  // 6. Bar chart horizontal — top prestataires
  const prestCanvas = document.getElementById('chart-prestataires');
  if (prestCanvas) {
    const items = data.topPrestataires || [];
    drawHBarChart(
      prestCanvas,
      items.map(p => String(p.prestataire).length > 20 ? String(p.prestataire).slice(0, 19) + '…' : p.prestataire),
      items.map(p => parseFloat(p.total) || 0),
      PALETTE
    );
  }
}

// ── Bouton Actualiser ────────────────────────────────────────

function initRefreshBtn(currentData) {
  const btn = document.getElementById('btn-refresh-stats');
  if (!btn) return;

  btn.addEventListener('click', () => {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Chargement…';

    fetch('/statistiques/ajax', { headers: { 'X-Requested-With': 'fetch' } })
      .then(r => r.json())
      .then(fresh => {
        // Mettre à jour le bloc JSON injecté pour les prochains redraws
        const el = document.getElementById('stats-data');
        if (el) el.textContent = JSON.stringify(fresh);
        renderCharts(fresh);
      })
      .catch(() => { /* silencieux */ })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualiser';
      });
  });
}

// ── Initialisation principale ────────────────────────────────

let _lastData = null;

function initStats() {
  const data = getData();
  _lastData  = data;
  if (!data || !Object.keys(data).length) return;
  renderCharts(data);
  initRefreshBtn(data);
}

// ── Redessiner au resize ─────────────────────────────────────

let _resizeTimer = null;
window.addEventListener('resize', () => {
  clearTimeout(_resizeTimer);
  _resizeTimer = setTimeout(() => {
    if (_lastData && document.getElementById('statistiques-page')) renderCharts(_lastData);
  }, 180);
});

// ── Redessiner au changement de thème dark/light ─────────────

new MutationObserver(() => {
  if (_lastData && document.getElementById('statistiques-page')) renderCharts(_lastData);
}).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

// ── SPA entry-point ──────────────────────────────────────────

window.YesPageInit = function statsInit() {
  initStats();
};

// Déclenchement direct si hors SPA (chargement classique)
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initStats);
} else {
  initStats();
}