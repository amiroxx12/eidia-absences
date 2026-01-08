<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importer des utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Importer un fichier CSV</h4>
                </div>
                <div class="card-body">
                    
                    <form action="<?= BASE_URL ?>/import/upload" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label for="csv_file" class="form-label">Choisir le fichier (.csv)</label>
                            <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Analyser le fichier</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>