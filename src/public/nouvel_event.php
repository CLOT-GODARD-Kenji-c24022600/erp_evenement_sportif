<?php include '../app/Views/includes/header.php'; ?>

<div class="container mb-5" style="max-width: 800px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h2 class="fs-4 fw-bold mb-0 text-body"><?= $t['app_name'] ?></h2>
        <span class="text-body-secondary"><?= $t['nav_dashboard'] ?></span>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-5">
            
            <h3 class="mb-5 fw-bold fs-4 text-body"><?= $t['form_page_title'] ?></h3>
            
            <form action="traitement_event.php" method="POST">
                
                <div class="mb-4">
                    <label for="nom_event" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_event_name'] ?></label>
                    <input type="text" class="form-control form-control-lg fs-6" id="nom_event" name="nom_event" placeholder="<?= $t['form_event_name_ph'] ?>" required>
                </div>

                <div class="mb-4">
                    <label for="type_sport" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_sport_type'] ?></label>
                    <select class="form-select form-select-lg fs-6" id="type_sport" name="type_sport" required>
                        <option value="" selected disabled><?= $t['form_sport_select'] ?></option>
                        <option value="football">Football</option>
                        <option value="rugby">Rugby</option>
                        <option value="athletisme"><?= $t['sport_athletism'] ?></option>
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
                        <input type="date" class="form-control form-control-lg fs-6" id="date_fin" name="date_fin">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label for="lieu" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_location'] ?></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="lieu" name="lieu" placeholder="<?= $t['form_location_ph'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="capacite" class="form-label fw-semibold text-body" style="font-size: 0.9rem;"><?= $t['form_capacity'] ?></label>
                        <input type="number" class="form-control form-control-lg fs-6" id="capacite" name="capacite" placeholder="<?= $t['form_capacity_ph'] ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <a href="/" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-3"><?= $t['form_cancel'] ?></a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3" style="background-color: #1a56db; border-color: #1a56db;"><?= $t['form_save'] ?></button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../app/Views/includes/footer.php'; ?>