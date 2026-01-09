<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu de l'import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3><i class="fas fa-table"></i> Aperçu avant import</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Voici les 5 premières lignes. Vérifiez que les colonnes (CNE, Nom...) sont bien alignées.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($previewData[0] as $colName => $value): ?>
                                <th><?= htmlspecialchars($colName) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewData as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?= htmlspecialchars($value) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form action="<?= BASE_URL ?>/import/process" method="POST">
                
                <input type="hidden" name="mapping" value="<?= htmlspecialchars(json_encode($mapping)) ?>">

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/import" class="btn btn-warning">
                        <i class="fas fa-arrow-left"></i> Annuler et Corriger
                    </a>

                    <button type="submit" class="btn btn-success">
                        Confirmer et Importer <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>