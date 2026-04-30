<footer class="py-4 mt-5 bg-body-tertiary border-top mt-auto">
    <div class="container text-center">
        <!-- On utilise la variable de traduction pour le nom, avec la date dynamique -->
        <span class="text-body-secondary">&copy; <?= date('Y') ?> <?= $t['app_name'] ?? 'ERP Événement Sportif' ?>.</span>
    </div>
</footer>

<!-- Script Bootstrap 5 (pour que les menus déroulants et composants fonctionnent) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>