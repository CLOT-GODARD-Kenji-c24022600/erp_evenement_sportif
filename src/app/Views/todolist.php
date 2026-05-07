<?php
$categories = [
    'general'   => ['label' => 'General',   'color' => 'secondary', 'icon' => 'bi-list-task'],
    'evenement' => ['label' => 'Evenement', 'color' => 'primary',   'icon' => 'bi-calendar-event'],
    'projet'    => ['label' => 'Projet',    'color' => 'info',      'icon' => 'bi-kanban'],
    'finance'   => ['label' => 'Finance',   'color' => 'success',   'icon' => 'bi-cash-coin'],
];

$priorities = [
    3 => ['label' => 'Haute',   'color' => 'danger'],
    2 => ['label' => 'Moyenne', 'color' => 'warning'],
    1 => ['label' => 'Basse',   'color' => 'success'],
];

$statuts = [
    'en_attente' => ['label' => 'En attente', 'color' => 'secondary', 'icon' => 'bi-hourglass'],
    'en_cours'   => ['label' => 'En cours',   'color' => 'primary',   'icon' => 'bi-play-circle'],
    'termine'    => ['label' => 'Termine',    'color' => 'success',   'icon' => 'bi-check-circle-fill'],
];

// Transitions autorisees par statut
$transitions = [
    'en_attente' => ['en_cours'   => ['label' => 'Demarrer',  'icon' => 'bi-play-circle']],
    'en_cours'   => [
        'en_attente' => ['label' => 'Mettre en attente', 'icon' => 'bi-hourglass'],
        'termine'    => ['label' => 'Terminer',           'icon' => 'bi-check-circle'],
    ],
    'termine'    => ['en_cours'   => ['label' => 'Reprendre', 'icon' => 'bi-arrow-counterclockwise']],
];

$total      = (int)($todoStats['total']      ?? 0);
$done       = (int)($todoStats['done']       ?? 0);
$en_cours   = (int)($todoStats['en_cours']   ?? 0);
$en_attente = (int)($todoStats['en_attente'] ?? 0);
$percent    = $total > 0 ? (int)round(($done / $total) * 100) : 0;

$actives   = array_filter($todos, fn($t) => $t['status'] !== 'termine');
$terminees = array_filter($todos, fn($t) => $t['status'] === 'termine');
?>

<section class="mb-5" aria-labelledby="todolist-heading">

    <header class="d-flex justify-content-between align-items-center mb-3">
        <hgroup>
            <h2 id="todolist-heading" class="fw-bold fs-5 mb-0">
                <i class="bi bi-check2-square me-2 text-primary" aria-hidden="true"></i>Taches &amp; Progression
            </h2>
            <p class="text-body-secondary small mb-0">
                <?= $done ?> / <?= $total ?> tache<?= $total > 1 ? 's' : '' ?> terminee<?= $done > 1 ? 's' : '' ?>
            </p>
        </hgroup>
        <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalNewTodo"
                type="button"
                aria-label="Creer une nouvelle tache">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nouvelle tache
        </button>
    </header>

    <!-- Barre de progression -->
    <figure class="mb-4" aria-label="Progression globale">
        <figcaption class="d-flex justify-content-between small text-body-secondary mb-1">
            <span>Progression globale</span>
            <strong><?= $percent ?>%</strong>
        </figcaption>
        <progress value="<?= $percent ?>" max="100" class="w-100"
                  style="height:10px; border-radius:8px; accent-color:#1a56db;"
                  aria-label="<?= $percent ?>% des taches completees"></progress>
    </figure>

    <!-- Statistiques rapides -->
    <ul class="list-unstyled d-flex gap-3 mb-4 flex-wrap">
        <li>
            <article class="rounded-3 px-3 py-2 shadow-sm border text-center" style="min-width:90px;">
                <p class="fw-bold fs-5 mb-0 text-primary"><?= $total ?></p>
                <p class="small text-body-secondary mb-0">Total</p>
            </article>
        </li>
        <li>
            <article class="rounded-3 px-3 py-2 shadow-sm border text-center" style="min-width:90px;">
                <p class="fw-bold fs-5 mb-0 text-secondary"><?= $en_attente ?></p>
                <p class="small text-body-secondary mb-0">En attente</p>
            </article>
        </li>
        <li>
            <article class="rounded-3 px-3 py-2 shadow-sm border text-center" style="min-width:90px;">
                <p class="fw-bold fs-5 mb-0 text-primary"><?= $en_cours ?></p>
                <p class="small text-body-secondary mb-0">En cours</p>
            </article>
        </li>
        <li>
            <article class="rounded-3 px-3 py-2 shadow-sm border text-center" style="min-width:90px;">
                <p class="fw-bold fs-5 mb-0 text-success"><?= $done ?></p>
                <p class="small text-body-secondary mb-0">Terminees</p>
            </article>
        </li>
    </ul>

    <!-- Filtres par categorie -->
    <nav aria-label="Filtrer les taches par categorie" class="mb-3">
        <ul class="nav nav-pills gap-1" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" type="button"
                        data-todo-filter="all" role="tab" aria-selected="true">
                    Tout <span class="badge bg-secondary ms-1"><?= count($todos) ?></span>
                </button>
            </li>
            <?php foreach ($categories as $key => $cat):
                $count = count(array_filter($todos, fn($t) => $t['category'] === $key));
                if ($count === 0) continue;
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" type="button"
                        data-todo-filter="<?= $key ?>" role="tab" aria-selected="false">
                    <i class="bi <?= $cat['icon'] ?> me-1" aria-hidden="true"></i><?= $cat['label'] ?>
                    <span class="badge bg-<?= $cat['color'] ?> ms-1"><?= $count ?></span>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Etat vide -->
    <?php if (empty($todos)): ?>
        <p class="text-body-secondary text-center py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
            Aucune tache pour le moment. Commencez par en creer une !
        </p>

    <?php else: ?>

        <!-- Taches actives (en attente + en cours) -->
        <?php if (!empty($actives)): ?>
        <section aria-labelledby="todo-actives-heading">
            <h3 id="todo-actives-heading" class="visually-hidden">Taches actives</h3>
            <ul class="list-group list-group-flush shadow-sm rounded-3 overflow-hidden mb-3" id="todo-list">
                <?php foreach ($actives as $todo):
                    $cat    = $categories[$todo['category']] ?? $categories['general'];
                    $prio   = $priorities[(int)$todo['priority']] ?? $priorities[1];
                    $statut = $statuts[$todo['status']] ?? $statuts['en_attente'];
                    $trans  = $transitions[$todo['status']] ?? [];
                ?>
                <li class="list-group-item px-3 py-3 todo-item"
                    data-category="<?= htmlspecialchars($todo['category']) ?>">
                    <article class="d-flex align-items-start gap-3">

                        <!-- Boutons de changement de statut -->
                        <nav aria-label="Changer le statut" class="flex-shrink-0 mt-1 d-flex flex-column gap-1">
                            <?php foreach ($trans as $newStatus => $info): ?>
                            <form method="POST" action="/?page=dashboard">
                                <input type="hidden" name="todo_action" value="set_status">
                                <input type="hidden" name="todo_id"     value="<?= (int)$todo['id'] ?>">
                                <input type="hidden" name="status"      value="<?= $newStatus ?>">
                                <button type="submit"
                                        class="btn p-0 border-0 bg-transparent"
                                        title="<?= $info['label'] ?>"
                                        aria-label="<?= $info['label'] ?>">
                                    <i class="bi <?= $info['icon'] ?> fs-5
                                        <?= $newStatus === 'termine'    ? 'text-success'   : '' ?>
                                        <?= $newStatus === 'en_cours'   ? 'text-primary'   : '' ?>
                                        <?= $newStatus === 'en_attente' ? 'text-secondary' : '' ?>"
                                       aria-hidden="true"></i>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </nav>

                        <!-- Contenu -->
                        <div class="flex-grow-1" style="min-width:0;">
                            <p class="mb-1 fw-semibold text-body lh-sm">
                                <?= htmlspecialchars($todo['title']) ?>
                            </p>
                            <?php if (!empty($todo['description'])): ?>
                                <p class="text-body-secondary small mb-1">
                                    <?= htmlspecialchars($todo['description']) ?>
                                </p>
                            <?php endif; ?>
                            <footer class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <!-- Statut -->
                                <span class="badge rounded-pill border bg-<?= $statut['color'] ?>-subtle text-<?= $statut['color'] ?> border-<?= $statut['color'] ?>-subtle">
                                    <i class="bi <?= $statut['icon'] ?> me-1" aria-hidden="true"></i><?= $statut['label'] ?>
                                </span>
                                <!-- Categorie -->
                                <span class="badge rounded-pill border bg-<?= $cat['color'] ?>-subtle text-<?= $cat['color'] ?> border-<?= $cat['color'] ?>-subtle">
                                    <i class="bi <?= $cat['icon'] ?> me-1" aria-hidden="true"></i><?= $cat['label'] ?>
                                </span>
                                <!-- Priorite -->
                                <span class="badge rounded-pill border bg-<?= $prio['color'] ?>-subtle text-<?= $prio['color'] ?> border-<?= $prio['color'] ?>-subtle">
                                    <?= $prio['label'] ?>
                                </span>
                                <!-- Echeance -->
                                <?php if (!empty($todo['due_date'])): ?>
                                <time datetime="<?= htmlspecialchars($todo['due_date']) ?>" class="small text-body-secondary">
                                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                                    <?= date('d/m/Y', strtotime($todo['due_date'])) ?>
                                </time>
                                <?php endif; ?>
                                <!-- Assigne a -->
                                <?php if (!empty($todo['assigne_prenom'])): ?>
                                <small class="text-body-secondary">
                                    <i class="bi bi-person-fill me-1 text-primary" aria-hidden="true"></i>
                                    <?= htmlspecialchars($todo['assigne_prenom'] . ' ' . $todo['assigne_nom']) ?>
                                </small>
                                <?php endif; ?>
                                <!-- Cree par -->
                                <?php if (!empty($todo['createur_prenom'])): ?>
                                <small class="text-body-secondary">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                                    <?= htmlspecialchars($todo['createur_prenom'] . ' ' . $todo['createur_nom']) ?>
                                </small>
                                <?php endif; ?>
                            </footer>
                        </div>

                        <!-- Actions edit / supprimer -->
                        <nav aria-label="Actions sur la tache" class="d-flex gap-1 flex-shrink-0">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary py-0 px-2"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($todo), ENT_QUOTES) ?>)"
                                    title="Modifier" aria-label="Modifier la tache">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </button>
                            <form method="POST" action="/?page=dashboard"
                                  onsubmit="return confirm('Supprimer cette tache ?')">
                                <input type="hidden" name="todo_action" value="delete">
                                <input type="hidden" name="todo_id"     value="<?= (int)$todo['id'] ?>">
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Supprimer" aria-label="Supprimer la tache">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </nav>

                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- Taches terminees (accordeon) -->
        <?php if (!empty($terminees)): ?>
        <details class="mt-2">
            <summary class="text-body-secondary small fw-semibold mb-2"
                     style="cursor:pointer; list-style:none; display:flex; align-items:center; gap:.4rem;">
                <i class="bi bi-check-all text-success" aria-hidden="true"></i>
                <?= count($terminees) ?> tache<?= count($terminees) > 1 ? 's' : '' ?> terminee<?= count($terminees) > 1 ? 's' : '' ?>
            </summary>
            <ul class="list-group list-group-flush rounded-3 overflow-hidden shadow-sm mt-2">
                <?php foreach ($terminees as $todo):
                    $trans = $transitions['termine'] ?? [];
                ?>
                <li class="list-group-item px-3 py-2 opacity-60">
                    <article class="d-flex align-items-center gap-3">

                        <!-- Bouton reprendre -->
                        <?php foreach ($trans as $newStatus => $info): ?>
                        <form method="POST" action="/?page=dashboard">
                            <input type="hidden" name="todo_action" value="set_status">
                            <input type="hidden" name="todo_id"     value="<?= (int)$todo['id'] ?>">
                            <input type="hidden" name="status"      value="<?= $newStatus ?>">
                            <button type="submit" class="btn p-0 border-0 bg-transparent"
                                    title="<?= $info['label'] ?>" aria-label="<?= $info['label'] ?>">
                                <i class="bi <?= $info['icon'] ?> fs-5 text-primary" aria-hidden="true"></i>
                            </button>
                        </form>
                        <?php endforeach; ?>

                        <p class="mb-0 flex-grow-1 text-decoration-line-through text-body-secondary small">
                            <?= htmlspecialchars($todo['title']) ?>
                            <?php if (!empty($todo['assigne_prenom'])): ?>
                                <small class="ms-2">
                                    <i class="bi bi-person-fill" aria-hidden="true"></i>
                                    <?= htmlspecialchars($todo['assigne_prenom'] . ' ' . $todo['assigne_nom']) ?>
                                </small>
                            <?php endif; ?>
                        </p>

                        <form method="POST" action="/?page=dashboard"
                              onsubmit="return confirm('Supprimer cette tache ?')">
                            <input type="hidden" name="todo_action" value="delete">
                            <input type="hidden" name="todo_id"     value="<?= (int)$todo['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                    aria-label="Supprimer la tache terminee">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        </details>
        <?php endif; ?>

    <?php endif; ?>

</section>


<!-- MODAL NOUVELLE TACHE -->
<dialog id="modalNewTodo" class="modal fade" tabindex="-1"
        aria-labelledby="modalNewTodoLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
            <header class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="modalNewTodoLabel">
                    <i class="bi bi-plus-circle me-2 text-primary" aria-hidden="true"></i>Nouvelle tache
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </header>
            <div class="modal-body pt-3">
                <form method="POST" action="/?page=dashboard" novalidate>
                    <input type="hidden" name="todo_action" value="create">
                    <p class="mb-3">
                        <label for="new-title" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                        <input type="text" id="new-title" name="title" class="form-control rounded-3"
                               placeholder="Ex : Confirmer le prestataire..." required maxlength="255">
                    </p>
                    <p class="mb-3">
                        <label for="new-desc" class="form-label fw-semibold">Description</label>
                        <textarea id="new-desc" name="description" class="form-control rounded-3"
                                  rows="2" placeholder="Details optionnels..."></textarea>
                    </p>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-category" class="form-label fw-semibold">Categorie</label>
                            <select id="new-category" name="category" class="form-select rounded-3">
                                <option value="general">General</option>
                                <option value="evenement">Evenement</option>
                                <option value="projet">Projet</option>
                                <option value="finance">Finance</option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="new-priority" class="form-label fw-semibold">Priorite</label>
                            <select id="new-priority" name="priority" class="form-select rounded-3">
                                <option value="1">Basse</option>
                                <option value="2" selected>Moyenne</option>
                                <option value="3">Haute</option>
                            </select>
                        </p>
                    </div>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-status" class="form-label fw-semibold">Statut</label>
                            <select id="new-status" name="status" class="form-select rounded-3">
                                <option value="en_attente" selected>En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Termine</option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="new-assigned" class="form-label fw-semibold">Assigner a</label>
                            <select id="new-assigned" name="assigned_to" class="form-select rounded-3">
                                <option value="">— Non assigne —</option>
                                <?php foreach ($utilisateurs as $u): ?>
                                <option value="<?= (int)$u['id'] ?>">
                                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-duedate" class="form-label fw-semibold">Echeance</label>
                            <input type="date" id="new-duedate" name="due_date" class="form-control rounded-3">
                        </p>
                        <p class="col mb-0">
                            <label for="new-event" class="form-label fw-semibold">Lier a un evenement</label>
                            <select id="new-event" name="event_id" class="form-select rounded-3">
                                <option value="">— Aucun —</option>
                                <?php foreach ($evenements as $ev): ?>
                                <option value="<?= (int)$ev['id'] ?>"><?= htmlspecialchars($ev['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <footer class="d-flex justify-content-end gap-2 pt-2 border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Creer la tache
                        </button>
                    </footer>
                </form>
            </div>
        </article>
    </div>
</dialog>


<!-- MODAL MODIFIER UNE TACHE -->
<dialog id="modalEditTodo" class="modal fade" tabindex="-1"
        aria-labelledby="modalEditTodoLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
            <header class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="modalEditTodoLabel">
                    <i class="bi bi-pencil me-2 text-primary" aria-hidden="true"></i>Modifier la tache
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </header>
            <div class="modal-body pt-3">
                <form method="POST" action="/?page=dashboard" novalidate>
                    <input type="hidden" name="todo_action" value="edit">
                    <input type="hidden" name="todo_id" id="edit-id">
                    <p class="mb-3">
                        <label for="edit-title" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                        <input type="text" id="edit-title" name="title" class="form-control rounded-3"
                               required maxlength="255">
                    </p>
                    <p class="mb-3">
                        <label for="edit-desc" class="form-label fw-semibold">Description</label>
                        <textarea id="edit-desc" name="description" class="form-control rounded-3" rows="2"></textarea>
                    </p>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-category" class="form-label fw-semibold">Categorie</label>
                            <select id="edit-category" name="category" class="form-select rounded-3">
                                <option value="general">General</option>
                                <option value="evenement">Evenement</option>
                                <option value="projet">Projet</option>
                                <option value="finance">Finance</option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="edit-priority" class="form-label fw-semibold">Priorite</label>
                            <select id="edit-priority" name="priority" class="form-select rounded-3">
                                <option value="1">Basse</option>
                                <option value="2">Moyenne</option>
                                <option value="3">Haute</option>
                            </select>
                        </p>
                    </div>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-status" class="form-label fw-semibold">Statut</label>
                            <select id="edit-status" name="status" class="form-select rounded-3">
                                <option value="en_attente">En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Termine</option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="edit-assigned" class="form-label fw-semibold">Assigner a</label>
                            <select id="edit-assigned" name="assigned_to" class="form-select rounded-3">
                                <option value="">— Non assigne —</option>
                                <?php foreach ($utilisateurs as $u): ?>
                                <option value="<?= (int)$u['id'] ?>">
                                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <div class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-duedate" class="form-label fw-semibold">Echeance</label>
                            <input type="date" id="edit-duedate" name="due_date" class="form-control rounded-3">
                        </p>
                        <p class="col mb-0">
                            <label for="edit-event" class="form-label fw-semibold">Lier a un evenement</label>
                            <select id="edit-event" name="event_id" class="form-select rounded-3">
                                <option value="">— Aucun —</option>
                                <?php foreach ($evenements as $ev): ?>
                                <option value="<?= (int)$ev['id'] ?>"><?= htmlspecialchars($ev['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <footer class="d-flex justify-content-end gap-2 pt-2 border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer
                        </button>
                    </footer>
                </form>
            </div>
        </article>
    </div>
</dialog>


<script>
function openEditModal(todo) {
    document.getElementById('edit-id').value        = todo.id          ?? '';
    document.getElementById('edit-title').value     = todo.title       ?? '';
    document.getElementById('edit-desc').value      = todo.description ?? '';
    document.getElementById('edit-category').value  = todo.category    ?? 'general';
    document.getElementById('edit-priority').value  = todo.priority    ?? 1;
    document.getElementById('edit-status').value    = todo.status      ?? 'en_attente';
    document.getElementById('edit-assigned').value  = todo.assigned_to ?? '';
    document.getElementById('edit-duedate').value   = todo.due_date    ?? '';
    document.getElementById('edit-event').value     = todo.event_id    ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditTodo')).show();
}

document.querySelectorAll('[data-todo-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-todo-filter]').forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        const filter = btn.dataset.todoFilter;
        document.querySelectorAll('.todo-item').forEach(item => {
            item.style.display = filter === 'all' || item.dataset.category === filter ? '' : 'none';
        });
    });
});
</script>