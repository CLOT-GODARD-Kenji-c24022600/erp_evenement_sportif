/**
 * YES - Your Event Solution
 * JS : Page d'erreur 404 — bouton retour navigateur.
 *
 * @file 404.js
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const btnBack = document.getElementById('btn-back');

    if (!btnBack) return;

    /**
     * Revient à la page précédente dans l'historique navigateur.
     * Si aucun historique n'est disponible, redirige vers le dashboard.
     *
     * @returns {void}
     */
    btnBack.addEventListener('click', () => {
        if (history.length > 1) {
            history.back();
        } else {
            window.location.href = '/dashboard';
        }
    });
});