<?php
use Core\Database;

$db = Database::getConnection();

$recherche = trim($_GET['q'] ?? '');
$resultats_staff = [];
$resultats_events = [];
$resultats_projets = [];

if (!empty($recherche)) {
    $term = '%' . $recherche . '%';

    // 1. RECHERCHE DANS LE STAFF
    try {
        $stmt_staff = $db->prepare("
            SELECT * FROM utilisateurs 
            WHERE (nom LIKE ? OR prenom LIKE ? OR poste LIKE ?) 
            AND statut = 'approuve'
        ");
        $stmt_staff->execute([$term, $term, $term]);
        $resultats_staff = $stmt_staff->fetchAll();
    } catch (PDOException $e) {}

    // 2. RECHERCHE DANS LES ÉVÉNEMENTS (nom, lieu, description)
    try {
        $stmt_events = $db->prepare("
            SELECT * FROM evenements 
            WHERE nom LIKE ? OR lieu LIKE ? OR description LIKE ?
        ");
        $stmt_events->execute([$term, $term, $term]);
        $resultats_events = $stmt_events->fetchAll();
    } catch (PDOException $e) {}

    // 3. RECHERCHE DANS LES PROJETS (nom, description)
    try {
        $stmt_projets = $db->prepare("
            SELECT * FROM projets 
            WHERE nom LIKE ? OR description LIKE ?
        ");
        $stmt_projets->execute([$term, $term]);
        $resultats_projets = $stmt_projets->fetchAll();
    } catch (PDOException $e) {}
}
?>

<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-search fs-3 text-primary me-3"></i>
        <div>
            <h2 class="fw-bold mb-0">Résultats de recherche</h2>
            <?php if (!empty($recherche)): ?>
                <p class="text-muted mb-0">Vous avez cherché : <strong>"<?= htmlspecialchars($recherche) ?>"</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($recherche)): ?>
        <div class="alert alert-warning shadow-sm border-0">Veuillez entrer un terme de recherche dans la barre du haut.</div>
    
    <?php elseif (empty($resultats_staff) && empty($resultats_events) && empty($resultats_projets)): ?>
        <div class="text-center py-5 bg-white rounded shadow-sm border-0 mt-4">
            <i class="bi bi-emoji-frown fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted fw-bold">Oups, aucun résultat !</h5>
            <p class="text-muted">Nous n'avons trouvé aucun membre du staff, événement ou projet pour "<?= htmlspecialchars($recherche) ?>".</p>
        </div>
        
    <?php else: ?>

        <?php if (!empty($resultats_staff)): ?>
            <div class="mb-5">
                <h5 class="fw-bold mb-3 text-primary text-uppercase" style="letter-spacing: 1px;">
                    <i class="bi bi-people-fill me-2"></i>Staff (<?= count($resultats_staff) ?>)
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($resultats_staff as $user): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm transition-hover">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; border: 3px solid #fff;">
                                                <?= strtoupper(substr($user['nom'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . $user['nom'])) ?></h5>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($user['poste'] ?? 'Staff') ?></p>
                                    <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="btn btn-sm btn-outline-primary mt-3 px-4 rounded-pill">
                                        <i class="bi bi-envelope me-1"></i> Contacter
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($resultats_projets)): ?>
            <div class="mb-5">
                <h5 class="fw-bold mb-3 text-warning text-uppercase" style="letter-spacing: 1px;">
                    <i class="bi bi-folder-fill me-2"></i>Projets (<?= count($resultats_projets) ?>)
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($resultats_projets as $projet): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm transition-hover position-relative">
                                <div class="card-body p-4 text-start">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                                            <i class="bi bi-folder fs-4"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0 text-dark">
                                            <?= htmlspecialchars($projet['nom']) ?>
                                        </h5>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <?php 
                                            $desc = $projet['description'] ?? 'Aucune description.';
                                            echo htmlspecialchars(mb-strimwidth($desc, 0, 100, '...')); 
                                        ?>
                                    </p>
                                    <?php if (!empty($projet['date_creation'])): ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-clock me-1"></i> Créé le <?= date('d/m/Y', strtotime($projet['date_creation'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-top-0 pt-0 pb-4 text-start px-4">
                                    <a href="#" class="btn btn-sm btn-warning px-4 rounded-pill stretched-link text-white fw-bold">
                                        Ouvrir le projet
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($resultats_events)): ?>
            <div class="mb-5">
                <h5 class="fw-bold mb-3 text-success text-uppercase" style="letter-spacing: 1px;">
                    <i class="bi bi-calendar-event-fill me-2"></i>Événements (<?= count($resultats_events) ?>)
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($resultats_events as $event): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm transition-hover position-relative">
                                <div class="card-body p-4 text-start">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                            <i class="bi bi-calendar-check fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-dark">
                                                <?= htmlspecialchars($event['nom']) ?>
                                            </h5>
                                            <?php if (!empty($event['lieu'])): ?>
                                                <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($event['lieu']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <?php 
                                            $desc = $event['description'] ?? 'Aucune description disponible.';
                                            echo htmlspecialchars(mb-strimwidth($desc, 0, 100, '...')); 
                                        ?>
                                    </p>
                                    <?php if (!empty($event['date_debut'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-calendar me-1"></i> Du <?= date('d/m/Y', strtotime($event['date_debut'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-top-0 pt-0 pb-4 text-start px-4">
                                    <a href="#" class="btn btn-sm btn-success px-4 rounded-pill stretched-link">
                                        Voir les détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<style>
    .transition-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .transition-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>