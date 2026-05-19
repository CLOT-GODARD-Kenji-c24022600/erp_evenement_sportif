<?php

/**
 * YES - Your Event Solution
 *
 * Layout : Pied de page de l'application.
 * Les balises </body> et </html> sont gérées par Renderer::renderApp().
 *
 * @file footer.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.3
 * @since 2026
 *
 * Variables attendues :
 * @var array $t Traductions chargées.
 */

declare(strict_types=1);
?>
    </main><!-- /.main-content -->

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
                            <a href="/plan_du_site" class="text-decoration-none text-reset">
                                Plan du site
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/layout.js"></script>
<script src="/assets/js/search.js"></script>
<script src="/assets/js/presence.js"></script>
<script src="/assets/js/routeur.js"></script>