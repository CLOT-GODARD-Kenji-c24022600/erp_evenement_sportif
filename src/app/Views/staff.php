<?php
use Core\Database;

// On récupère la connexion BDD
$db = Database::getConnection();

// On récupère tous les utilisateurs "approuvés" (le vrai staff actif)
$stmt = $db->query("SELECT * FROM utilisateurs WHERE statut = 'approuve' ORDER BY nom ASC, prenom ASC");
$staff_members = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1">Équipe & Staff</h2>
            <p class="text-muted mb-0">Retrouvez et contactez les membres de l'équipe.</p>
        </div>
        <div class="w-25 min-w-200px">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Chercher un nom, un poste...">
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="staffGrid">
        <?php foreach ($staff_members as $member): 
            // Préparation du nom complet
            $fullName = trim(($member['prenom'] ?? '') . ' ' . $member['nom']);
            if (empty($fullName)) $fullName = "Utilisateur inconnu";
            
            // Badge pour le rôle
            $roleBadgeClass = $member['role'] === 'admin' ? 'bg-danger' : 'bg-primary';
            $roleName = strtoupper($member['role']);
        ?>
            <div class="col staff-card">
                <div class="card h-100 border-0 shadow-sm transition-hover">
                    <div class="card-body text-center p-4">
                        
                        <div class="mb-3 position-relative d-inline-block">
                            <?php if (!empty($member['avatar'])): ?>
                                <img src="uploads/avatars/<?= htmlspecialchars($member['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff;">
                            <?php else: ?>
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem; border: 3px solid #fff;">
                                    <?= strtoupper(substr($fullName, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            
                            <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-light rounded-circle" style="margin-bottom: 5px; margin-right: 5px;" title="Actif"></span>
                        </div>

                        <h5 class="fw-bold mb-1 staff-name"><?= htmlspecialchars($fullName) ?></h5>
                        <p class="text-muted small mb-2 staff-poste"><?= htmlspecialchars($member['poste'] ?? 'Membre du staff') ?></p>
                        <span class="badge <?= $roleBadgeClass ?> mb-3 rounded-pill px-3"><?= $roleName ?></span>

                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <a href="mailto:<?= htmlspecialchars($member['email']) ?>" class="btn btn-light btn-sm px-3 shadow-sm" title="Envoyer un email">
                                <i class="bi bi-envelope-fill text-primary"></i> Email
                            </a>
                            
                            <?php if (!empty($member['telephone'])): ?>
                                <a href="tel:<?= htmlspecialchars($member['telephone']) ?>" class="btn btn-light btn-sm px-3 shadow-sm" title="Appeler">
                                    <i class="bi bi-telephone-fill text-success"></i> Appel
                                </a>
                            <?php else: ?>
                                <button class="btn btn-light btn-sm px-3 shadow-sm disabled" title="Pas de numéro">
                                    <i class="bi bi-telephone-x text-muted"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="noResultMsg" class="text-center py-5 d-none">
        <i class="bi bi-emoji-frown fs-1 text-muted"></i>
        <h5 class="mt-3 text-muted">Aucun membre ne correspond à votre recherche.</h5>
    </div>
</div>

<style>
    .transition-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .transition-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .min-w-200px { min-width: 200px; }
</style>

<script>
    // --- SCRIPT DE RECHERCHE EN TEMPS RÉEL ---
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let cards = document.querySelectorAll('.staff-card');
        let visibleCount = 0;

        cards.forEach(function(card) {
            let name = card.querySelector('.staff-name').textContent.toLowerCase();
            let poste = card.querySelector('.staff-poste').textContent.toLowerCase();

            if (name.includes(filter) || poste.includes(filter)) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        let noResultMsg = document.getElementById('noResultMsg');
        if (visibleCount === 0) {
            noResultMsg.classList.remove('d-none');
        } else {
            noResultMsg.classList.add('d-none');
        }
    });
</script>