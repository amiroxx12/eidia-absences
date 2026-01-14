<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-file-csv me-2"></i>Importer des Étudiants</h3>
                    <p class="mb-0 opacity-75">Fichier CSV requis (UTF-8)</p>
                </div>
                
                <div class="card-body p-5">
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/import/upload" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4 text-center">
                            <div class="upload-icon mb-3 text-primary">
                                <i class="fas fa-cloud-upload-alt fa-4x"></i>
                            </div>
                            <label for="csv_file" class="form-label fw-bold">Choisissez votre fichier CSV</label>
                            <input class="form-control form-control-lg" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text mt-2">
                                Le fichier doit contenir au minimum : Nom, Prénom, Email Parent, Tél Parent, CIN Parent.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right me-2"></i>Passer à l'étape suivante
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        Format attendu : CSV.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>