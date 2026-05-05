<?php if (isset($_SESSION['user_id']) && !in_array($page, $pages_publiques)): ?>
    <footer class="py-4 bg-body border-top mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center small text-body-secondary">
                <div>
                    &copy; <?= date('Y') ?> <?= $t['app_name'] ?? 'SportEvent ERP' ?>.
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <a href="#" class="text-decoration-none text-reset">Aide</a>
                    <a href="#" class="text-decoration-none text-reset border-start ps-3">Confidentialité</a>
                </div>
            </div>
        </div>
    </footer>
    </div> 
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. GESTION DE LA SIDEBAR
    const sbBtn = document.getElementById('sidebarToggle');
    if (sbBtn) {
        sbBtn.addEventListener('click', (e) => {
            e.preventDefault();
            document.body.classList.toggle('collapsed');
            const isCollapsed = document.body.classList.contains('collapsed');
            document.cookie = "sidebar=" + (isCollapsed ? 'collapsed' : 'expanded') + "; path=/; max-age=31536000";
        });
    }

    // 2. GESTION DU MODE SOMBRE (Mise à jour dynamique des icônes)
    const themeBtn = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            let currentTheme = htmlElement.getAttribute('data-bs-theme');
            let newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            
            // Mise à jour de l'icône
            if (newTheme === 'dark') {
                themeBtn.innerHTML = '<i class="bi bi-moon-stars-fill text-warning"></i>';
            } else {
                themeBtn.innerHTML = '<i class="bi bi-sun-fill text-warning"></i>';
            }
            
            document.cookie = "theme=" + newTheme + "; max-age=31536000; path=/";
        });
    }
</script>
</body>
</html>