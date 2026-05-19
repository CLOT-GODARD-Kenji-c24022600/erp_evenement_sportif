<?php

/**
 * YES – Your Event Solution
 * Vue : Annuaire enrichi – Contacts externes + Membres internes
 *
 * @file annuaire.php
 * @version 2.0  –  2026
 *
 * Variables attendues :
 * @var array $t            Traductions.
 * @var array $contacts     Contacts externes (table contacts).
 * @var array $usersInterne Membres internes approuvés (table utilisateurs).
 * @var string|null $contactMsg  Message flash.
 * @var string      $contactType 'success' | 'error'.
 */

declare(strict_types=1);
?>

<div class="container-fluid py-4">

    <header class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <hgroup class="flex-grow-1">
            <h1 class="fw-bold fs-4 mb-0">
                <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                Annuaire
            </h1>
            <p class="text-body-secondary small mb-0">Contacts externes · Membres de l'équipe</p>
        </hgroup>
        <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                data-bs-toggle="modal" data-bs-target="#modalContactCreate">
            <i class="bi bi-person-plus-fill me-1"></i> Ajouter un contact
        </button>
    </header>

    <?php if (!empty($contactMsg)): ?>
    <aside class="alert alert-<?= $contactType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-<?= $contactType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
        <?= htmlspecialchars((string)$contactMsg, ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </aside>
    <?php endif; ?>

    <!-- Recherche globale -->
    <div class="mb-4" style="max-width:360px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-body border-end-0">
                <i class="bi bi-search text-body-secondary"></i>
            </span>
            <input type="search" id="annuaire-search" class="form-control border-start-0 rounded-end-3"
                   placeholder="Rechercher dans l'annuaire…" aria-label="Rechercher">
        </div>
    </div>

    <!-- ── ONGLETS ──────────────────────────────────────────── -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                    data-bs-target="#pane-membres" type="button" role="tab">
                <i class="bi bi-people-fill me-1 text-primary"></i>
                Membres de l'équipe
                <span class="badge bg-primary ms-1"><?= count($usersInterne) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="tab"
                    data-bs-target="#pane-contacts" type="button" role="tab">
                <i class="bi bi-person-rolodex me-1 text-secondary"></i>
                Contacts externes
                <span class="badge bg-secondary ms-1"><?= count($contacts) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ══════════════════════════════════════════════════
             MEMBRES INTERNES
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="pane-membres" role="tabpanel">

            <?php if (empty($usersInterne)): ?>
            <p class="text-body-secondary text-center py-5">
                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                Aucun membre approuvé dans l'équipe.
            </p>
            <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="membres-grid">
                <?php foreach ($usersInterne as $u):
                    $initials = mb_strtoupper(mb_substr($u['prenom'] ?? '?', 0, 1) . mb_substr($u['nom'] ?? '?', 0, 1));
                    $fullName = trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''));
                    $avatarColors = ['primary','success','warning','danger','info','secondary'];
                    $colorIndex   = crc32($fullName) % count($avatarColors);
                    $avatarColor  = $avatarColors[abs($colorIndex)];
                ?>
                <div class="col annuaire-card"
                     data-search="<?= htmlspecialchars(strtolower($fullName.' '.($u['poste']??'').' '.($u['email']??'')), ENT_QUOTES) ?>">
                    <article class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <header class="d-flex align-items-start gap-3 mb-3">
                                <div class="rounded-circle bg-<?= $avatarColor ?>-subtle border border-<?= $avatarColor ?>-subtle
                                            d-flex align-items-center justify-content-center fw-bold text-<?= $avatarColor ?>"
                                     style="width:48px;height:48px;flex-shrink:0;font-size:1.1rem;">
                                    <?= $initials ?>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h2 class="fw-bold h6 mb-1"><?= htmlspecialchars($fullName, ENT_QUOTES) ?></h2>
                                    <p class="small text-body-secondary mb-0">
                                        <?= htmlspecialchars((string)($u['poste'] ?? '—'), ENT_QUOTES) ?>
                                    </p>
                                    <?php if (!empty($u['infos'])): ?>
                                    <p class="small text-body-secondary mb-0 fst-italic">
                                        <?= htmlspecialchars((string)$u['infos'], ENT_QUOTES) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <span class="badge rounded-pill bg-<?= $u['role'] === 'admin' ? 'danger' : 'secondary' ?>-subtle
                                             text-<?= $u['role'] === 'admin' ? 'danger' : 'secondary' ?>
                                             border border-<?= $u['role'] === 'admin' ? 'danger' : 'secondary' ?>-subtle">
                                    <?= ucfirst(htmlspecialchars((string)($u['role'] ?? 'staff'), ENT_QUOTES)) ?>
                                </span>
                            </header>

                            <ul class="list-unstyled small text-body-secondary mb-3">
                                <?php if (!empty($u['telephone'])): ?>
                                <li class="mb-1">
                                    <i class="bi bi-telephone-fill me-2 text-primary"></i>
                                    <a href="tel:<?= htmlspecialchars($u['telephone'], ENT_QUOTES) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($u['telephone'], ENT_QUOTES) ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (!empty($u['email'])): ?>
                                <li class="mb-1">
                                    <i class="bi bi-envelope-fill me-2 text-primary"></i>
                                    <a href="mailto:<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($u['email'], ENT_QUOTES) ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (!empty($u['contact_urgence'])): ?>
                                <li class="mb-1">
                                    <i class="bi bi-heart-pulse-fill me-2 text-danger"></i>
                                    Urgence : <?= htmlspecialchars($u['contact_urgence'], ENT_QUOTES) ?>
                                    <?= !empty($u['tel_urgence']) ? ' — '.htmlspecialchars($u['tel_urgence'], ENT_QUOTES) : '' ?>
                                </li>
                                <?php endif; ?>
                                <?php if (!empty($u['comm'])): ?>
                                <li class="mb-1">
                                    <i class="bi bi-chat-left-text-fill me-2 text-info"></i>
                                    <?= htmlspecialchars($u['comm'], ENT_QUOTES) ?>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <!-- Infos staff -->
                            <?php $hasStaff = !empty($u['tshirt']) || !empty($u['pointure']) || !empty($u['poids']) || !empty($u['telephone_modele']); ?>
                            <?php if ($hasStaff): ?>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php if (!empty($u['tshirt'])): ?>
                                <span class="badge bg-body-secondary text-body border rounded-pill">
                                    <i class="bi bi-arrows-angle-expand me-1"></i>T-Shirt <?= htmlspecialchars($u['tshirt'], ENT_QUOTES) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($u['pointure'])): ?>
                                <span class="badge bg-body-secondary text-body border rounded-pill">
                                    <i class="bi bi-boot me-1"></i>Pointure <?= htmlspecialchars($u['pointure'], ENT_QUOTES) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($u['poids'])): ?>
                                <span class="badge bg-body-secondary text-body border rounded-pill">
                                    <i class="bi bi-speedometer2 me-1"></i><?= htmlspecialchars((string)$u['poids'], ENT_QUOTES) ?> kg
                                </span>
                                <?php endif; ?>
                                <?php if ($u['pieces_ok']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Pièces OK
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <footer class="d-flex justify-content-end gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-3"
                                        title="Copier vers contacts externes"
                                        onclick="openTransferModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3"
                                        onclick="openUserEdit(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil me-1"></i> Modifier
                                </button>
                            </footer>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
            <p id="membres-no-results" class="text-body-secondary text-center py-4" style="display:none;">
                <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>Aucun membre ne correspond.
            </p>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════
             CONTACTS EXTERNES
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="pane-contacts" role="tabpanel">

            <?php if (empty($contacts)): ?>
            <p class="text-body-secondary text-center py-5">
                <i class="bi bi-person-rolodex fs-2 d-block mb-2 opacity-50"></i>
                Aucun contact externe. Ajoute le premier !
            </p>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="contacts-table">
                        <thead class="table-dark small">
                            <tr>
                                <th class="ps-3">Nom</th>
                                <th>Infos / Rôle</th>
                                <th>Téléphone</th>
                                <th>Mail</th>
                                <th>Urgence</th>
                                <th>T-Shirt</th>
                                <th>Pointure</th>
                                <th>Poids</th>
                                <th>Pièces</th>
                                <th>Type</th>
                                <th>Comm</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contacts as $c): ?>
                        <tr class="annuaire-row"
                            data-search="<?= htmlspecialchars(strtolower(($c['nom']??'').' '.($c['infos']??'').' '.($c['telephone']??'').' '.($c['mail']??'')), ENT_QUOTES) ?>">
                            <td class="ps-3 fw-medium"><?= htmlspecialchars((string)$c['nom'], ENT_QUOTES) ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($c['infos']??'—'), ENT_QUOTES) ?></td>
                            <td class="small">
                                <?php if (!empty($c['telephone'])): ?>
                                <a href="tel:<?= htmlspecialchars($c['telephone'], ENT_QUOTES) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($c['telephone'], ENT_QUOTES) ?>
                                </a>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="small">
                                <?php if (!empty($c['mail'])): ?>
                                <a href="mailto:<?= htmlspecialchars($c['mail'], ENT_QUOTES) ?>" class="text-decoration-none text-primary">
                                    <?= htmlspecialchars($c['mail'], ENT_QUOTES) ?>
                                </a>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="small">
                                <?= htmlspecialchars((string)($c['contact_urgence']??''), ENT_QUOTES) ?>
                                <?= !empty($c['tel_urgence']) ? '<br><span class="text-body-secondary">'.htmlspecialchars($c['tel_urgence'], ENT_QUOTES).'</span>' : '' ?>
                            </td>
                            <td class="small text-center"><?= htmlspecialchars((string)($c['tshirt']??'—'), ENT_QUOTES) ?></td>
                            <td class="small text-center"><?= htmlspecialchars((string)($c['pointure']??'—'), ENT_QUOTES) ?></td>
                            <td class="small text-center"><?= !empty($c['poids']) ? htmlspecialchars((string)$c['poids'], ENT_QUOTES).' kg' : '—' ?></td>
                            <td class="text-center">
                                <?= $c['pieces_ok'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-body-secondary"></i>' ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $c['type'] === 'staff' ? 'primary' : 'secondary' ?>-subtle
                                             text-<?= $c['type'] === 'staff' ? 'primary' : 'secondary' ?>
                                             border border-<?= $c['type'] === 'staff' ? 'primary' : 'secondary' ?>-subtle">
                                    <?= $c['type'] === 'staff' ? 'Staff' : 'Contact' ?>
                                </span>
                            </td>
                            <td class="small text-body-secondary" style="max-width:180px;">
                                <?= htmlspecialchars((string)($c['comm']??''), ENT_QUOTES) ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openContactEdit(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="/annuaire" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce contact ?')">
                                    <input type="hidden" name="contact_action" value="delete">
                                    <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <p id="contacts-no-results" class="text-body-secondary text-center py-4" style="display:none;">
                <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>Aucun contact ne correspond.
            </p>
            <?php endif; ?>
        </div>

    </div><!-- /.tab-content -->
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════════ -->



<!-- Modal Modifier Contact -->
<div class="modal fade" id="modalContactEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/annuaire">
          <input type="hidden" name="contact_action" value="update">
          <input type="hidden" name="contact_id" id="ce-id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" id="ce-nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type</label>
              <select name="type" id="ce-type" class="form-select rounded-3">
                <option value="contact">Contact</option>
                <option value="staff">Staff</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Infos / Rôle</label>
              <input type="text" name="infos" id="ce-infos" class="form-control rounded-3" placeholder="ex: Coordinateur logistique">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" id="ce-tel" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mail</label>
              <input type="email" name="mail" id="ce-mail" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Commentaire (Comm)</label>
              <input type="text" name="comm" id="ce-comm" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-shield-exclamation me-1"></i>Contact d'urgence</p></div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom contact urgence</label>
              <input type="text" name="contact_urgence" id="ce-urg" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tél urgence</label>
              <input type="text" name="tel_urgence" id="ce-telurg" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-person-badge me-1"></i>Infos staff</p></div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">T-Shirt</label>
              <select name="tshirt" id="ce-tshirt" class="form-select rounded-3">
                <option value="">—</option>
                <?php foreach (['XS','S','M','L','XL','XXL','XXXL'] as $sz): ?>
                <option value="<?= $sz ?>"><?= $sz ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Pointure</label>
              <input type="text" name="pointure" id="ce-pointure" class="form-control rounded-3" placeholder="ex: 42">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Poids (kg)</label>
              <input type="number" step="0.1" name="poids" id="ce-poids" class="form-control rounded-3">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Modèle téléphone</label>
              <input type="text" name="telephone_modele" id="ce-phone-mod" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pieces_ok" id="ce-pieces">
                <label class="form-check-label fw-semibold" for="ce-pieces">Pièces OK</label>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Modifier Membre interne -->
<div class="modal fade" id="modalUserEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le membre</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/annuaire">
          <input type="hidden" name="contact_action" value="update_user">
          <input type="hidden" name="user_id" id="ue-id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Prénom</label>
              <input type="text" name="prenom" id="ue-prenom" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom</label>
              <input type="text" name="nom" id="ue-nom" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" id="ue-email" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Poste</label>
              <input type="text" name="poste" id="ue-poste" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" id="ue-tel" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Infos</label>
              <input type="text" name="infos" id="ue-infos" class="form-control rounded-3" placeholder="ex: Pilote">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Commentaire (Comm)</label>
              <input type="text" name="comm" id="ue-comm" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-shield-exclamation me-1"></i>Contact d'urgence</p></div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom contact urgence</label>
              <input type="text" name="contact_urgence" id="ue-urg" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tél urgence</label>
              <input type="text" name="tel_urgence" id="ue-telurg" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-person-badge me-1"></i>Infos staff</p></div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">T-Shirt</label>
              <select name="tshirt" id="ue-tshirt" class="form-select rounded-3">
                <option value="">—</option>
                <?php foreach (['XS','S','M','L','XL','XXL','XXXL'] as $sz): ?>
                <option value="<?= $sz ?>"><?= $sz ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Pointure</label>
              <input type="text" name="pointure" id="ue-pointure" class="form-control rounded-3">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Poids (kg)</label>
              <input type="number" step="0.1" name="poids" id="ue-poids" class="form-control rounded-3">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Modèle téléphone</label>
              <input type="text" name="telephone_modele" id="ue-phone-mod" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pieces_ok" id="ue-pieces">
                <label class="form-check-label fw-semibold" for="ue-pieces">Pièces OK</label>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Créer Contact -->
<div class="modal fade" id="modalContactCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Nouveau contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/annuaire">
          <input type="hidden" name="contact_action" value="create">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type</label>
              <select name="type" class="form-select rounded-3">
                <option value="contact">Contact</option>
                <option value="staff">Staff</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Infos / Rôle</label>
              <input type="text" name="infos" class="form-control rounded-3" placeholder="ex: Coordinateur logistique">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mail</label>
              <input type="email" name="mail" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Commentaire (Comm)</label>
              <input type="text" name="comm" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-shield-exclamation me-1"></i>Contact d'urgence</p></div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom contact urgence</label>
              <input type="text" name="contact_urgence" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tél urgence</label>
              <input type="text" name="tel_urgence" class="form-control rounded-3">
            </div>
            <div class="col-12"><hr class="my-1"><p class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-person-badge me-1"></i>Infos staff</p></div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">T-Shirt</label>
              <select name="tshirt" class="form-select rounded-3">
                <option value="">—</option>
                <?php foreach (['XS','S','M','L','XL','XXL','XXXL'] as $sz): ?>
                <option value="<?= $sz ?>"><?= $sz ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Pointure</label>
              <input type="text" name="pointure" class="form-control rounded-3" placeholder="ex: 42">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Poids (kg)</label>
              <input type="number" step="0.1" name="poids" class="form-control rounded-3">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Modèle téléphone</label>
              <input type="text" name="telephone_modele" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pieces_ok" id="cc-pieces">
                <label class="form-check-label fw-semibold" for="cc-pieces">Pièces OK</label>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal : Transférer membre → contact externe -->
<div class="modal fade" id="modalTransfer" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-box-arrow-right me-2 text-secondary"></i>Copier vers contacts externes
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-body-secondary small mb-3">
          Cela va créer une fiche dans <strong>Contacts externes</strong> avec les infos de ce membre.
          Le compte ERP reste inchangé.
        </p>
        <form method="POST" action="/annuaire">
          <input type="hidden" name="contact_action" value="transfer_user">
          <input type="hidden" name="transfer_user_id" id="tr-user-id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Type dans contacts externes</label>
            <select name="type" class="form-select rounded-3">
              <option value="staff">Staff</option>
              <option value="contact">Contact</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-secondary rounded-3 fw-semibold">
              <i class="bi bi-box-arrow-right me-1"></i>Copier
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>