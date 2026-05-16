/**
 * YES – Your Event Solution
 * JS : Annuaire — Recherche + modals Contacts et Membres
 *
 * @file annuaire.js
 * @version 1.0  –  2026
 */

'use strict';

// ── Recherche en temps réel ────────────────────────────────────

function _pageInit() {

  const input = document.getElementById('annuaire-search');
  if (!input) return;

  input.addEventListener('input', () => {
    const query = input.value.trim().toLowerCase();

    // -- Cartes membres
    let visibleMembers = 0;
    document.querySelectorAll('.annuaire-card').forEach(card => {
      const match = !query || card.dataset.search.includes(query);
      card.style.display = match ? '' : 'none';
      if (match) visibleMembers++;
    }); // ✅ ferme le .forEach()

    const noMembers = document.getElementById('membres-no-results');
    if (noMembers) noMembers.style.display = visibleMembers === 0 ? '' : 'none';

    // -- Lignes contacts
    let visibleContacts = 0;
    document.querySelectorAll('.annuaire-row').forEach(row => {
      const match = !query || row.dataset.search.includes(query);
      row.style.display = match ? '' : 'none';
      if (match) visibleContacts++;
    });

    const noContacts = document.getElementById('contacts-no-results');
    if (noContacts) noContacts.style.display = visibleContacts === 0 ? '' : 'none';
  });
} // ✅ ferme _pageInit

// Chargement initial (page complète)
document.addEventListener('DOMContentLoaded', _pageInit);

// Navigation SPA : appelé par routeur.js après injection AJAX
window.YesPageInit = _pageInit;

// ── Modal : modifier un contact externe ───────────────────────
function openContactEdit(c) {
  document.getElementById('ce-id').value        = c.id               ?? '';
  document.getElementById('ce-nom').value       = c.nom              ?? '';
  document.getElementById('ce-type').value      = c.type             ?? 'contact';
  document.getElementById('ce-infos').value     = c.infos            ?? '';
  document.getElementById('ce-tel').value       = c.telephone        ?? '';
  document.getElementById('ce-mail').value      = c.mail             ?? '';
  document.getElementById('ce-comm').value      = c.comm             ?? '';
  document.getElementById('ce-urg').value       = c.contact_urgence  ?? '';
  document.getElementById('ce-telurg').value    = c.tel_urgence      ?? '';
  document.getElementById('ce-tshirt').value    = c.tshirt           ?? '';
  document.getElementById('ce-pointure').value  = c.pointure         ?? '';
  document.getElementById('ce-poids').value     = c.poids            ?? '';
  document.getElementById('ce-phone-mod').value = c.telephone_modele ?? '';
  document.getElementById('ce-pieces').checked  = !!parseInt(c.pieces_ok ?? 0);

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalContactEdit')
  ).show();
}

// ── Modal : modifier un membre interne ────────────────────────
function openUserEdit(u) {
  document.getElementById('ue-id').value        = u.id               ?? '';
  document.getElementById('ue-prenom').value    = u.prenom           ?? '';
  document.getElementById('ue-nom').value       = u.nom              ?? '';
  document.getElementById('ue-email').value     = u.email            ?? '';
  document.getElementById('ue-poste').value     = u.poste            ?? '';
  document.getElementById('ue-tel').value       = u.telephone        ?? '';
  document.getElementById('ue-infos').value     = u.infos            ?? '';
  document.getElementById('ue-comm').value      = u.comm             ?? '';
  document.getElementById('ue-urg').value       = u.contact_urgence  ?? '';
  document.getElementById('ue-telurg').value    = u.tel_urgence      ?? '';
  document.getElementById('ue-tshirt').value    = u.tshirt           ?? '';
  document.getElementById('ue-pointure').value  = u.pointure         ?? '';
  document.getElementById('ue-poids').value     = u.poids            ?? '';
  document.getElementById('ue-phone-mod').value = u.telephone_modele ?? '';
  document.getElementById('ue-pieces').checked  = !!parseInt(u.pieces_ok ?? 0);

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalUserEdit')
  ).show();
}

// ── Modal : transférer un membre vers contacts externes ────────
function openTransferModal(u) {
  document.getElementById('tr-user-id').value = u.id ?? '';
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalTransfer')
  ).show();
}