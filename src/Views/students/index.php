<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function confirmDelete(id) {
            if(confirm("Êtes-vous sûr de vouloir supprimer cet étudiant ? Ses absences seront aussi effacées.")) {
                window.location.href = "<?= BASE_URL ?>/students/delete?id=" + id;
            }
        }
    </script>
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="container mt-4">
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/students" class="row g-3 align-items-center">
                
                <div class="col-md-3">
                    <select name="classe" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Toutes les classes --</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= (isset($_GET['classe']) && $_GET['classe'] == $c) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (Nom, CNE...)" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        <?php if(isset($_GET['search']) || isset($_GET['classe'])): ?>
                            <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary" title="Réinitialiser"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3 text-end">
                    <a href="<?= BASE_URL ?>/import" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Importer CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Classe</th>
                            <th>CNE</th>
                            <th>Nom Prénom</th>
                            <th>Contact Parent</th>
                            <th class="text-center">Absences</th> <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($etudiants)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-2x mb-3"></i><br>
                                    Aucun étudiant trouvé pour cette recherche.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($etudiant['classe'] ?? 'N/A') ?></span></td>
                                <td><code><?= htmlspecialchars($etudiant['cne']) ?></code></td>
                                <td class="fw-bold">
                                    <a href="<?= BASE_URL ?>/students/details?cne=<?= $etudiant['cne'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($etudiant['nom']) ?> <?= htmlspecialchars($etudiant['prenom']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if(!empty($etudiant['telephone_parent'])): ?>
                                        <i class="fas fa-phone-alt text-success"></i> <?= htmlspecialchars($etudiant['telephone_parent']) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Non renseigné</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php 
                                        $nb = $etudiant['total_absences'] ?? 0;
                                        if ($nb == 0) {
                                            $badgeClass = 'bg-success'; // Vert
                                        } elseif ($nb <= 3) {
                                            $badgeClass = 'bg-warning text-dark'; // Orange
                                        } else {
                                            $badgeClass = 'bg-danger'; // Rouge
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill" style="min-width: 30px;">
                                        <?= $nb ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/students/details?cne=<?= $etudiant['cne'] ?>" class="btn btn-sm btn-outline-primary" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <button onclick="confirmDelete(<?= $etudiant['id'] ?>)" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-2 text-muted small">
        Total : <strong><?= count($etudiants) ?></strong> étudiants affichés.
    </div>
</div>

</body>
</html>