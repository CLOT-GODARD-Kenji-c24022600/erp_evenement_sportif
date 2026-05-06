<?php
use App\Controllers\UserController;

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<main class='container py-5'><aside class='alert alert-danger'>Accès refusé.</aside></main>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    // On empêche l'admin de se supprimer lui-même par erreur
    if ($_POST['user_id'] != $_SESSION['user_id']) {
        UserController::gererAction((int)$_POST['user_id'], $_POST['action']);
    }
}

$users = UserController::getAll();
?>

<main class="container py-4">
    <header class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-primary">👥 Gestion des Utilisateurs</h2>
            <p class="text-muted">Gérez les accès, les rôles et les comptes.</p>
        </div>
    </header>

    <section class="card shadow-sm border-0">
        <article class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger">ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">STAFF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['statut'] === 'en_attente'): ?>
                                <span class="badge bg-warning text-dark">En attente</span>
                            <?php elseif ($u['statut'] === 'approuve'): ?>
                                <span class="badge bg-success">Approuvé</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejeté</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($u['id'] != $_SESSION['user_id']): // On cache les actions pour son propre compte ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                
                                <?php if ($u['statut'] !== 'approuve'): ?>
                                    <button type="submit" name="action" value="approuver" class="btn btn-sm btn-success">✅</button>
                                <?php endif; ?>
                                <?php if ($u['statut'] !== 'rejete'): ?>
                                    <button type="submit" name="action" value="rejeter" class="btn btn-sm btn-warning">❌</button>
                                <?php endif; ?>

                                <?php if ($u['role'] === 'staff' && $u['statut'] === 'approuve'): ?>
                                    <button type="submit" name="action" value="promouvoir_admin" class="btn btn-sm btn-outline-danger" title="Passer Admin">⬆️ Admin</button>
                                <?php elseif ($u['role'] === 'admin'): ?>
                                    <button type="submit" name="action" value="retrograder_staff" class="btn btn-sm btn-outline-secondary" title="Passer Staff">⬇️ Staff</button>
                                <?php endif; ?>

                                <button type="submit" name="action" value="supprimer" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Supprimer définitivement ce compte ?');">🗑️</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>