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
 * @version 1.2
 * @since 2026
 *
 * Variables attendues :
 * @var array $t Traductions chargées.
 */

declare(strict_types=1);
?>
    <footer class="py-4 bg-body border-top mt-auto app-footer">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center small text-body-secondary">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?>
                </p>
                <nav aria-label="Liens du pied de page">
                    <ul class="list-unstyled d-flex gap-3 align-items-center mb-0">
                        <li>
                            <a href="#" class="text-decoration-none text-reset">
                                <?= htmlspecialchars($t['footer_help'], ENT_QUOTES) ?>
                            </a>
                        </li>
                        <li class="border-start ps-3">
                            <a href="#" class="text-decoration-none text-reset">
                                <?= htmlspecialchars($t['footer_privacy'], ENT_QUOTES) ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </footer>

    </main><!-- /.main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/layout.js"></script>
<script src="assets/js/search.js"></script>
<script src="assets/js/presence.js"></script>