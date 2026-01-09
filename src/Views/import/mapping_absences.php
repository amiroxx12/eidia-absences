<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-random"></i> Correspondance des colonnes (Absences)</h4>
        </div>
        <div class="card-body">
            <p class="alert alert-info">
                Associez les colonnes de votre fichier CSV aux champs de la base de données.
            </p>

            <form action="<?= BASE_URL ?>/import/preview-absences" method="POST">
                
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($detectedDelimiter) ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Colonne du CSV</th>
                                <th>Exemple</th>
                                <th>Champ Base de Données</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csvHeaders as $index => $header): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($header) ?></td>
                                    <td class="text-muted small">Col <?= $index + 1 ?></td>
                                    <td>
                                        <select name="mapping[<?= $index ?>]" class="form-select">
                                            <option value="">-- Ignorer --</option>
                                            
                                            <?php foreach ($dbColumns as $dbCol): ?>
                                                <?php 
                                                    // Auto-sélection si correspondance trouvée par Levenshtein
                                                    $selected = (isset($suggestedMapping[$index]) && $suggestedMapping[$index] === $dbCol) ? 'selected' : '';
                                                    
                                                    $labels = [
                                                        'etudiant_cne' => 'CNE Étudiant (Obligatoire)',
                                                        'date_seance'  => 'Date (YYYY-MM-DD)',
                                                        'heure_debut'  => 'Heure (HH:MM)',
                                                        'matiere'      => 'Matière / Module'
                                                    ];
                                                ?>
                                                <option value="<?= $dbCol ?>" <?= $selected ?>>
                                                    <?= $labels[$dbCol] ?? $dbCol ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/import/absences" class="btn btn-secondary">Retour</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-eye"></i> Prévisualiser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>