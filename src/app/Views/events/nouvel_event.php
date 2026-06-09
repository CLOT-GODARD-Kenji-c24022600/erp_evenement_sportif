<?php

/**
 * YES - Your Event Solution
 * @file nouvel_event.php
 * @version 1.3  –  2026
 */

declare(strict_types=1);

$errorMsg = \Core\Session::flash('error_msg');
?>
<div class="container-fluid py-4">
<div class="row g-4">
<div class="col-12 col-xl-7">

    <header class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h1 class="fs-4 fw-bold mb-0 text-body"><?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?></h1>
        <span class="text-body-secondary"><?= htmlspecialchars($t['nav_dashboard'], ENT_QUOTES) ?></span>
    </header>

    <article class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-5">

            <h2 class="mb-4 fw-bold fs-4 text-body"><?= htmlspecialchars($t['form_page_title'], ENT_QUOTES) ?></h2>

            <?php if ($errorMsg !== null): ?>
                <aside class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </aside>
            <?php endif; ?>

            <form action="/nouvel_event" method="POST" class="needs-validation" novalidate>

                <!-- ── Informations générales ─────────────────── -->
                <fieldset class="mb-4 border-0 p-0">
                    <label for="nom_event" class="form-label fw-semibold text-body">
                        <?= htmlspecialchars($t['form_event_name'], ENT_QUOTES) ?>
                    </label>
                    <input type="text" class="form-control form-control-lg fs-6"
                           id="nom_event" name="nom_event"
                           placeholder="<?= htmlspecialchars($t['form_event_name_ph'], ENT_QUOTES) ?>"
                           required>
                </fieldset>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="type_sport" class="form-label fw-semibold text-body">
                        <?= htmlspecialchars($t['form_sport_type'], ENT_QUOTES) ?>
                    </label>
                    <select class="form-select form-select-lg fs-6" id="type_sport" name="type_sport" required>
                        <option value="" selected disabled><?= htmlspecialchars($t['form_sport_select'], ENT_QUOTES) ?></option>
                        <option value="football"><?= htmlspecialchars($t['sport_football'], ENT_QUOTES) ?></option>
                        <option value="rugby"><?= htmlspecialchars($t['sport_rugby'], ENT_QUOTES) ?></option>
                        <option value="basketball"><?= htmlspecialchars($t['sport_basketball'], ENT_QUOTES) ?></option>
                        <option value="tennis"><?= htmlspecialchars($t['sport_tennis'], ENT_QUOTES) ?></option>
                        <option value="athletisme"><?= htmlspecialchars($t['sport_athletism'], ENT_QUOTES) ?></option>
                        <option value="natation"><?= htmlspecialchars($t['sport_swimming'], ENT_QUOTES) ?></option>
                        <option value="cyclisme"><?= htmlspecialchars($t['sport_cycling'], ENT_QUOTES) ?></option>
                        <option value="esport"><?= htmlspecialchars($t['sport_esport'], ENT_QUOTES) ?></option>
                        <option value="autre"><?= htmlspecialchars($t['sport_other'], ENT_QUOTES) ?></option>
                    </select>
                </fieldset>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="description" class="form-label fw-semibold text-body">
                        <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                    </label>
                    <textarea class="form-control form-control-lg fs-6"
                              id="description" name="description" rows="4"
                              placeholder="<?= htmlspecialchars($t['form_desc_ph'], ENT_QUOTES) ?>"></textarea>
                </fieldset>

                <div class="row mb-4">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_debut" class="form-label fw-semibold text-body">
                            <?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="date" class="form-control form-control-lg fs-6"
                               id="date_debut" name="date_debut" required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_fin" class="form-label fw-semibold text-body">
                            <?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="date" class="form-control form-control-lg fs-6"
                               id="date_fin" name="date_fin">
                    </fieldset>
                </div>

                <div class="row mb-4">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="lieu" class="form-label fw-semibold text-body">
                            <?= htmlspecialchars($t['form_location'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" class="form-control form-control-lg fs-6"
                               id="lieu" name="lieu"
                               placeholder="<?= htmlspecialchars($t['form_location_ph'], ENT_QUOTES) ?>"
                               required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="capacite" class="form-label fw-semibold text-body">
                            <?= htmlspecialchars($t['form_capacity'], ENT_QUOTES) ?>
                        </label>
                        <input type="number" class="form-control form-control-lg fs-6"
                               id="capacite" name="capacite"
                               placeholder="<?= htmlspecialchars($t['form_capacity_ph'], ENT_QUOTES) ?>"
                               min="0">
                    </fieldset>
                </div>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="projet_id" class="form-label fw-semibold text-body">
                        <?= htmlspecialchars($t['form_project'], ENT_QUOTES) ?>
                    </label>
                    <select class="form-select form-select-lg fs-6" id="projet_id" name="projet_id">
                        <option value=""><?= htmlspecialchars($t['label_none'], ENT_QUOTES) ?></option>
                        <?php foreach ($projets as $pr): ?>
                        <option value="<?= (int) $pr['id'] ?>">
                            <?= htmlspecialchars((string) $pr['nom'], ENT_QUOTES) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>

                <!-- ── Phases de production ────────────────────── -->
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>Phases de production
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-warning">
                            <i class="bi bi-hammer me-1"></i>Pré-production
                        </label>
                    </div>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_preprod_debut" class="form-label small">Date début</label>
                        <input type="date" class="form-control" id="date_preprod_debut" name="date_preprod_debut">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_preprod_fin" class="form-label small">Date fin</label>
                        <input type="date" class="form-control" id="date_preprod_fin" name="date_preprod_fin">
                    </fieldset>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary">
                            <i class="bi bi-gear-fill me-1"></i>Production / Installation
                        </label>
                    </div>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_prod_debut" class="form-label small">Date début</label>
                        <input type="date" class="form-control" id="date_prod_debut" name="date_prod_debut">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_prod_fin" class="form-label small">Date fin</label>
                        <input type="date" class="form-control" id="date_prod_fin" name="date_prod_fin">
                    </fieldset>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-success">
                            <i class="bi bi-play-circle-fill me-1"></i>Exploitation / Événement
                        </label>
                    </div>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_exploit_debut" class="form-label small">Date début</label>
                        <input type="date" class="form-control" id="date_exploit_debut" name="date_exploit_debut">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_exploit_fin" class="form-label small">Date fin</label>
                        <input type="date" class="form-control" id="date_exploit_fin" name="date_exploit_fin">
                    </fieldset>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-danger">
                            <i class="bi bi-trash3-fill me-1"></i>Démontage
                        </label>
                    </div>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_demontage_debut" class="form-label small">Date début</label>
                        <input type="date" class="form-control" id="date_demontage_debut" name="date_demontage_debut">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_demontage_fin" class="form-label small">Date fin</label>
                        <input type="date" class="form-control" id="date_demontage_fin" name="date_demontage_fin">
                    </fieldset>
                </div>

                <!-- ── Liens Google Drive / Maps ──────────────── -->
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-google me-2 text-danger"></i>Liens Google
                </h5>
                <div class="row g-3 mb-5">
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label for="drive_url" class="form-label fw-semibold">
                            <i class="bi bi-cloud me-1 text-primary"></i>Google Drive (dossier)
                        </label>
                        <input type="url" class="form-control" id="drive_url" name="drive_url"
                               placeholder="https://drive.google.com/drive/folders/...">
                    </fieldset>
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label for="drive_doc_url" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-text me-1 text-success"></i>Google Doc / Sheet
                        </label>
                        <input type="url" class="form-control" id="drive_doc_url" name="drive_doc_url"
                               placeholder="https://docs.google.com/...">
                    </fieldset>
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label for="maps_url" class="form-label fw-semibold">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>Google My Maps
                        </label>
                        <input type="url" class="form-control" id="maps_url" name="maps_url"
                               placeholder="https://www.google.com/maps/...">
                    </fieldset>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <a href="/dashboard" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-3">
                        <?= htmlspecialchars($t['form_cancel'], ENT_QUOTES) ?>
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                        <?= htmlspecialchars($t['form_save'], ENT_QUOTES) ?>
                    </button>
                </div>

            </form>
        </div>
    </article>
</div>

<!-- Colonne droite : aide contextuelle -->
<div class="col-12 col-xl-5 d-none d-xl-block">
    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top:80px;">
        <div class="card-header bg-primary text-white rounded-top-3 border-0">
            <h2 class="fw-bold fs-6 mb-0"><i class="bi bi-lightbulb-fill me-2"></i>Guide de création</h2>
        </div>
        <div class="card-body p-4">
            <ul class="list-unstyled mb-0">
                <li class="mb-3 d-flex gap-3">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;">1</span>
                    <div><p class="fw-semibold mb-1 small">Informations générales</p><p class="text-body-secondary small mb-0">Nom, sport, lieu et capacité de votre événement.</p></div>
                </li>
                <li class="mb-3 d-flex gap-3">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;">2</span>
                    <div><p class="fw-semibold mb-1 small">Phases de production</p><p class="text-body-secondary small mb-0">Définissez les dates de pré-production, installation, exploitation et démontage.</p></div>
                </li>
                <li class="mb-3 d-flex gap-3">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;">3</span>
                    <div><p class="fw-semibold mb-1 small">Liens Google</p><p class="text-body-secondary small mb-0">Liez un dossier Drive, un document Google ou une carte My Maps à votre événement.</p></div>
                </li>
                <li class="d-flex gap-3">
                    <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;"><i class="bi bi-check"></i></span>
                    <div><p class="fw-semibold mb-1 small">Accès à l'opérationnel</p><p class="text-body-secondary small mb-0">Après création, gérez le budget, planning, matériel et facturation depuis la page Opérationnel.</p></div>
                </li>
            </ul>
        </div>
    </div>
</div>
</div>
</div>