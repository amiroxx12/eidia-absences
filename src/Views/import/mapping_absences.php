<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-magic"></i> Détection Automatique des Colonnes</h4>
        </div>
        <div class="card-body">
            <p class="alert alert-light border">
                <strong>Information :</strong> L'algorithme a analysé votre fichier. 
                Vérifiez les colonnes marquées <span class="badge bg-warning text-dark">Manuel</span> ou avec un score faible.
            </p>

            <form action="<?= BASE_URL ?>/import/previewAbsences" method="POST">
                
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($detectedDelimiter) ?>">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Colonne CSV</th>
                                <th style="width: 35%">Champ Système</th>
                                <th style="width: 40%">Analyse IA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csvHeaders as $index => $header): ?>
                                <?php 
                                    // Récupération des données enrichies du Service
                                    $suggestion = $suggestedMapping[$index] ?? null;
                                    $detectedField = $suggestion['target_field'] ?? '';
                                    $score = $suggestion['confidence'] ?? 0;
                                    $status = $suggestion['status'] ?? 'manuel';
                                    
                                    // Labels pour l'affichage humain
                                    $labels = [
                                        'etudiant_cne' => 'CNE Étudiant (Obligatoire)',
                                        'date_seance'  => 'Date Séance (YYYY-MM-DD)',
                                        'heure_debut'  => 'Heure Début',
                                        'matiere'      => 'Matière / Module',
                                        'motif'        => 'Motif / Justification'
                                    ];

                                    // Classes CSS selon le statut
                                    $rowClass = ($status === 'auto') ? '' : 'table-warning';
                                    $badgeClass = ($status === 'auto') ? 'bg-success' : (($score > 0) ? 'bg-warning text-dark' : 'bg-secondary');
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td class="fw-bold">
                                        <?= htmlspecialchars($header) ?>
                                        <div class="small text-muted">Index: <?= $index ?></div>
                                    </td>
                                    
                                    <td>
                                        <select name="mapping[<?= $index ?>]" class="form-select <?= ($status === 'auto') ? 'is-valid' : '' ?>">
                                            <option value="">-- Ignorer cette colonne --</option>
                                            
                                            <?php foreach ($dbColumns as $dbCol): ?>
                                                <option value="<?= $dbCol ?>" <?= ($detectedField === $dbCol) ? 'selected' : '' ?>>
                                                    <?= $labels[$dbCol] ?? $dbCol ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td>
                                        <?php if ($detectedField): ?>
                                            <div class="d-flex align-items-center">
                                                <span class="badge <?= $badgeClass ?> me-2">
                                                    <?= $score ?>% Confiance
                                                </span>
                                                <span class="small text-muted">
                                                    (Détection : <?= ucfirst($status) ?>)
                                                </span>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px; width: 100px;">
                                                <div class="progress-bar <?= ($status === 'auto') ? 'bg-success' : 'bg-warning' ?>" 
                                                     role="progressbar" style="width: <?= $score ?>%"></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Non reconnu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/import/absences" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check"></i> Valider et Prévisualiser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>