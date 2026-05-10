<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Profil utilisateur.
 *
 * @file profil.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array  $user    Données de l'utilisateur connecté.
 * @var string $msg     Message de retour.
 * @var string $msgType Type du message ('success' ou 'danger').
 * @var array  $t       Traductions chargées.
 */

declare(strict_types=1);
?>
<section class="container-fluid">

    <header class="mb-4">
        <h1 class="fw-bold"><?= htmlspecialchars($t['profile_title'], ENT_QUOTES) ?></h1>
    </header>

    <?php if ($msg !== ''): ?>
        <aside class="alert alert-<?= htmlspecialchars($msgType, ENT_QUOTES) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
    <?php endif; ?>

    <div class="row">

        <aside class="col-xl-4 col-lg-5 mb-4">
            <article class="card shadow-sm border-0 text-center p-4">
                <section class="card-body">
                    <figure class="mb-3">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars((string) $user['avatar'], ENT_QUOTES) ?>"
                                 alt="Avatar de <?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . $user['nom']), ENT_QUOTES) ?>"
                                 class="rounded-circle shadow profile-avatar">
                        <?php else: ?>
                            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow profile-avatar-placeholder">
                                <?= strtoupper(substr((string) $user['nom'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                    </figure>

                    <h2 class="fw-bold mb-1 h4">
                        <?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . $user['nom']), ENT_QUOTES) ?>
                    </h2>
                    <p class="text-muted mb-4">
                        <?= htmlspecialchars((string) ($user['poste'] ?? 'Membre du staff'), ENT_QUOTES) ?>
                    </p>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <fieldset class="mb-3 border-0 p-0">
                            <label for="avatar-upload" class="form-label visually-hidden">
                                <?= htmlspecialchars($t['profile_btn_avatar'], ENT_QUOTES) ?>
                            </label>
                            <input class="form-control form-control-sm" type="file" id="avatar-upload"
                                   name="avatar" accept="image/*" required>
                        </fieldset>
                        <button type="submit" name="update_avatar" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                            <i class="bi bi-camera me-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($t['profile_btn_avatar'], ENT_QUOTES) ?>
                        </button>
                    </form>

                    <hr class="my-4">

                    <a href="?page=logout" class="btn btn-danger btn-sm w-100 fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                        <?= htmlspecialchars($t['profile_btn_logout'], ENT_QUOTES) ?>
                    </a>
                </section>
            </article>
        </aside>

        <section class="col-xl-8 col-lg-7">

            <article class="card shadow-sm border-0 mb-4">
                <header class="card-header bg-white py-3">
                    <h2 class="mb-0 fw-bold text-primary h5">
                        <?= htmlspecialchars($t['profile_info_title'], ENT_QUOTES) ?>
                    </h2>
                </header>
                <section class="card-body p-4">
                    <form action="" method="POST" novalidate>
                        <section class="row mb-3">
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="prenom" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_prenom'], ENT_QUOTES) ?>
                                </label>
                                <input type="text" id="prenom" name="prenom" class="form-control"
                                       value="<?= htmlspecialchars((string) ($user['prenom'] ?? ''), ENT_QUOTES) ?>"
                                       autocomplete="given-name">
                            </fieldset>
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="nom" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_nom'], ENT_QUOTES) ?>
                                </label>
                                <input type="text" id="nom" name="nom" class="form-control"
                                       value="<?= htmlspecialchars((string) $user['nom'], ENT_QUOTES) ?>"
                                       autocomplete="family-name" required>
                            </fieldset>
                        </section>
                        <fieldset class="mb-3 border-0 p-0">
                            <label for="email" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_email'], ENT_QUOTES) ?>
                            </label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars((string) $user['email'], ENT_QUOTES) ?>"
                                   autocomplete="email" required>
                        </fieldset>
                        <section class="row mb-3">
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="poste" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_poste'], ENT_QUOTES) ?>
                                </label>
                                <input type="text" id="poste" name="poste" class="form-control"
                                       value="<?= htmlspecialchars((string) ($user['poste'] ?? ''), ENT_QUOTES) ?>">
                            </fieldset>
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="telephone" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_phone'], ENT_QUOTES) ?>
                                </label>
                                <input type="tel" id="telephone" name="telephone" class="form-control"
                                       value="<?= htmlspecialchars((string) ($user['telephone'] ?? ''), ENT_QUOTES) ?>"
                                       autocomplete="tel">
                            </fieldset>
                        </section>
                        <fieldset class="mb-3 border-0 p-0">
                            <label for="statut_presence" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_status'], ENT_QUOTES) ?>
                            </label>
                            <select id="statut_presence" name="statut_presence" class="form-select bg-light">
                                <option value="online"  <?= (($user['statut_presence'] ?? 'online') === 'online')  ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['profile_status_online'], ENT_QUOTES) ?>
                                </option>
                                <option value="dnd"     <?= (($user['statut_presence'] ?? '') === 'dnd')    ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['profile_status_dnd'], ENT_QUOTES) ?>
                                </option>
                                <option value="idle"    <?= (($user['statut_presence'] ?? '') === 'idle')   ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['profile_status_idle'], ENT_QUOTES) ?>
                                </option>
                                <option value="offline" <?= (($user['statut_presence'] ?? '') === 'offline') ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['profile_status_offline'], ENT_QUOTES) ?>
                                </option>
                            </select>
                        </fieldset>
                        <hr class="my-4">
                        <button type="submit" name="update_info" class="btn btn-primary px-4 fw-bold">
                            <?= htmlspecialchars($t['profile_btn_save'], ENT_QUOTES) ?>
                        </button>
                    </form>
                </section>
            </article>

            <article class="card shadow-sm border-0">
                <header class="card-header bg-white py-3">
                    <h2 class="mb-0 fw-bold h5 text-danger">
                        <i class="bi bi-shield-lock me-2" aria-hidden="true"></i>
                        <?= htmlspecialchars($t['profile_security_title'], ENT_QUOTES) ?>
                    </h2>
                </header>
                <section class="card-body p-4">
                    <form action="" method="POST" novalidate>
                        <fieldset class="mb-3 border-0 p-0">
                            <label for="old_password" class="form-label small fw-bold">
                                <?= htmlspecialchars($t['profile_field_old_pwd'], ENT_QUOTES) ?>
                            </label>
                            <input type="password" id="old_password" name="old_password" class="form-control"
                                   autocomplete="current-password" required>
                        </fieldset>
                        <section class="row mb-3">
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="new_password" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_new_pwd'], ENT_QUOTES) ?>
                                </label>
                                <input type="password" id="new_password" name="new_password" class="form-control"
                                       autocomplete="new-password" required>
                            </fieldset>
                            <fieldset class="col-md-6 border-0 p-0 px-2">
                                <label for="confirm_password" class="form-label small fw-bold">
                                    <?= htmlspecialchars($t['profile_field_confirm'], ENT_QUOTES) ?>
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                       autocomplete="new-password" required>
                            </fieldset>
                        </section>
                        <button type="submit" name="update_password" class="btn btn-outline-danger px-4">
                            <?= htmlspecialchars($t['profile_btn_pwd'], ENT_QUOTES) ?>
                        </button>
                    </form>
                </section>
            </article>

        </section>
    </div>
</section>