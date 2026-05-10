<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Formulaire de création d'un nouvel événement.
 *
 * @file nouvel_event.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array $t Traductions chargées.
 */

declare(strict_types=1);

$errorMsg = \Core\Session::flash('error_msg');
?>
<section class="container mb-5" style="max-width: 800px;">

    <header class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h1 class="fs-4 fw-bold mb-0 text-body"><?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?></h1>
        <span class="text-body-secondary"><?= htmlspecialchars($t['nav_dashboard'], ENT_QUOTES) ?></span>
    </header>

    <article class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-5">

            <h2 class="mb-4 fw-bold fs-4 text-body"><?= htmlspecialchars($t['form_page_title'], ENT_QUOTES) ?></h2>

            <?php if ($errorMsg !== null): ?>
                <aside class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </aside>
            <?php endif; ?>

            <form action="/traitement_event.php" method="POST" class="needs-validation" novalidate>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="nom_event" class="form-label fw-semibold text-body event-label">
                        <?= htmlspecialchars($t['form_event_name'], ENT_QUOTES) ?>
                    </label>
                    <input type="text" class="form-control form-control-lg fs-6"
                           id="nom_event" name="nom_event"
                           placeholder="<?= htmlspecialchars($t['form_event_name_ph'], ENT_QUOTES) ?>"
                           required>
                </fieldset>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="type_sport" class="form-label fw-semibold text-body event-label">
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
                    <label for="description" class="form-label fw-semibold text-body event-label">
                        <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                    </label>
                    <textarea class="form-control form-control-lg fs-6"
                              id="description" name="description" rows="4"
                              placeholder="<?= htmlspecialchars($t['form_desc_ph'], ENT_QUOTES) ?>"></textarea>
                </fieldset>

                <div class="row mb-4">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_debut" class="form-label fw-semibold text-body event-label">
                            <?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="date" class="form-control form-control-lg fs-6"
                               id="date_debut" name="date_debut" required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_fin" class="form-label fw-semibold text-body event-label">
                            <?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="date" class="form-control form-control-lg fs-6"
                               id="date_fin" name="date_fin" disabled>
                    </fieldset>
                </div>

                <div class="row mb-5">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="lieu" class="form-label fw-semibold text-body event-label">
                            <?= htmlspecialchars($t['form_location'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" class="form-control form-control-lg fs-6"
                               id="lieu" name="lieu"
                               placeholder="<?= htmlspecialchars($t['form_location_ph'], ENT_QUOTES) ?>"
                               required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="capacite" class="form-label fw-semibold text-body event-label">
                            <?= htmlspecialchars($t['form_capacity'], ENT_QUOTES) ?>
                        </label>
                        <input type="number" class="form-control form-control-lg fs-6"
                               id="capacite" name="capacite"
                               placeholder="<?= htmlspecialchars($t['form_capacity_ph'], ENT_QUOTES) ?>"
                               min="0">
                    </fieldset>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <a href="/?page=dashboard" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-3">
                        <?= htmlspecialchars($t['form_cancel'], ENT_QUOTES) ?>
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                        <?= htmlspecialchars($t['form_save'], ENT_QUOTES) ?>
                    </button>
                </div>

            </form>
        </div>
    </article>
</section>
