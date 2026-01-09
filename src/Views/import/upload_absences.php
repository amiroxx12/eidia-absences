<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-file-csv"></i> Import Absences (Hebdo)</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div class="alert alert-info">
                        <strong>Format attendu :</strong> Un fichier CSV avec au minimum :
                        <ul>
                            <li>CNE (Identifiant étudiant)</li>
                            <li>Date (AAAA-MM-JJ)</li>
                            <li>Heure (HH:MM)</li>
                            <li>Matière</li>
                        </ul>
                    </div>

                    <form action="<?= BASE_URL ?>/import/absences" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="csv_absences" class="form-label">Fichier CSV des absences</label>
                            <input type="file" class="form-control" name="csv_absences" id="csv_absences" required accept=".csv">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-arrow-right"></i> Étape suivante : Mapping
                            </button>
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>