<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Annuaire des ressources.
 *
 * @file annuaire.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 *
 * Variables attendues :
 * @var array $t Traductions chargées.
 * @var array $utilisateurs Liste des utilisateurs issue de la BDD.
 */

declare(strict_types=1);

?>
<section class="container mt-5 mb-5">

    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-body mb-0"><?= htmlspecialchars($t['dir_title'], ENT_QUOTES) ?></h1>
        <button type="button" class="btn btn-success fw-semibold shadow-sm"
                data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-cloud-upload me-2" aria-hidden="true"></i><?= htmlspecialchars($t['dir_import_btn'], ENT_QUOTES) ?>
        </button>
    </header>

    <article class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-4"><?= htmlspecialchars($t['dir_th_name'], ENT_QUOTES) ?></th>
                            <th scope="col"><?= htmlspecialchars($t['dir_th_role'], ENT_QUOTES) ?></th>
                            <th scope="col">Statut</th>
                            <th scope="col"><?= htmlspecialchars($t['dir_th_email'], ENT_QUOTES) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utilisateurs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Aucun utilisateur dans l'annuaire.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($utilisateurs as $user): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-body">
                                    <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom'], ENT_QUOTES) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'secondary' ?>">
                                        <?= htmlspecialchars(ucfirst($user['role'] ?? 'staff'), ENT_QUOTES) ?>
                                    </span>
                                </td>
                                <td class="text-body-secondary">
                                    <?php 
                                        $statut = $user['statut'] ?? 'en_attente';
                                        $badgeClass = ($statut === 'approuve') ? 'success' : (($statut === 'rejete') ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?>-emphasis">
                                        <?= htmlspecialchars(ucfirst($statut), ENT_QUOTES) ?>
                                    </span>
                                </td>
                                <td class="text-primary">
                                    <a href="mailto:<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($user['email'], ENT_QUOTES) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </article>

</section>

<div class="modal fade" id="importModal" tabindex="-1"
        aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow">
            <header class="modal-header bg-primary text-white border-0">
                <h2 class="modal-title fw-bold h5" id="importModalLabel">
                    <i class="bi bi-database-add me-2" aria-hidden="true"></i><?= htmlspecialchars($t['dir_modal_title'], ENT_QUOTES) ?>
                </h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </header>
            
            <form action="/import_csv" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 text-body">
                    <p class="text-body-secondary mb-4"><?= htmlspecialchars($t['dir_modal_desc'], ENT_QUOTES) ?></p>
                    <fieldset class="mb-3 border-0 p-0">
                        <label for="dbFile" class="form-label fw-semibold"><?= htmlspecialchars($t['dir_modal_file'], ENT_QUOTES) ?></label>
                        <input class="form-control" type="file" id="dbFile" name="dbFile" accept=".csv" required>
                    </fieldset>
                </div>
                <footer class="modal-footer border-0 bg-body-tertiary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= htmlspecialchars($t['form_cancel'], ENT_QUOTES) ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= htmlspecialchars($t['dir_modal_upload'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </div>
</div>