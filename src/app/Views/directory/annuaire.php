<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Annuaire des ressources.
 *
 * @file annuaire.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array $t Traductions chargées.
 */

declare(strict_types=1);

$mockData = [
    ['nom' => 'Léon Marchand',    'role' => 'Athlète',          'entite' => 'Dauphins du TOEC',  'contact' => 'leon.m@example.com'],
    ['nom' => 'Antoine Dupont',   'role' => 'Athlète',          'entite' => 'Stade Toulousain',  'contact' => 'antoine.d@example.com'],
    ['nom' => 'Stade Vélodrome',  'role' => 'Lieu',             'entite' => 'Marseille',         'contact' => 'resa@velodrome.fr'],
    ['nom' => 'Marie Dupont',     'role' => 'Médecin Bénévole', 'entite' => 'Croix-Rouge',       'contact' => 'm.dupont@croix-rouge.fr'],
    ['nom' => 'Nike France',      'role' => 'Sponsor',          'entite' => 'Équipementier',     'contact' => 'partenariat@nike.fr'],
];
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
                            <th scope="col"><?= htmlspecialchars($t['dir_th_entity'], ENT_QUOTES) ?></th>
                            <th scope="col"><?= htmlspecialchars($t['dir_th_email'], ENT_QUOTES) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mockData as $entree): ?>
                        <tr>
                            <td class="ps-4 fw-medium text-body"><?= htmlspecialchars($entree['nom'], ENT_QUOTES) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($entree['role'], ENT_QUOTES) ?></span></td>
                            <td class="text-body-secondary"><?= htmlspecialchars($entree['entite'], ENT_QUOTES) ?></td>
                            <td class="text-primary"><?= htmlspecialchars($entree['contact'], ENT_QUOTES) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </article>

</section>

<!-- Modale d'importation -->
<dialog class="modal fade" id="importModal" tabindex="-1"
        aria-labelledby="importModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow">
            <header class="modal-header bg-primary text-white border-0">
                <h2 class="modal-title fw-bold h5" id="importModalLabel">
                    <i class="bi bi-database-add me-2" aria-hidden="true"></i><?= htmlspecialchars($t['dir_modal_title'], ENT_QUOTES) ?>
                </h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </header>
            <div class="modal-body p-4 text-body">
                <p class="text-body-secondary mb-4"><?= htmlspecialchars($t['dir_modal_desc'], ENT_QUOTES) ?></p>
                <form action="#" method="POST" enctype="multipart/form-data">
                    <fieldset class="mb-3 border-0 p-0">
                        <label for="dbFile" class="form-label fw-semibold"><?= htmlspecialchars($t['dir_modal_file'], ENT_QUOTES) ?></label>
                        <input class="form-control" type="file" id="dbFile" accept=".csv,.sql">
                    </fieldset>
                </form>
            </div>
            <footer class="modal-footer border-0 bg-body-tertiary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= htmlspecialchars($t['form_cancel'], ENT_QUOTES) ?>
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <?= htmlspecialchars($t['dir_modal_upload'], ENT_QUOTES) ?>
                </button>
            </footer>
        </article>
    </div>
</dialog>
