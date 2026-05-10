<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Gestion (édition / suppression) d'un événement existant.
 *
 * @file gerer_event.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array $event Données de l'événement à éditer.
 * @var array $t     Traductions chargées.
 */

declare(strict_types=1);

$errorMsg   = \Core\Session::flash('error_msg');
$successMsg = \Core\Session::flash('success_msg');
?>
<section class="container-fluid py-4">

    <header class="d-flex justify-content-between align-items-center mb-4">
        <hgroup>
            <h1 class="fw-bold text-body mb-0">Gérer l'événement</h1>
            <p class="text-body-secondary mb-0">Modification de l'événement #<?= (int) $event['id'] ?></p>
        </hgroup>
        <a href="/?page=dashboard" class="btn btn-outline-secondary shadow-sm">
            <?= htmlspecialchars($t['btn_back'], ENT_QUOTES) ?>
        </a>
    </header>

    <?php if ($errorMsg !== null): ?>
        <aside class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
    <?php endif; ?>

    <?php if ($successMsg !== null): ?>
        <aside class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($successMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
    <?php endif; ?>

    <article class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <form action="/traitement_gerer_event.php" method="POST" novalidate>

                <input type="hidden" name="id"     value="<?= (int) $event['id'] ?>">
                <input type="hidden" name="action" id="form_action" value="update">

                <section class="row mb-3">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="nom_event" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_event_name'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="nom_event" name="nom_event" class="form-control"
                               value="<?= htmlspecialchars((string) $event['nom'], ENT_QUOTES) ?>" required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="type_sport" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_sport_type'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="type_sport" name="type_sport" class="form-control"
                               value="<?= htmlspecialchars((string) ($event['sport'] ?? ''), ENT_QUOTES) ?>">
                    </fieldset>
                </section>

                <section class="row mb-3">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="lieu" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_location'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="lieu" name="lieu" class="form-control"
                               value="<?= htmlspecialchars((string) ($event['lieu'] ?? ''), ENT_QUOTES) ?>">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="capacite" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_capacity'], ENT_QUOTES) ?>
                        </label>
                        <input type="number" id="capacite" name="capacite" class="form-control"
                               value="<?= htmlspecialchars((string) ($event['capacite'] ?? ''), ENT_QUOTES) ?>">
                    </fieldset>
                </section>

                <section class="row mb-3">
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_debut" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="datetime-local" id="date_debut" name="date_debut" class="form-control"
                               value="<?= date('Y-m-d\TH:i', strtotime((string) $event['date_debut'])) ?>"
                               required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_fin" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="datetime-local" id="date_fin" name="date_fin" class="form-control"
                               value="<?= !empty($event['date_fin']) ? date('Y-m-d\TH:i', strtotime((string) $event['date_fin'])) : '' ?>">
                    </fieldset>
                </section>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="description" class="form-label fw-semibold">
                        <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars((string) ($event['description'] ?? ''), ENT_QUOTES) ?></textarea>
                </fieldset>

                <footer class="d-flex justify-content-between">
                    <button type="submit"
                            onclick="document.getElementById('form_action').value='delete'; return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>');"
                            class="btn btn-danger">
                        <i class="bi bi-trash me-2" aria-hidden="true"></i>
                        <?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>
                    </button>
                    <button type="submit"
                            onclick="document.getElementById('form_action').value='update';"
                            class="btn btn-primary px-4">
                        <i class="bi bi-save me-2" aria-hidden="true"></i>
                        <?= htmlspecialchars($t['btn_save'], ENT_QUOTES) ?>
                    </button>
                </footer>

            </form>
        </div>
    </article>
</section>