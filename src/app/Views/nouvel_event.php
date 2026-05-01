<div class="container mb-5" style="max-width: 800px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h2 class="fs-4 fw-bold mb-0 text-body"><?= $t['app_name'] ?></h2>
        <span class="text-body-secondary"><?= $t['nav_dashboard'] ?></span>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-5">
            
            <h3 class="mb-4 fw-bold fs-4 text-body"><?= $t['form_page_title'] ?></h3>
            
            <!-- Affichage du message d'erreur venant du PHP (Sécurité serveur) -->
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $_SESSION['error_msg'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>

            <!-- NOUVEAU : Ajout de la classe "needs-validation" et de l'attribut "novalidate" -->
            <form action="/traitement_event.php" method="POST" class="needs-validation" novalidate>
                
                <div class="mb-4">
                    <label for="nom_event" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_event_name'] ?></label>
                    <input type="text" class="form-control form-control-lg fs-6" id="nom_event" name="nom_event" placeholder="<?= $t['form_event_name_ph'] ?>" required>
                </div>

                <div class="mb-4">
                    <label for="type_sport" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_sport_type'] ?></label>
                    <select class="form-select form-select-lg fs-6" id="type_sport" name="type_sport" required>
                        <option value="" selected disabled><?= $t['form_sport_select'] ?></option>
                        <option value="football"><?= $t['sport_football'] ?></option>
                        <option value="rugby"><?= $t['sport_rugby'] ?></option>
                        <option value="basketball"><?= $t['sport_basketball'] ?></option>
                        <option value="tennis"><?= $t['sport_tennis'] ?></option>
                        <option value="athletisme"><?= $t['sport_athletism'] ?></option>
                        <option value="natation"><?= $t['sport_swimming'] ?></option>
                        <option value="cyclisme"><?= $t['sport_cycling'] ?></option>
                        <option value="esport"><?= $t['sport_esport'] ?></option>
                        <hr>
                        <option value="autre"><?= $t['sport_other'] ?></option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_desc'] ?></label>
                    <textarea class="form-control form-control-lg fs-6" id="description" name="description" rows="4" placeholder="<?= $t['form_desc_ph'] ?>"></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="date_debut" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_start_date'] ?></label>
                        <input type="date" class="form-control form-control-lg fs-6" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="col-md-6">
                        <label for="date_fin" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_end_date'] ?></label>
                        <!-- NOUVEAU : On ajoute disabled par défaut pour forcer à choisir la date de début d'abord -->
                        <input type="date" class="form-control form-control-lg fs-6" id="date_fin" name="date_fin" disabled>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label for="lieu" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_location'] ?></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="lieu" name="lieu" placeholder="<?= $t['form_location_ph'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="capacite" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_capacity'] ?></label>
                        <input type="number" class="form-control form-control-lg fs-6" id="capacite" name="capacite" placeholder="<?= $t['form_capacity_ph'] ?>" min="0" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <a href="/?page=dashboard" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-3"><?= $t['form_cancel'] ?></a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3" style="background-color: #1a56db; border-color: #1a56db;"><?= $t['form_save'] ?></button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- NOUVEAU : Script d'interactivité du formulaire -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Validation visuelle Bootstrap (Bordures rouges/vertes)
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault(); // Empêche l'envoi si des champs sont invalides
            event.stopPropagation();
        }
        form.classList.add('was-validated'); // Active les bordures Bootstrap
    }, false);

    // 2. Logique intelligente des dates
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');

    if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
            if (this.value) {
                // Déverrouille la date de fin
                dateFin.disabled = false;
                // La date de fin minimale devient la date de début
                dateFin.min = this.value;
                
                // Si une date de fin est déjà saisie et qu'elle est "avant" la nouvelle date de début, on l'efface
                if (dateFin.value && dateFin.value < this.value) {
                    dateFin.value = '';
                }
            } else {
                // Si on efface la date de début, on rebloque la date de fin
                dateFin.disabled = true;
                dateFin.value = '';
            }
        });
    }
});
</script>