<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration de l'import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.match-found { background-color: #f0fff4; border-left: 4px solid #198754; }</style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Correspondance des colonnes</h4>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <strong>Délimiteur :</strong> <?= htmlspecialchars($detectedDelimiter) ?>
            </div>

            <form action="<?= BASE_URL ?>/import/preview" method="POST">
                
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($detectedDelimiter) ?>">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th>CSV</th><th>-></th><th>BDD</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csvHeaders as $index => $header): ?>
                                <?php 
                                    $hasSuggestion = isset($suggestedMapping[$index]);
                                    $rowClass = $hasSuggestion ? 'match-found' : '';
                                ?>
                            <tr class="<?= $rowClass ?>">
                                <td>
                                    <strong><?= htmlspecialchars($header) ?></strong>
                                    <?php if($hasSuggestion): ?><span class="badge bg-success">Auto</span><?php endif; ?>
                                </td>
                                <td>👉</td>
                                <td>
                                    <select name="mapping[<?= $index ?>]" class="form-select">
                                        <option value="">-- Ignorer --</option>
                                        <?php foreach ($dbColumns as $dbCol): ?>
                                            <?php $isSelected = ($hasSuggestion && $suggestedMapping[$index] === $dbCol) ? 'selected' : ''; ?>
                                            <option value="<?= $dbCol ?>" <?= $isSelected ?>><?= ucfirst($dbCol) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/import" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary btn-lg">Suivant : Aperçu</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>