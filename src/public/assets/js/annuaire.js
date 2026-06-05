/**
 * YES – Your Event Solution
 * JS : Annuaire — Recherche + modals Contacts et Membres
 *
 * @file annuaire.js
 * @version 2.0  –  2026
 * AJOUTS : openContactLier + nouveaux champs contact (societe, poste, etc.)
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
    });

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
}

document.addEventListener('DOMContentLoaded', _pageInit);
window.YesPageInit = _pageInit;

// ── Modal : modifier un contact externe ───────────────────────
window.openContactEdit = function (c) {
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
  const chk = (id, val) => { const el = document.getElementById(id); if (el) el.checked = !!parseInt(val ?? 0); };

  set('ce-id',        c.id);
  set('ce-nom',       c.nom);
  set('ce-type',      c.type             ?? 'contact');
  set('ce-infos',     c.infos);
  set('ce-tel',       c.telephone);
  set('ce-mail',      c.mail);
  set('ce-comm',      c.comm);
  set('ce-urg',       c.contact_urgence);
  set('ce-telurg',    c.tel_urgence);
  set('ce-tshirt',    c.tshirt);
  set('ce-pointure',  c.pointure);
  set('ce-poids',     c.poids);
  set('ce-phone-mod', c.telephone_modele);
  chk('ce-pieces',    c.pieces_ok);

  // Nouveaux champs
  set('ce-societe', c.societe);
  set('ce-poste',   c.poste);
  set('ce-site',    c.site_web);
  set('ce-adresse', c.adresse);
  set('ce-notes',   c.notes);

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalContactEdit')
  ).show();
};

// ── Modal : lier un contact à un événement / projet ───────────
window.openContactLier = function (contactId, contactNom) {
  // Remplir l'ID dans les deux formulaires (event + projet)
  const elEv = document.getElementById('lier-contact-id-ev');
  const elPr = document.getElementById('lier-contact-id-pr');
  const elNom = document.getElementById('lier-contact-nom');

  if (elEv)  elEv.value       = contactId;
  if (elPr)  elPr.value       = contactId;
  if (elNom) elNom.textContent = contactNom;

  const modal = document.getElementById('modalLierContact');
  if (!modal) {
    console.error('Modal #modalLierContact introuvable');
    return;
  }

  bootstrap.Modal.getOrCreateInstance(modal).show();
};

// ── Modal : modifier un membre interne ────────────────────────
window.openUserEdit = function (u) {
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
  const chk = (id, val) => { const el = document.getElementById(id); if (el) el.checked = !!parseInt(val ?? 0); };

  set('ue-id',        u.id);
  set('ue-prenom',    u.prenom);
  set('ue-nom',       u.nom);
  set('ue-email',     u.email);
  set('ue-poste',     u.poste);
  set('ue-tel',       u.telephone);
  set('ue-infos',     u.infos);
  set('ue-comm',      u.comm);
  set('ue-urg',       u.contact_urgence);
  set('ue-telurg',    u.tel_urgence);
  set('ue-tshirt',    u.tshirt);
  set('ue-pointure',  u.pointure);
  set('ue-poids',     u.poids);
  set('ue-phone-mod', u.telephone_modele);
  chk('ue-pieces',    u.pieces_ok);

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalUserEdit')
  ).show();
};

// ── Modal : transférer un membre vers contacts externes ────────
window.openTransferModal = function (u) {
  const el = document.getElementById('tr-user-id');
  if (el) el.value = u.id ?? '';
  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalTransfer')
  ).show();
};