<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importer des étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-file-csv"></i> Importer des étudiants (CSV)</h3>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <p>Sélectionnez un fichier CSV contenant la liste des étudiants. Le système vous aidera ensuite à faire correspondre les colonnes.</p>
                    
                    <form action="<?= BASE_URL ?>/import/upload" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="csv_file" class="form-label fw-bold">Fichier CSV (.csv)</label>
                            <input class="form-control form-control-lg" type="file" id="csv_file" name="csv_file" required accept=".csv">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                Suivant <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>