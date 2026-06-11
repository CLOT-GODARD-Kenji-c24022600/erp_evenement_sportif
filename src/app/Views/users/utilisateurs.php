<?php
/**
 * YES - Your Event Solution
 * Vue : Gestion des utilisateurs (admin uniquement).
 */
declare(strict_types=1);

use App\Models\UserModel;

// Couleurs CSS par rôle
$roleColor = [
    'super_admin'  => ['bg' => '#c0392b', 'text' => '#fff'],
    'admin'        => ['bg' => '#6c3483', 'text' => '#fff'],
    'developpeur'  => ['bg' => '#1a5276', 'text' => '#fff'],
    'chef_projet'  => ['bg' => '#1f618d', 'text' => '#fff'],
    'regisseur'    => ['bg' => '#0e6655', 'text' => '#fff'],
    'commercial'   => ['bg' => '#1e8449', 'text' => '#fff'],
    'staff'        => ['bg' => '#5d6d7e', 'text' => '#fff'],
    'benevole'     => ['bg' => '#d5d8dc', 'text' => '#333'],
];
$roleIcon = [
    'super_admin'  => '👑',
    'admin'        => '🛡️',
    'developpeur'  => '💻',
    'chef_projet'  => '📋',
    'regisseur'    => '🎛️',
    'commercial'   => '💼',
    'staff'        => '👤',
    'benevole'     => '🤝',
];

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
$currentRole   = (string) ($_SESSION['user_role'] ?? '');
$isSuperAdmin  = $currentRole === 'super_admin';
$isPrivileged  = UserModel::isPrivileged($currentRole);

// Helper inline pour générer un badge de rôle
$roleBadgeHtml = function(string $role) use ($roleColor, $roleIcon): string {
    $c    = $roleColor[$role]  ?? ['bg' => '#aaa', 'text' => '#fff'];
    $icon = $roleIcon[$role]   ?? '👤';
    $label = htmlspecialchars(UserModel::roleLabel($role), ENT_QUOTES);
    return sprintf(
        '<span class="badge rounded-pill" style="background:%s;color:%s;font-size:.78rem;padding:.35em .7em">%s %s</span>',
        $c['bg'], $c['text'], $icon, $label
    );
};
?>
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
                        <th><?= htmlspecialchars($t['users_th_name'], ENT_QUOTES) ?></th>
                        <th><?= htmlspecialchars($t['users_th_email'], ENT_QUOTES) ?></th>
                        <th><?= htmlspecialchars($t['users_th_role'], ENT_QUOTES) ?></th>
                        <th><?= htmlspecialchars($t['users_th_status'], ENT_QUOTES) ?></th>
                        <th class="text-end"><?= htmlspecialchars($t['users_th_actions'], ENT_QUOTES) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $uRole     = (string) ($u['role'] ?? 'staff');
                        $uId       = (int) $u['id'];
                        $isSelf    = $uId === $currentUserId;
                        $isTarget  = $uRole === 'super_admin';
                        $canAct    = !$isSelf && $isPrivileged && ($isSuperAdmin || !$isTarget);
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <?= htmlspecialchars((string)($u['prenom'] . ' ' . $u['nom']), ENT_QUOTES) ?>
                            <?php if ($isSelf): ?>
                                <span class="badge bg-light text-dark border ms-1">Vous</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars((string)$u['email'], ENT_QUOTES) ?></td>
                        <td><?= $roleBadgeHtml($uRole) ?></td>
                        <td>
                            <?php if ($u['statut'] === 'en_attente'): ?>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($t['users_status_pending'], ENT_QUOTES) ?></span>
                            <?php elseif ($u['statut'] === 'approuve'): ?>
                                <span class="badge bg-success"><?= htmlspecialchars($t['users_status_approved'], ENT_QUOTES) ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($t['users_status_rejected'], ENT_QUOTES) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($canAct): ?>
                            <form method="POST" class="d-inline-flex gap-1 align-items-center flex-wrap justify-content-end">
                                <input type="hidden" name="user_id" value="<?= $uId ?>">

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

                                <!-- Changement de rôle via dropdown -->
                                <div class="input-group input-group-sm" style="width:auto">
                                    <select name="new_role" class="form-select form-select-sm"
                                            style="min-width:150px;font-size:.8rem">
                                        <?php foreach (UserModel::ROLES as $rKey => $rLabel):
                                            // Cacher super_admin si pas super_admin
                                            if ($rKey === 'super_admin' && !$isSuperAdmin) continue;
                                        ?>
                                            <option value="<?= htmlspecialchars($rKey, ENT_QUOTES) ?>"
                                                <?= $uRole === $rKey ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($roleIcon[$rKey] ?? '') ?> <?= htmlspecialchars($rLabel, ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="action" value="changer_role"
                                            class="btn btn-sm btn-outline-primary">
                                        Appliquer
                                    </button>
                                </div>

                                <button type="submit" name="action" value="supprimer"
                                        class="btn btn-sm btn-danger ms-1"
                                        onclick="return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>');">
                                    🗑️
                                </button>
                            </form>
                            <?php elseif ($isSelf): ?>
                                <span class="text-muted small fst-italic">Votre compte</span>
                            <?php else: ?>
                                <span class="text-muted small fst-italic">Protégé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- Légende des rôles -->
    <div class="mt-4 card border-0 shadow-sm p-3">
        <h6 class="fw-semibold mb-3">📖 Rôles disponibles</h6>
        <div class="row g-2">
        <?php
        $descriptions = [
            'super_admin'  => 'Accès total · Gestion des rôles de tous les utilisateurs',
            'admin'        => 'Gestion complète des événements, projets et utilisateurs',
            'developpeur'  => 'Mêmes droits qu\'Admin + marquage développeur',
            'chef_projet'  => 'Budget, planning, facturation et équipe sur ses événements',
            'regisseur'    => 'Matériel, planning et opérationnel · Pas le budget',
            'commercial'   => 'Facturation, contacts et lecture du budget',
            'staff'        => 'Lecture générale + accès à son planning personnel',
            'benevole'     => 'Planning uniquement, accès lecture seul',
        ];
        foreach (UserModel::ROLES as $rKey => $rLabel):
            if ($rKey === 'super_admin' && !$isSuperAdmin) continue;
            $c    = $roleColor[$rKey] ?? ['bg' => '#aaa', 'text' => '#fff'];
            $icon = $roleIcon[$rKey]  ?? '👤';
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="d-flex align-items-start gap-2">
                <span class="badge rounded-pill flex-shrink-0 mt-1"
                      style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>;font-size:.8rem;padding:.35em .65em">
                    <?= $icon ?>
                </span>
                <div>
                    <div class="fw-semibold small"><?= htmlspecialchars($rLabel, ENT_QUOTES) ?></div>
                    <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($descriptions[$rKey] ?? '', ENT_QUOTES) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

</section>