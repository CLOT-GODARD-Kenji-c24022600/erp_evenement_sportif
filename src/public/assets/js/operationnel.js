/**
 * YES – Your Event Solution
 * JS : Page Opérationnel — Budget / Planning / Matériel / Facturation
 *
 * @file operationnel.js
 * @version 1.5  –  2026
 */

'use strict';

const OPS_TAB_KEY = 'ops_active_tab';

function fmtEur(value) {
  return parseFloat(value || 0)
    .toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
}

function updateFcTotal() {
  const pu  = parseFloat(document.getElementById('fc-pu')?.value)  || 0;
  const qte = parseFloat(document.getElementById('fc-qte')?.value) || 0;
  const el  = document.getElementById('fc-total');
  if (el) el.value = fmtEur(pu * qte);
}

function updateFeTotal() {
  const pu  = parseFloat(document.getElementById('fe-pu')?.value)  || 0;
  const qte = parseFloat(document.getElementById('fe-qte')?.value) || 0;
  const el  = document.getElementById('fe-total');
  if (el) el.value = fmtEur(pu * qte);
}

function openBudgetEdit(data) {
  document.getElementById('be-id').value    = data.id              ?? '';
  document.getElementById('be-type').value  = data.type            ?? 'charge';
  document.getElementById('be-cat').value   = data.categorie       ?? '';
  document.getElementById('be-scat').value  = data.sous_categorie  ?? '';
  document.getElementById('be-lib').value   = data.libelle         ?? '';
  document.getElementById('be-prev').value  = data.previsionnel    ?? 0;
  document.getElementById('be-comp').value  = data.comparatif      ?? 0;
  document.getElementById('be-note').value  = data.note            ?? '';
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalBudgetEdit')
  ).show();
}

function openPlanningEdit(data) {
  document.getElementById('pe-id').value     = data.id         ?? '';
  document.getElementById('pe-tache').value  = data.tache      ?? '';
  document.getElementById('pe-statut').value = data.statut     ?? 'wip';
  document.getElementById('pe-ordre').value  = data.ordre      ?? 0;
  document.getElementById('pe-debut').value  = data.date_debut ?? '';
  document.getElementById('pe-fin').value    = data.date_fin   ?? '';
  document.getElementById('pe-note').value   = data.note       ?? '';
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalPlanningEdit')
  ).show();
}

function openMaterielEdit(data) {
  document.getElementById('me-id').value    = data.id          ?? '';
  document.getElementById('me-nom').value   = data.nom         ?? '';
  document.getElementById('me-qte').value   = data.quantite    ?? 1;
  document.getElementById('me-four').value  = data.fournisseur ?? '';
  document.getElementById('me-din').value   = data.date_in     ?? '';
  document.getElementById('me-dout').value  = data.date_out    ?? '';
  document.getElementById('me-comm').value  = data.commentaire ?? '';
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalMaterielEdit')
  ).show();
}

function openFacturationEdit(data) {
  document.getElementById('fe-id').value         = data.id            ?? '';
  document.getElementById('fe-cat').value        = data.categorie     ?? '';
  document.getElementById('fe-poste').value      = data.poste         ?? '';
  document.getElementById('fe-prest').value      = data.prestataire   ?? '';
  document.getElementById('fe-cont').value       = data.contact       ?? '';
  document.getElementById('fe-tel').value        = data.telephone     ?? '';
  document.getElementById('fe-mail').value       = data.mail          ?? '';
  document.getElementById('fe-pu').value         = data.prix_unitaire ?? 0;
  document.getElementById('fe-qte').value        = data.quantite      ?? 1;
  document.getElementById('fe-note').value       = data.note          ?? '';
  document.getElementById('fe-devis').checked    = !!parseInt(data.statut_devis    ?? 0);
  document.getElementById('fe-facture').checked  = !!parseInt(data.statut_facture  ?? 0);
  document.getElementById('fe-virement').checked = !!parseInt(data.statut_virement ?? 0);
  updateFeTotal();
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalFacturationEdit')
  ).show();
}

function _pageInit() {

  // ✅ Effacer l'état "active" hardcodé par PHP sur #pane-budget
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
    btn.classList.remove('active');
    btn.setAttribute('aria-selected', 'false');
    btn.setAttribute('tabindex', '-1');
  });
  document.querySelectorAll('.tab-pane').forEach(pane => {
    pane.classList.remove('show', 'active');
  });

  // ✅ Initialiser Bootstrap Tabs explicitement
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
    bootstrap.Tab.getOrCreateInstance(btn);
  });

  // ✅ Restaurer l'onglet depuis le hash (mis par PHP dans la redirect)
  requestAnimationFrame(() => {
    const hash   = location.hash;
    const saved  = sessionStorage.getItem(OPS_TAB_KEY);
    const target = hash || saved || '#pane-budget';

    const btn = document.querySelector(`[data-bs-target="${target}"]`);
    if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();

    sessionStorage.removeItem(OPS_TAB_KEY);
  });

  // ✅ Mémoriser l'onglet dans le hash au changement
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
    btn.addEventListener('shown.bs.tab', () => {
      const tabId = btn.dataset.bsTarget;
      const url   = new URL(window.location.href);
      url.hash    = tabId;
      history.replaceState(null, '', url.toString());
    });
  });

  // ✅ Avant chaque submit : remplir le champ active_tab avec l'onglet courant
  document.addEventListener('submit', (e) => {
    const activeTab = document.querySelector('[data-bs-toggle="tab"].active');
    if (!activeTab?.dataset?.bsTarget) return;
    e.target.querySelectorAll('.js-active-tab').forEach(input => {
      input.value = activeTab.dataset.bsTarget;
    });
  }, true);

  // ✅ Sauvegarder avant submit des selects contexte (event/projet)
  document.querySelectorAll('select[onchange="this.form.submit()"]').forEach(sel => {
    sel.addEventListener('change', () => {
      const activeTab = document.querySelector('[data-bs-toggle="tab"].active');
      if (activeTab?.dataset?.bsTarget) {
        sessionStorage.setItem(OPS_TAB_KEY, activeTab.dataset.bsTarget);
      }
    });
  });

  // Listeners calcul total facturation
  const fcPu  = document.getElementById('fc-pu');
  const fcQte = document.getElementById('fc-qte');
  if (fcPu)  fcPu.addEventListener('input', updateFcTotal);
  if (fcQte) fcQte.addEventListener('input', updateFcTotal);

  const fePu  = document.getElementById('fe-pu');
  const feQte = document.getElementById('fe-qte');
  if (fePu)  fePu.addEventListener('input', updateFeTotal);
  if (feQte) feQte.addEventListener('input', updateFeTotal);
}

document.addEventListener('DOMContentLoaded', _pageInit);
window.YesPageInit = _pageInit;