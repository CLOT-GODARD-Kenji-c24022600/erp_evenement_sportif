<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Gestion des utilisateurs (admin uniquement).
 *
 * @file utilisateurs.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array[] $users Liste complète des utilisateurs.
 * @var array   $t     Traductions chargées.
 */

declare(strict_types=1);
?>
<section class="container py-4">

    <header class="mb-4 d-flex justify-content-between align-items-center">
        <hgroup>
            <h1 class="text-primary h3 mb-0">👥 <?= htmlspecialchars($t['users_title'], ENT_QUOTES) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($t['users_subtitle'], ENT_QUOTES) ?></p>
        </hgroup>
    </header>

    <article class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col"><?= htmlspecialchars($t['users_th_name'], ENT_QUOTES) ?></th>
                        <th scope="col"><?= htmlspecialchars($t['users_th_email'], ENT_QUOTES) ?></th>
                        <th scope="col"><?= htmlspecialchars($t['users_th_role'], ENT_QUOTES) ?></th>
                        <th scope="col"><?= htmlspecialchars($t['users_th_status'], ENT_QUOTES) ?></th>
                        <th scope="col" class="text-end"><?= htmlspecialchars($t['users_th_actions'], ENT_QUOTES) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-semibold">
                            <?= htmlspecialchars((string) $u['prenom'] . ' ' . $u['nom'], ENT_QUOTES) ?>
                        </td>
                        <td><?= htmlspecialchars((string) $u['email'], ENT_QUOTES) ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger">ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">STAFF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['statut'] === 'en_attente'): ?>
                                <span class="badge bg-warning text-dark">
                                    <?= htmlspecialchars($t['users_status_pending'], ENT_QUOTES) ?>
                                </span>
                            <?php elseif ($u['statut'] === 'approuve'): ?>
                                <span class="badge bg-success">
                                    <?= htmlspecialchars($t['users_status_approved'], ENT_QUOTES) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <?= htmlspecialchars($t['users_status_rejected'], ENT_QUOTES) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                <?php if ($u['statut'] !== 'approuve'): ?>
                                    <button type="submit" name="action" value="approuver"
                                            class="btn btn-sm btn-success"
                                            title="<?= htmlspecialchars($t['users_btn_approve'], ENT_QUOTES) ?>">✅</button>
                                <?php endif; ?>
                                <?php if ($u['statut'] !== 'rejete'): ?>
                                    <button type="submit" name="action" value="rejeter"
                                            class="btn btn-sm btn-warning"
                                            title="<?= htmlspecialchars($t['users_btn_reject'], ENT_QUOTES) ?>">❌</button>
                                <?php endif; ?>
                                <?php if ($u['role'] === 'staff' && $u['statut'] === 'approuve'): ?>
                                    <button type="submit" name="action" value="promouvoir_admin"
                                            class="btn btn-sm btn-outline-danger">
                                        ⬆️ <?= htmlspecialchars($t['users_btn_promote'], ENT_QUOTES) ?>
                                    </button>
                                <?php elseif ($u['role'] === 'admin'): ?>
                                    <button type="submit" name="action" value="retrograder_staff"
                                            class="btn btn-sm btn-outline-secondary">
                                        ⬇️ <?= htmlspecialchars($t['users_btn_demote'], ENT_QUOTES) ?>
                                    </button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="supprimer"
                                        class="btn btn-sm btn-danger ms-2"
                                        onclick="return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>');">
                                    🗑️
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

</section>