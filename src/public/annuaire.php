<?php include '../app/Views/includes/header.php'; 

// 1. NOTRE FAUSSE BASE DE DONNÉES (Mocks)
$fausse_bdd = [
    ['nom' => 'Léon Marchand', 'role' => 'Athlète', 'entite' => 'Dauphins du TOEC', 'contact' => 'leon.m@example.com'],
    ['nom' => 'Antoine Dupont', 'role' => 'Athlète', 'entite' => 'Stade Toulousain', 'contact' => 'antoine.d@example.com'],
    ['nom' => 'Stade Vélodrome', 'role' => 'Lieu', 'entite' => 'Marseille', 'contact' => 'resa@velodrome.fr'],
    ['nom' => 'Marie Dupont', 'role' => 'Médecin Bénévole', 'entite' => 'Croix-Rouge', 'contact' => 'm.dupont@croix-rouge.fr'],
    ['nom' => 'Nike France', 'role' => 'Sponsor', 'entite' => 'Équipementier', 'contact' => 'partenariat@nike.fr'],
];
?>

<div class="container mt-5 mb-5">
    
    <!-- En-tête de la page avec le bouton d'importation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-body mb-0"><?= $t['dir_title'] ?></h1>
        </div>
        <!-- Bouton qui déclenche la modale -->
        <button type="button" class="btn btn-success fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-cloud-upload me-2"></i><?= $t['dir_import_btn'] ?>
        </button>
    </div>

    <!-- Le Tableau affichant la fausse BDD -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-4"><?= $t['dir_th_name'] ?></th>
                            <th scope="col"><?= $t['dir_th_role'] ?></th>
                            <th scope="col"><?= $t['dir_th_entity'] ?></th>
                            <th scope="col"><?= $t['dir_th_email'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fausse_bdd as $entree): ?>
                        <tr>
                            <td class="ps-4 fw-medium text-body"><?= htmlspecialchars($entree['nom']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($entree['role']) ?></span></td>
                            <td class="text-body-secondary"><?= htmlspecialchars($entree['entite']) ?></td>
                            <td class="text-primary"><?= htmlspecialchars($entree['contact']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- LA MODALE D'IMPORTATION (Cachée par défaut) -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="importModalLabel"><i class="bi bi-database-add me-2"></i><?= $t['dir_modal_title'] ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-body">
                <p class="text-body-secondary mb-4"><?= $t['dir_modal_desc'] ?></p>
                
                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="dbFile" class="form-label fw-semibold"><?= $t['dir_modal_file'] ?></label>
                        <input class="form-control" type="file" id="dbFile" accept=".csv, .sql">
                    </div>
                </form>

            </div>
            <div class="modal-footer border-0 bg-body-tertiary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= $t['form_cancel'] ?></button>
                <!-- Bouton fictif pour l'instant -->
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?= $t['dir_modal_upload'] ?></button>
            </div>
        </div>
    </div>
</div>

<?php include '../app/Views/includes/footer.php'; ?>