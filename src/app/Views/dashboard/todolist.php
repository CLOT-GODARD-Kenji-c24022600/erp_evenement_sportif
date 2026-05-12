<?php

/**
 * YES - Your Event Solution
 *
 * Vue partielle : Liste des tâches (Todo).
 * Incluse par dashboard.php.
 *
 * @file todolist.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 *
 * Variables attendues (héritées du dashboard) :
 * @var array $todos        Liste complète des tâches.
 * @var array $todoStats    Statistiques des tâches.
 * @var array $utilisateurs Liste des utilisateurs pour le select.
 * @var array $evenements   Liste des événements pour le select.
 * @var array $projets      Liste des projets pour le select.
 * @var array $t            Traductions chargées.
 */

declare(strict_types=1);

$categories = [
    'general'   => ['label' => $t['todo_cat_general'], 'color' => 'secondary', 'icon' => 'bi-list-task'],
    'evenement' => ['label' => $t['todo_cat_event'],   'color' => 'primary',   'icon' => 'bi-calendar-event'],
    'projet'    => ['label' => $t['todo_cat_project'], 'color' => 'info',      'icon' => 'bi-kanban'],
    'finance'   => ['label' => $t['todo_cat_finance'], 'color' => 'success',   'icon' => 'bi-cash-coin'],
];

$priorities = [
    3 => ['label' => $t['todo_priority_high'],   'color' => 'danger'],
    2 => ['label' => $t['todo_priority_medium'], 'color' => 'warning'],
    1 => ['label' => $t['todo_priority_low'],    'color' => 'success'],
];

$statuts = [
    'en_attente' => ['label' => $t['todo_status_pending'],  'color' => 'secondary', 'icon' => 'bi-hourglass'],
    'en_cours'   => ['label' => $t['todo_status_progress'], 'color' => 'primary',   'icon' => 'bi-play-circle'],
    'termine'    => ['label' => $t['todo_status_done'],     'color' => 'success',   'icon' => 'bi-check-circle-fill'],
];

$transitions = [
    'en_attente' => ['en_cours'   => ['label' => $t['todo_btn_start'],  'icon' => 'bi-play-circle']],
    'en_cours'   => [
        'en_attente' => ['label' => $t['todo_btn_pause'],  'icon' => 'bi-hourglass'],
        'termine'    => ['label' => $t['todo_btn_finish'], 'icon' => 'bi-check-circle'],
    ],
    'termine'    => ['en_cours'   => ['label' => $t['todo_btn_resume'], 'icon' => 'bi-arrow-counterclockwise']],
];

$total      = (int) ($todoStats['total']      ?? 0);
$done       = (int) ($todoStats['done']       ?? 0);
$en_cours   = (int) ($todoStats['en_cours']   ?? 0);
$en_attente = (int) ($todoStats['en_attente'] ?? 0);
$percent    = $total > 0 ? (int) round(($done / $total) * 100) : 0;

$actives   = array_filter($todos, fn($t) => $t['status'] !== 'termine');
$terminees = array_filter($todos, fn($t) => $t['status'] === 'termine');
?>

<section class="mb-5" aria-labelledby="todolist-heading">

    <header class="d-flex justify-content-between align-items-center mb-3">
        <hgroup>
            <h2 id="todolist-heading" class="fw-bold fs-5 mb-0">
                <i class="bi bi-check2-square me-2 text-primary" aria-hidden="true"></i>
                <?= htmlspecialchars($t['todo_title'], ENT_QUOTES) ?>
            </h2>
            <p class="text-body-secondary small mb-0">
                <?= $done ?> / <?= $total ?> <?= htmlspecialchars($t['todo_done_count'], ENT_QUOTES) ?>
            </p>
        </hgroup>
        <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalNewTodo"
                type="button"
                aria-label="<?= htmlspecialchars($t['todo_new_btn'], ENT_QUOTES) ?>">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
            <?= htmlspecialchars($t['todo_new_btn'], ENT_QUOTES) ?>
        </button>
    </header>

    <figure class="mb-4" aria-label="<?= htmlspecialchars($t['todo_global_progress'], ENT_QUOTES) ?>">
        <figcaption class="d-flex justify-content-between small text-body-secondary mb-1">
            <span><?= htmlspecialchars($t['todo_global_progress'], ENT_QUOTES) ?></span>
            <strong><?= $percent ?>%</strong>
        </figcaption>
        <progress value="<?= $percent ?>" max="100" class="w-100 todo-progress"
                  aria-label="<?= $percent ?>%"></progress>
    </figure>

    <ul class="list-unstyled d-flex gap-3 mb-4 flex-wrap">
        <?php foreach ([
            ['val' => $total,      'color' => 'primary',   'label' => $t['todo_total']],
            ['val' => $en_attente, 'color' => 'secondary', 'label' => $t['todo_pending']],
            ['val' => $en_cours,   'color' => 'primary',   'label' => $t['todo_in_progress']],
            ['val' => $done,       'color' => 'success',   'label' => $t['todo_done']],
        ] as $stat): ?>
        <li>
            <article class="rounded-3 px-3 py-2 shadow-sm border text-center todo-stat-card">
                <p class="fw-bold fs-5 mb-0 text-<?= $stat['color'] ?>"><?= $stat['val'] ?></p>
                <p class="small text-body-secondary mb-0"><?= htmlspecialchars($stat['label'], ENT_QUOTES) ?></p>
            </article>
        </li>
        <?php endforeach; ?>
    </ul>

    <nav aria-label="<?= htmlspecialchars($t['todo_filter_all'], ENT_QUOTES) ?>" class="mb-3">
        <ul class="nav nav-pills gap-1" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" type="button"
                        data-todo-filter="all" role="tab" aria-selected="true">
                    <?= htmlspecialchars($t['todo_filter_all'], ENT_QUOTES) ?>
                    <span class="badge bg-secondary ms-1"><?= count($todos) ?></span>
                </button>
            </li>
            <?php foreach ($categories as $key => $cat):
                $count = count(array_filter($todos, fn($todo) => $todo['category'] === $key));
                if ($count === 0) continue;
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" type="button"
                        data-todo-filter="<?= $key ?>" role="tab" aria-selected="false">
                    <i class="bi <?= $cat['icon'] ?> me-1" aria-hidden="true"></i>
                    <?= htmlspecialchars($cat['label'], ENT_QUOTES) ?>
                    <span class="badge bg-<?= $cat['color'] ?> ms-1"><?= $count ?></span>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <?php if (empty($todos)): ?>
        <p class="text-body-secondary text-center py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
            <?= htmlspecialchars($t['todo_empty'], ENT_QUOTES) ?>
        </p>
    <?php else: ?>

        <?php if (!empty($actives)): ?>
        <section aria-labelledby="todo-actives-heading">
            <h3 id="todo-actives-heading" class="visually-hidden">Tâches actives</h3>
            <ul class="list-group list-group-flush shadow-sm rounded-3 overflow-hidden mb-3" id="todo-list">
                <?php foreach ($actives as $todo):
                    $cat    = $categories[$todo['category']] ?? $categories['general'];
                    $prio   = $priorities[(int) $todo['priority']] ?? $priorities[1];
                    $statut = $statuts[$todo['status']] ?? $statuts['en_attente'];
                    $trans  = $transitions[$todo['status']] ?? [];
                ?>
                <li class="list-group-item px-3 py-3 todo-item"
                    data-category="<?= htmlspecialchars((string) $todo['category'], ENT_QUOTES) ?>">
                    <article class="d-flex align-items-start gap-3">

                        <nav aria-label="Changer le statut" class="flex-shrink-0 mt-1 d-flex flex-column gap-1">
                            <?php foreach ($trans as $newStatus => $info): ?>
                            <form method="POST" action="/dashboard">
                                <input type="hidden" name="todo_action" value="set_status">
                                <input type="hidden" name="todo_id"     value="<?= (int) $todo['id'] ?>">
                                <input type="hidden" name="status"      value="<?= htmlspecialchars($newStatus, ENT_QUOTES) ?>">
                                <button type="submit" class="btn p-0 border-0 bg-transparent"
                                        title="<?= htmlspecialchars($info['label'], ENT_QUOTES) ?>"
                                        aria-label="<?= htmlspecialchars($info['label'], ENT_QUOTES) ?>">
                                    <i class="bi <?= $info['icon'] ?> fs-5
                                        <?= $newStatus === 'termine'    ? 'text-success'   : '' ?>
                                        <?= $newStatus === 'en_cours'   ? 'text-primary'   : '' ?>
                                        <?= $newStatus === 'en_attente' ? 'text-secondary' : '' ?>"
                                       aria-hidden="true"></i>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </nav>

                        <section class="flex-grow-1" style="min-width:0;">
                            <p class="mb-1 fw-semibold text-body lh-sm">
                                <?= htmlspecialchars((string) $todo['title'], ENT_QUOTES) ?>
                            </p>
                            <?php if (!empty($todo['description'])): ?>
                                <p class="text-body-secondary small mb-1">
                                    <?= htmlspecialchars((string) $todo['description'], ENT_QUOTES) ?>
                                </p>
                            <?php endif; ?>
                            <footer class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <span class="badge rounded-pill border bg-<?= $statut['color'] ?>-subtle text-<?= $statut['color'] ?> border-<?= $statut['color'] ?>-subtle">
                                    <i class="bi <?= $statut['icon'] ?> me-1" aria-hidden="true"></i>
                                    <?= htmlspecialchars($statut['label'], ENT_QUOTES) ?>
                                </span>
                                <span class="badge rounded-pill border bg-<?= $cat['color'] ?>-subtle text-<?= $cat['color'] ?> border-<?= $cat['color'] ?>-subtle">
                                    <i class="bi <?= $cat['icon'] ?> me-1" aria-hidden="true"></i>
                                    <?= htmlspecialchars($cat['label'], ENT_QUOTES) ?>
                                </span>
                                <span class="badge rounded-pill border bg-<?= $prio['color'] ?>-subtle text-<?= $prio['color'] ?> border-<?= $prio['color'] ?>-subtle">
                                    <?= htmlspecialchars($prio['label'], ENT_QUOTES) ?>
                                </span>
                                <?php if (!empty($todo['due_date'])): ?>
                                <time datetime="<?= htmlspecialchars((string) $todo['due_date'], ENT_QUOTES) ?>" class="small text-body-secondary">
                                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                                    <?= date('d/m/Y', strtotime((string) $todo['due_date'])) ?>
                                </time>
                                <?php endif; ?>
                                <?php if (!empty($todo['assigne_prenom'])): ?>
                                <small class="text-body-secondary">
                                    <i class="bi bi-person-fill me-1 text-primary" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) $todo['assigne_prenom'] . ' ' . $todo['assigne_nom'], ENT_QUOTES) ?>
                                </small>
                                <?php endif; ?>
                                <?php if (!empty($todo['projet_nom'])): ?>
                                <small class="text-body-secondary">
                                    <i class="bi bi-kanban-fill me-1 text-info" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) $todo['projet_nom'], ENT_QUOTES) ?>
                                </small>
                                <?php endif; ?>
                                <?php if (!empty($todo['createur_prenom'])): ?>
                                <small class="text-body-secondary">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) $todo['createur_prenom'] . ' ' . $todo['createur_nom'], ENT_QUOTES) ?>
                                </small>
                                <?php endif; ?>
                            </footer>
                        </section>

                        <nav aria-label="Actions sur la tâche" class="d-flex gap-1 flex-shrink-0">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary py-0 px-2"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($todo), ENT_QUOTES) ?>)"
                                    title="<?= htmlspecialchars($t['btn_edit'], ENT_QUOTES) ?>"
                                    aria-label="<?= htmlspecialchars($t['btn_edit'], ENT_QUOTES) ?>">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </button>
                            <form method="POST" action="/dashboard"
                                  onsubmit="return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>')">
                                <input type="hidden" name="todo_action" value="delete">
                                <input type="hidden" name="todo_id"     value="<?= (int) $todo['id'] ?>">
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="<?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>"
                                        aria-label="<?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>">
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

        <?php if (!empty($terminees)): ?>
        <details class="mt-2">
            <summary class="text-body-secondary small fw-semibold mb-2 todo-summary">
                <i class="bi bi-check-all text-success" aria-hidden="true"></i>
                <?= count($terminees) ?> <?= htmlspecialchars($t['todo_done_count'], ENT_QUOTES) ?>
            </summary>
            <ul class="list-group list-group-flush rounded-3 overflow-hidden shadow-sm mt-2">
                <?php foreach ($terminees as $todo):
                    $trans = $transitions['termine'] ?? [];
                ?>
                <li class="list-group-item px-3 py-2 opacity-60">
                    <article class="d-flex align-items-center gap-3">
                        <?php foreach ($trans as $newStatus => $info): ?>
                        <form method="POST" action="/dashboard">
                            <input type="hidden" name="todo_action" value="set_status">
                            <input type="hidden" name="todo_id"     value="<?= (int) $todo['id'] ?>">
                            <input type="hidden" name="status"      value="<?= htmlspecialchars($newStatus, ENT_QUOTES) ?>">
                            <button type="submit" class="btn p-0 border-0 bg-transparent"
                                    title="<?= htmlspecialchars($info['label'], ENT_QUOTES) ?>"
                                    aria-label="<?= htmlspecialchars($info['label'], ENT_QUOTES) ?>">
                                <i class="bi <?= $info['icon'] ?> fs-5 text-primary" aria-hidden="true"></i>
                            </button>
                        </form>
                        <?php endforeach; ?>
                        <p class="mb-0 flex-grow-1 text-decoration-line-through text-body-secondary small">
                            <?= htmlspecialchars((string) $todo['title'], ENT_QUOTES) ?>
                            <?php if (!empty($todo['assigne_prenom'])): ?>
                                <small class="ms-2">
                                    <i class="bi bi-person-fill" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) $todo['assigne_prenom'] . ' ' . $todo['assigne_nom'], ENT_QUOTES) ?>
                                </small>
                            <?php endif; ?>
                        </p>
                        <form method="POST" action="/dashboard"
                              onsubmit="return confirm('<?= htmlspecialchars($t['users_confirm_delete'], ENT_QUOTES) ?>')">
                            <input type="hidden" name="todo_action" value="delete">
                            <input type="hidden" name="todo_id"     value="<?= (int) $todo['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                    aria-label="<?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>">
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

<!-- Modal : Nouvelle tâche -->
<dialog id="modalNewTodo" class="modal fade" tabindex="-1"
        aria-labelledby="modalNewTodoLabel" aria-modal="true">
    <section class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
            <header class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="modalNewTodoLabel">
                    <i class="bi bi-plus-circle me-2 text-primary" aria-hidden="true"></i>
                    <?= htmlspecialchars($t['todo_modal_new_title'], ENT_QUOTES) ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
            </header>
            <div class="modal-body pt-3">
                <form method="POST" action="/dashboard" novalidate>
                    <input type="hidden" name="todo_action" value="create">
                    <p class="mb-3">
                        <label for="new-title" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['todo_field_title'], ENT_QUOTES) ?>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="new-title" name="title" class="form-control rounded-3"
                               required maxlength="255">
                    </p>
                    <p class="mb-3">
                        <label for="new-desc" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['todo_field_desc'], ENT_QUOTES) ?>
                        </label>
                        <textarea id="new-desc" name="description" class="form-control rounded-3" rows="2"></textarea>
                    </p>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-category" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_category'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-category" name="category" class="form-select rounded-3">
                                <option value="general"><?= htmlspecialchars($t['todo_cat_general'], ENT_QUOTES) ?></option>
                                <option value="evenement"><?= htmlspecialchars($t['todo_cat_event'], ENT_QUOTES) ?></option>
                                <option value="projet"><?= htmlspecialchars($t['todo_cat_project'], ENT_QUOTES) ?></option>
                                <option value="finance"><?= htmlspecialchars($t['todo_cat_finance'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="new-priority" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_priority'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-priority" name="priority" class="form-select rounded-3">
                                <option value="1"><?= htmlspecialchars($t['todo_priority_low'], ENT_QUOTES) ?></option>
                                <option value="2" selected><?= htmlspecialchars($t['todo_priority_medium'], ENT_QUOTES) ?></option>
                                <option value="3"><?= htmlspecialchars($t['todo_priority_high'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-status" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_status'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-status" name="status" class="form-select rounded-3">
                                <option value="en_attente" selected><?= htmlspecialchars($t['todo_status_pending'], ENT_QUOTES) ?></option>
                                <option value="en_cours"><?= htmlspecialchars($t['todo_status_progress'], ENT_QUOTES) ?></option>
                                <option value="termine"><?= htmlspecialchars($t['todo_status_done'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="new-assigned" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_assigned'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-assigned" name="assigned_to" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_assigned'], ENT_QUOTES) ?></option>
                                <?php foreach ($utilisateurs as $u): ?>
                                <option value="<?= (int) $u['id'] ?>">
                                    <?= htmlspecialchars((string) $u['prenom'] . ' ' . $u['nom'], ENT_QUOTES) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-duedate" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_duedate'], ENT_QUOTES) ?>
                            </label>
                            <input type="date" id="new-duedate" name="due_date" class="form-control rounded-3">
                        </p>
                        <p class="col mb-0">
                            <label for="new-event" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_event'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-event" name="event_id" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_event'], ENT_QUOTES) ?></option>
                                <?php foreach ($evenements as $ev): ?>
                                <option value="<?= (int) $ev['id'] ?>"><?= htmlspecialchars((string) $ev['nom'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="new-projet" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_project'], ENT_QUOTES) ?>
                            </label>
                            <select id="new-projet" name="projet_id" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_project'], ENT_QUOTES) ?></option>
                                <?php foreach ($projets as $pr): ?>
                                <option value="<?= (int) $pr['id'] ?>"><?= htmlspecialchars((string) $pr['nom'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <footer class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                            <?= htmlspecialchars($t['todo_btn_cancel'], ENT_QUOTES) ?>
                        </button>
                        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($t['todo_btn_create'], ENT_QUOTES) ?>
                        </button>
                    </footer>
                </form>
            </div>
        </article>
    </section>
</dialog>

<!-- Modal : Modifier une tâche -->
<dialog id="modalEditTodo" class="modal fade" tabindex="-1"
        aria-labelledby="modalEditTodoLabel" aria-modal="true">
    <section class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
            <header class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="modalEditTodoLabel">
                    <i class="bi bi-pencil me-2 text-primary" aria-hidden="true"></i>
                    <?= htmlspecialchars($t['todo_modal_edit_title'], ENT_QUOTES) ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
            </header>
            <div class="modal-body pt-3">
                <form method="POST" action="/dashboard" novalidate>
                    <input type="hidden" name="todo_action" value="edit">
                    <input type="hidden" name="todo_id" id="edit-id">
                    <p class="mb-3">
                        <label for="edit-title" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['todo_field_title'], ENT_QUOTES) ?>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="edit-title" name="title" class="form-control rounded-3" required maxlength="255">
                    </p>
                    <p class="mb-3">
                        <label for="edit-desc" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['todo_field_desc'], ENT_QUOTES) ?>
                        </label>
                        <textarea id="edit-desc" name="description" class="form-control rounded-3" rows="2"></textarea>
                    </p>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-category" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_category'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-category" name="category" class="form-select rounded-3">
                                <option value="general"><?= htmlspecialchars($t['todo_cat_general'], ENT_QUOTES) ?></option>
                                <option value="evenement"><?= htmlspecialchars($t['todo_cat_event'], ENT_QUOTES) ?></option>
                                <option value="projet"><?= htmlspecialchars($t['todo_cat_project'], ENT_QUOTES) ?></option>
                                <option value="finance"><?= htmlspecialchars($t['todo_cat_finance'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="edit-priority" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_priority'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-priority" name="priority" class="form-select rounded-3">
                                <option value="1"><?= htmlspecialchars($t['todo_priority_low'], ENT_QUOTES) ?></option>
                                <option value="2"><?= htmlspecialchars($t['todo_priority_medium'], ENT_QUOTES) ?></option>
                                <option value="3"><?= htmlspecialchars($t['todo_priority_high'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-status" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_status'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-status" name="status" class="form-select rounded-3">
                                <option value="en_attente"><?= htmlspecialchars($t['todo_status_pending'], ENT_QUOTES) ?></option>
                                <option value="en_cours"><?= htmlspecialchars($t['todo_status_progress'], ENT_QUOTES) ?></option>
                                <option value="termine"><?= htmlspecialchars($t['todo_status_done'], ENT_QUOTES) ?></option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="edit-assigned" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_assigned'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-assigned" name="assigned_to" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_assigned'], ENT_QUOTES) ?></option>
                                <?php foreach ($utilisateurs as $u): ?>
                                <option value="<?= (int) $u['id'] ?>">
                                    <?= htmlspecialchars((string) $u['prenom'] . ' ' . $u['nom'], ENT_QUOTES) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-duedate" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_duedate'], ENT_QUOTES) ?>
                            </label>
                            <input type="date" id="edit-duedate" name="due_date" class="form-control rounded-3">
                        </p>
                        <p class="col mb-0">
                            <label for="edit-event" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_event'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-event" name="event_id" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_event'], ENT_QUOTES) ?></option>
                                <?php foreach ($evenements as $ev): ?>
                                <option value="<?= (int) $ev['id'] ?>"><?= htmlspecialchars((string) $ev['nom'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <section class="row g-3 mb-3">
                        <p class="col mb-0">
                            <label for="edit-projet" class="form-label fw-semibold">
                                <?= htmlspecialchars($t['todo_field_project'], ENT_QUOTES) ?>
                            </label>
                            <select id="edit-projet" name="projet_id" class="form-select rounded-3">
                                <option value=""><?= htmlspecialchars($t['todo_no_project'], ENT_QUOTES) ?></option>
                                <?php foreach ($projets as $pr): ?>
                                <option value="<?= (int) $pr['id'] ?>"><?= htmlspecialchars((string) $pr['nom'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </section>
                    <footer class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                            <?= htmlspecialchars($t['todo_btn_cancel'], ENT_QUOTES) ?>
                        </button>
                        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($t['todo_btn_save'], ENT_QUOTES) ?>
                        </button>
                    </footer>
                </form>
            </div>
        </article>
    </section>
</dialog>