<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration de l'import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .match-perfect { background-color: #d1e7dd; border-left: 5px solid #198754; }
        .match-partial { background-color: #fff3cd; border-left: 5px solid #ffc107; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-random"></i> Correspondance des colonnes</h4>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Délimiteur détecté : <strong><?= htmlspecialchars($detectedDelimiter) ?></strong>
            </div>

            <form action="<?= BASE_URL ?>/import/preview" method="POST">
                
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($detectedDelimiter) ?>">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 40%">Colonne du CSV</th>
                                <th style="width: 10%" class="text-center"><i class="fas fa-arrow-right"></i></th>
                                <th style="width: 50%">Champ dans la Base de Données</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csvHeaders as $index => $header): ?>
                                <?php 
                                    // 1. Récupération intelligente des données du Service
                                    $suggestion = $suggestedMapping[$index] ?? null;
                                    $targetField = $suggestion['target_field'] ?? null;
                                    $confidence = $suggestion['confidence'] ?? 0;
                                    
                                    // 2. Détermination du style (Vert si sûr, Jaune si doute, Blanc si rien)
                                    $rowClass = '';
                                    if ($targetField && $confidence >= 90) $rowClass = 'match-perfect';
                                    elseif ($targetField && $confidence >= 70) $rowClass = 'match-partial';
                                ?>
                            <tr class="<?= $rowClass ?>">
                                <td>
                                    <strong><?= htmlspecialchars($header) ?></strong>
                                    
                                    <?php if($confidence >= 90): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Auto (100%)</span>
                                    <?php elseif($confidence >= 70): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-question"></i> Probable</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">👉</td>
                                <td>
                                    <select name="mapping[<?= $index ?>]" class="form-select <?= ($confidence >= 90) ? 'border-success' : '' ?>">
                                        <option value="">-- Ignorer cette colonne --</option>
                                        <?php foreach ($dbColumns as $dbCol): ?>
                                            <?php 
                                                // 3. LA CORRECTION EST ICI : On compare avec $targetField
                                                $isSelected = ($targetField === $dbCol) ? 'selected' : ''; 
                                            ?>
                                            <option value="<?= $dbCol ?>" <?= $isSelected ?>>
                                                <?= ucfirst(str_replace('_', ' ', $dbCol)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/import" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <div>
                        <small class="text-muted me-3">Vérifiez bien les correspondances avant de continuer.</small>
                        <button type="submit" class="btn btn-primary btn-lg">
                            Suivant : Aperçu <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>