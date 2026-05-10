/**
 * YES - Your Event Solution
 * JS : Dashboard (filtres todo, modale édition)
 *
 * @file dashboard.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

/**
 * Ouvre la modale d'édition d'une tâche et pré-remplit les champs.
 *
 * @param {Object} todo Données de la tâche.
 */
function openEditModal(todo) {
  const fields = {
    'edit-id':       todo.id          ?? '',
    'edit-title':    todo.title       ?? '',
    'edit-desc':     todo.description ?? '',
    'edit-category': todo.category    ?? 'general',
    'edit-priority': todo.priority    ?? 1,
    'edit-status':   todo.status      ?? 'en_attente',
    'edit-assigned': todo.assigned_to ?? '',
    'edit-duedate':  todo.due_date    ?? '',
    'edit-event':    todo.event_id    ?? '',
  };

  for (const [id, value] of Object.entries(fields)) {
    const el = document.getElementById(id);
    if (el) el.value = value;
  }

  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditTodo')).show();
}

document.addEventListener('DOMContentLoaded', () => {

  // ── Filtres de catégorie todo ───────────────────────
  document.querySelectorAll('[data-todo-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      // Réinitialise les actifs
      document.querySelectorAll('[data-todo-filter]').forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });

      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');

      const filter = btn.dataset.todoFilter;

      document.querySelectorAll('.todo-item').forEach(item => {
        item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
      });
    });
  });

});
