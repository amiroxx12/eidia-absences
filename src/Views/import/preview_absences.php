<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-list-check"></i> Vérification avant import</h4>
        </div>
        <div class="card-body">
            <p class="lead">Voici comment les 5 premières lignes seront importées.</p>
            
            <div class="alert alert-light border">
                Si des colonnes affichent "N/A", vérifiez votre mapping à l'étape précédente.
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>CNE</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Matière</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewData as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['etudiant_cne'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['date_seance'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['heure_debut'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['matiere'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form action="<?= BASE_URL ?>/import/process-absences" method="POST">
                <input type="hidden" name="mapping" value='<?= json_encode($mapping) ?>'>
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($delimiter) ?>">

                <div class="d-flex justify-content-between mt-4">
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Corriger le Mapping
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle"></i> Confirmer et Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>