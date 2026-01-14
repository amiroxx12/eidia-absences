<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Audit de Sécurité - EIDIA</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; }
        .container { max-width: 1200px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c3e50; color: white; }
        tr:hover { background-color: #f1f1f1; }
        
        /* Badges pour les actions */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; color: white; }
        .bg-login { background-color: #27ae60; } /* Vert */
        .bg-import { background-color: #2980b9; } /* Bleu */
        .bg-notif { background-color: #e67e22; }  /* Orange */
        .bg-admin { background-color: #c0392b; }  /* Rouge */
        .bg-magic { background-color: #8e44ad; }  /* Violet */
        
        .btn-back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #3498db; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="<?= BASE_URL ?>/dashboard" class="btn-back">← Retour au Dashboard</a>
    
    <h1>🕵️‍♂️ Journal d'Audit & Sécurité</h1>
    <p>Historique des 100 dernières actions critiques sur la plateforme.</p>

    <table>
        <thead>
            <tr>
                <th>Date & Heure</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Détails</th>
                <th>Adresse IP</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5" style="text-align:center;">Aucune activité enregistrée.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php 
                        // Choix de la couleur du badge
                        $badgeClass = 'bg-admin'; // Défaut
                        if ($log['action'] === 'LOGIN') $badgeClass = 'bg-login';
                        elseif ($log['action'] === 'IMPORT') $badgeClass = 'bg-import';
                        elseif ($log['action'] === 'NOTIFICATION') $badgeClass = 'bg-notif';
                        elseif ($log['action'] === 'MAGIC_LINK') $badgeClass = 'bg-magic';
                    ?>
                    <tr>
                        <td style="white-space:nowrap; color:#666;">
                            <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($log['user_nom'] ?? 'Parent') ?></strong>
                            <?php if(!empty($log['user_email'])): ?>
                                <br><small style="color:#888;"><?= htmlspecialchars($log['user_email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($log['action']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($log['details']) ?>
                        </td>
                        <td style="font-family:monospace;">
                            <?= htmlspecialchars($log['ip_address']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>