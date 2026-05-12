<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Liste du staff (équipe).
 *
 * @file staff.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 *
 * Variables attendues :
 * @var array[] $staffMembers Liste des membres approuvés.
 * @var array   $t            Traductions chargées.
 */

declare(strict_types=1);
?>
<section class="container-fluid">

    <header class="d-flex justify-content-between align-items-end mb-4">
        <hgroup>
            <h1 class="fw-bold mb-1"><?= htmlspecialchars($t['staff_title'], ENT_QUOTES) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($t['staff_subtitle'], ENT_QUOTES) ?></p>
        </hgroup>
        <search>
            <label for="searchInput" class="visually-hidden">
                <?= htmlspecialchars($t['staff_search_ph'], ENT_QUOTES) ?>
            </label>
            <div class="input-group shadow-sm staff-search-wrapper">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted" aria-hidden="true"></i>
                </span>
                <input type="search" id="searchInput" class="form-control border-start-0 ps-0"
                       placeholder="<?= htmlspecialchars($t['staff_search_ph'], ENT_QUOTES) ?>"
                       aria-label="<?= htmlspecialchars($t['staff_search_ph'], ENT_QUOTES) ?>">
            </div>
        </search>
    </header>

    <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled" id="staffGrid">
        <?php foreach ($staffMembers as $member):
            $fullName = trim(($member['prenom'] ?? '') . ' ' . $member['nom']);
            if (empty($fullName)) $fullName = 'Utilisateur inconnu';

            $roleBadgeClass = $member['role'] === 'admin' ? 'bg-danger' : 'bg-primary';
            $roleName       = strtoupper((string) $member['role']);

            $statusPref   = $member['statut_presence'] ?? 'online';
            $lastActivity = strtotime($member['derniere_activite'] ?? '2000-01-01');
            $diff         = time() - $lastActivity;

            if ($statusPref === 'offline' || $diff > 900) {
                $dotColor = 'bg-secondary';
                $dotTitle = $t['staff_status_offline'];
            } elseif ($statusPref === 'idle' || $diff > 300) {
                $dotColor = 'bg-warning';
                $dotTitle = $t['staff_status_idle'];
            } elseif ($statusPref === 'dnd') {
                $dotColor = 'bg-danger';
                $dotTitle = $t['staff_status_dnd'];
            } else {
                $dotColor = 'bg-success';
                $dotTitle = $t['staff_status_online'];
            }
        ?>
        <li class="col staff-card" data-user-id="<?= (int) $member['id'] ?>">
            <article class="card h-100 border-0 shadow-sm staff-card-hover">
                <section class="card-body text-center p-4">

                    <figure class="mb-3 position-relative d-inline-block">
                        <?php if (!empty($member['avatar'])): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars((string) $member['avatar'], ENT_QUOTES) ?>"
                                 alt="Avatar de <?= htmlspecialchars($fullName, ENT_QUOTES) ?>"
                                 class="rounded-circle shadow-sm staff-avatar">
                        <?php else: ?>
                            <span class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm staff-avatar">
                                <?= strtoupper(substr($fullName, 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <span class="position-absolute bottom-0 end-0 p-2 <?= $dotColor ?> border border-light rounded-circle status-dot presence-dot"
                              title="<?= htmlspecialchars($dotTitle, ENT_QUOTES) ?>"
                              data-bs-toggle="tooltip"
                              aria-label="Statut : <?= htmlspecialchars($dotTitle, ENT_QUOTES) ?>"></span>
                    </figure>

                    <h2 class="fw-bold mb-1 h5 staff-name"><?= htmlspecialchars($fullName, ENT_QUOTES) ?></h2>
                    <p class="text-muted small mb-2 staff-poste">
                        <?= htmlspecialchars((string) ($member['poste'] ?? 'Membre du staff'), ENT_QUOTES) ?>
                    </p>
                    <span class="badge <?= $roleBadgeClass ?> mb-3 rounded-pill px-3"><?= $roleName ?></span>

                    <nav class="d-flex justify-content-center gap-2 mt-2" aria-label="Contacter <?= htmlspecialchars($fullName, ENT_QUOTES) ?>">
                        <a href="mailto:<?= htmlspecialchars((string) $member['email'], ENT_QUOTES) ?>"
                           class="btn btn-light btn-sm px-3 shadow-sm">
                            <i class="bi bi-envelope-fill text-primary" aria-hidden="true"></i>
                            <?= htmlspecialchars($t['staff_btn_email'], ENT_QUOTES) ?>
                        </a>
                        <?php if (!empty($member['telephone'])): ?>
                            <a href="tel:<?= htmlspecialchars((string) $member['telephone'], ENT_QUOTES) ?>"
                               class="btn btn-light btn-sm px-3 shadow-sm">
                                <i class="bi bi-telephone-fill text-success" aria-hidden="true"></i>
                                <?= htmlspecialchars($t['staff_btn_call'], ENT_QUOTES) ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-light btn-sm px-3 shadow-sm" disabled>
                                <i class="bi bi-telephone-x text-muted" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>
                    </nav>

                </section>
            </article>
        </li>
        <?php endforeach; ?>
    </ul>

    <p id="noResultMsg" class="text-center py-5 d-none" aria-live="polite">
        <i class="bi bi-emoji-frown fs-1 text-muted d-block mb-3" aria-hidden="true"></i>
        <?= htmlspecialchars($t['staff_no_result'], ENT_QUOTES) ?>
    </p>

</section>