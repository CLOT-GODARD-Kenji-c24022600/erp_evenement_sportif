<!-- src/app/Views/dashboard.php -->
<div class="container mt-5 mb-5">
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold text-body"><?= $t['nav_dashboard'] ?></h1>
            <p class="text-body-secondary"><?= $t['dash_welcome'] ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-primary border-0 shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> 
                <strong><?= $t['dash_empty_title'] ?></strong> <?= $t['dash_empty_desc'] ?>
            </div>
        </div>
    </div>
</div>