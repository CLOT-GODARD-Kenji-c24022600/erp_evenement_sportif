<?php

/**
 * YES - Your Event Solution
 * @file gerer_event.php
 * @version 1.2  –  2026
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
        <a href="/dashboard" class="btn btn-outline-secondary shadow-sm">
            <?= htmlspecialchars($t['btn_back'], ENT_QUOTES) ?>
        </a>
    </header>

    <?php if ($errorMsg !== null): ?>
        <aside class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <?php if ($successMsg !== null): ?>
        <aside class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($successMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <article class="card shadow-sm border-0 rounded-3" style="max-width:900px;">
        <div class="card-body p-4">
            <form action="/traitement_gerer_event.php" method="POST" novalidate>

                <input type="hidden" name="id"     value="<?= (int) $event['id'] ?>">
                <input type="hidden" name="action" id="form_action" value="update">

                <!-- ── Infos générales ───────────────────────── -->
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
                        <input type="date" id="date_debut" name="date_debut" class="form-control"
                               value="<?= !empty($event['date_debut']) ? date('Y-m-d', strtotime((string)$event['date_debut'])) : '' ?>"
                               required>
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label for="date_fin" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?>
                        </label>
                        <input type="date" id="date_fin" name="date_fin" class="form-control"
                               value="<?= !empty($event['date_fin']) ? date('Y-m-d', strtotime((string)$event['date_fin'])) : '' ?>">
                    </fieldset>
                </section>

                <fieldset class="mb-4 border-0 p-0">
                    <label for="description" class="form-label fw-semibold">
                        <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars((string) ($event['description'] ?? ''), ENT_QUOTES) ?></textarea>
                </fieldset>

                <!-- ── Phases de production ────────────────────── -->
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>Phases de production
                </h5>

                <?php
                $phases = [
                    ['key' => 'preprod',   'label' => 'Pré-production',       'color' => 'warning', 'icon' => 'bi-hammer'],
                    ['key' => 'prod',      'label' => 'Production/Installation','color' => 'primary', 'icon' => 'bi-gear-fill'],
                    ['key' => 'exploit',   'label' => 'Exploitation/Événement','color' => 'success', 'icon' => 'bi-play-circle-fill'],
                    ['key' => 'demontage', 'label' => 'Démontage',             'color' => 'danger',  'icon' => 'bi-trash3-fill'],
                ];
                foreach ($phases as $ph):
                    $dKey = 'date_' . $ph['key'] . '_debut';
                    $fKey = 'date_' . $ph['key'] . '_fin';
                ?>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <span class="fw-semibold text-<?= $ph['color'] ?>">
                            <i class="bi <?= $ph['icon'] ?> me-1"></i><?= $ph['label'] ?>
                        </span>
                    </div>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label class="form-label small">Date début</label>
                        <input type="date" class="form-control" name="<?= $dKey ?>"
                               value="<?= !empty($event[$dKey]) ? date('Y-m-d', strtotime((string)$event[$dKey])) : '' ?>">
                    </fieldset>
                    <fieldset class="col-md-6 border-0 p-0 px-2">
                        <label class="form-label small">Date fin</label>
                        <input type="date" class="form-control" name="<?= $fKey ?>"
                               value="<?= !empty($event[$fKey]) ? date('Y-m-d', strtotime((string)$event[$fKey])) : '' ?>">
                    </fieldset>
                </div>
                <?php endforeach; ?>

                <!-- ── Liens Google Drive / Maps ──────────────── -->
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-google me-2 text-danger"></i>Liens Google
                </h5>
                <div class="row g-3 mb-4">
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-cloud me-1 text-primary"></i>Google Drive (dossier)
                        </label>
                        <input type="url" class="form-control" name="drive_url"
                               value="<?= htmlspecialchars((string)($event['drive_url'] ?? ''), ENT_QUOTES) ?>"
                               placeholder="https://drive.google.com/drive/folders/...">
                    </fieldset>
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-text me-1 text-success"></i>Google Doc / Sheet
                        </label>
                        <input type="url" class="form-control" name="drive_doc_url"
                               value="<?= htmlspecialchars((string)($event['drive_doc_url'] ?? ''), ENT_QUOTES) ?>"
                               placeholder="https://docs.google.com/...">
                    </fieldset>
                    <fieldset class="col-12 border-0 p-0 px-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>Google My Maps
                        </label>
                        <input type="url" class="form-control" name="maps_url"
                               value="<?= htmlspecialchars((string)($event['maps_url'] ?? ''), ENT_QUOTES) ?>"
                               placeholder="https://www.google.com/maps/...">
                    </fieldset>
                </div>

                <footer class="d-flex justify-content-between pt-3 border-top">
                    <button type="submit"
                            onclick="document.getElementById('form_action').value='delete'; return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>');"
                            class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        <?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>
                    </button>
                    <button type="submit"
                            onclick="document.getElementById('form_action').value='update';"
                            class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>
                        <?= htmlspecialchars($t['btn_save'], ENT_QUOTES) ?>
                    </button>
                </footer>

            </form>
        </div>
    </article>
</section>