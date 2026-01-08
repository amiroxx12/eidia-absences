<?php
// On récupère les erreurs s'il y en a (envoyées par le Controller)
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EIDIA Absences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <div class="text-center mb-4">
        <h3 class="text-primary">EIDIA Absences</h3>
        <p class="text-muted">Portail de gestion</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= defined('BASE_URL') ? BASE_URL : '/eidia-absences/public' ?>/login" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email académique</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="admin@eidia.edu">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </div>
    </form>
</div>

</body>
</html>