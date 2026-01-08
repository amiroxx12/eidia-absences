<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu de l'importation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Aperçu avant validation</h4>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                Voici un extrait des <strong>5 premières lignes</strong> telles qu'elles seront enregistrées.
                <br>Si cela vous semble correct, validez l'importation en bas de page.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($finalMapping as $dbCol): ?>
                                <?php if(!empty($dbCol)): ?>
                                    <th><?= ucfirst($dbCol) ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $row): ?>
                        <tr>
                            <?php foreach ($finalMapping as $csvIndex => $dbCol): ?>
                                <?php if(!empty($dbCol)): ?>
                                    <td>
                                        <?php 
                                            // On récupère la valeur ou on met un tiret si vide
                                            $val = $row[$csvIndex] ?? ''; 
                                            
                                            // Sécurité visuelle : ne pas afficher les mots de passe si mappés
                                            if ($dbCol === 'password') {
                                                echo '<em>(Généré auto)</em>';
                                            } else {
                                                echo htmlspecialchars($val); 
                                            }
                                        ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form action="/import/process" method="POST" class="mt-4">
                
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($delimiter) ?>">
                
                <?php foreach ($finalMapping as $index => $col): ?>
                    <input type="hidden" name="mapping[<?= $index ?>]" value="<?= htmlspecialchars($col) ?>">
                <?php endforeach; ?>

                <?php if (isset($saveConfig) && $saveConfig): ?>
                    <input type="hidden" name="save_config" value="1">
                    <input type="hidden" name="config_name" value="<?= htmlspecialchars($configName) ?>">
                <?php endif; ?>

                <div class="d-flex justify-content-between">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Corriger le mapping
                    </a>
                    
                    <button type="submit" class="btn btn-success btn-lg">
                        Confirmer et Importer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>