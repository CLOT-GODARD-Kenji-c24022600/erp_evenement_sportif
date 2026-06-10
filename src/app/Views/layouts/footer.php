<?php

/**
 * YES - Your Event Solution
 *
 * Layout : Pied de page + modales Quick-Create.
 * Les modales sont placées ICI (après </main>) pour ne pas
 * perturber le flex layout de .main-content.
 *
 * @file footer.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.4
 * @since 2026
 */

declare(strict_types=1);
?>

    <footer class="py-4 bg-body border-top mt-auto app-footer">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center small">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($t['app_name'] ?? 'YES', ENT_QUOTES) ?>
                </p>
                <nav aria-label="Liens du pied de page">
                    <ul class="list-unstyled d-flex gap-3 align-items-center mb-0">
                        <li>
                            <a href="/aide" class="text-decoration-none text-reset">
                                <?= htmlspecialchars($t['footer_help'] ?? 'Aide', ENT_QUOTES) ?>
                            </a>
                        </li>
                        <li class="border-start ps-3">
                            <a href="/mentions_legales" class="text-decoration-none text-reset">
                                <?= htmlspecialchars($t['footer_privacy'] ?? 'Mentions légales', ENT_QUOTES) ?>
                            </a>
                        </li>
                        <li class="border-start ps-3">
                            <a href="/plan-du-site" class="text-decoration-none text-reset">
                                Plan du site
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </footer>

</main>

<!-- ═══════════════════════════════════════════════════════════════
     MODALES QUICK-CREATE — placées APRÈS </main> pour ne pas être
     enfants du flex container .main-content (évite footer au milieu)
════════════════════════════════════════════════════════════════ -->

<!-- Modale Quick-Create : Événement -->
<dialog class="modal fade" id="modalEvent" tabindex="-1" aria-labelledby="modalEventLabel" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="quick_create" value="event">
                <header class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEventLabel">
                        <?= htmlspecialchars($t['qc_event_title'], ENT_QUOTES) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
                </header>
                <div class="modal-body">
                    <p class="mb-3">
                        <label for="qc-event-nom" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['form_event_name'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="qc-event-nom" name="nom" class="form-control" required>
                    </p>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="qc-event-debut" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?>
                            </label>
                            <input type="date" id="qc-event-debut" name="date_debut" class="form-control" required>
                        </p>
                        <p class="col mb-0">
                            <label for="qc-event-fin" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?>
                            </label>
                            <input type="date" id="qc-event-fin" name="date_fin" class="form-control" required>
                        </p>
                    </section>
                    <p class="mb-3">
                        <label for="qc-event-lieu" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['form_location'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="qc-event-lieu" name="lieu" class="form-control" required>
                    </p>
                    <p class="mb-3">
                        <label for="qc-event-desc" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                        </label>
                        <textarea id="qc-event-desc" name="description" class="form-control" rows="2"></textarea>
                    </p>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <?= htmlspecialchars($t['qc_event_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- Modale Quick-Create : Projet -->
<dialog class="modal fade" id="modalProjet" tabindex="-1" aria-labelledby="modalProjetLabel" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="quick_create" value="projet">
                <header class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalProjetLabel">
                        <?= htmlspecialchars($t['qc_projet_title'], ENT_QUOTES) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
                </header>
                <div class="modal-body">
                    <p class="mb-3">
                        <label for="qc-projet-nom" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['form_event_name'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" id="qc-projet-nom" name="nom" class="form-control" required>
                    </p>
                    <p class="mb-3">
                        <label for="qc-projet-desc" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['form_desc'], ENT_QUOTES) ?>
                        </label>
                        <textarea id="qc-projet-desc" name="description" class="form-control" rows="3"></textarea>
                    </p>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold">
                        <?= htmlspecialchars($t['qc_projet_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<?php if ($isAdmin): ?>
<!-- Modale Quick-Create : Membre du staff (admin uniquement) -->
<dialog class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserLabel" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="quick_create" value="user">
                <header class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalUserLabel">
                        <?= htmlspecialchars($t['qc_user_title'], ENT_QUOTES) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
                </header>
                <div class="modal-body">
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="qc-user-prenom" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_prenom'], ENT_QUOTES) ?>
                            </label>
                            <input type="text" id="qc-user-prenom" name="prenom" class="form-control" required>
                        </p>
                        <p class="col mb-0">
                            <label for="qc-user-nom" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_nom'], ENT_QUOTES) ?>
                            </label>
                            <input type="text" id="qc-user-nom" name="nom" class="form-control" required>
                        </p>
                    </section>
                    <p class="mb-3">
                        <label for="qc-user-email" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['profile_field_email'], ENT_QUOTES) ?>
                        </label>
                        <input type="email" id="qc-user-email" name="email" class="form-control" required>
                    </p>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="qc-user-poste" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_poste'], ENT_QUOTES) ?>
                            </label>
                            <input type="text" id="qc-user-poste" name="poste" class="form-control">
                        </p>
                        <p class="col mb-0">
                            <label for="qc-user-role" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['users_th_role'], ENT_QUOTES) ?>
                            </label>
                            <select id="qc-user-role" name="role" class="form-select">
                                <option value="staff">Staff</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </p>
                    </section>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <?= htmlspecialchars($t['qc_user_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/layout.js"></script>
<script src="/assets/js/search.js"></script>
<script src="/assets/js/presence.js"></script>
<script src="/assets/js/routeur.js"></script>