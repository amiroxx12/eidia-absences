<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-check-circle"></i> Prévisualisation avant Import</h4>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info border-info">
                <i class="fas fa-info-circle me-2"></i>
                Voici un aperçu des <strong>10 premières lignes</strong>. 
                Si tout semble correct, cliquez sur "Confirmer l'import".
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="table-light">
                        <tr>
                            <?php if (!empty($previewData)): ?>
                                <?php foreach (array_keys($previewData[0]) as $header): ?>
                                    <th><?= htmlspecialchars($header) ?></th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewData as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= htmlspecialchars($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form action="<?= BASE_URL ?>/import/processAbsences" method="POST">
                
                <input type="hidden" name="mapping" value='<?= json_encode($mapping) ?>'>
                
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/import/absences" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Annuler / Recommencer
                    </a>
                    
                    <button type="submit" class="btn btn-success px-5" onclick="return confirm('Confirmer l\'import et l\'envoi des notifications ?');">
                        <i class="fas fa-save me-2"></i>Confirmer l'Import
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>