<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="fas fa-user-circle fa-5x mb-2"></i>
                    <h3 class="mb-0"><?= htmlspecialchars($etudiant['nom']) ?></h3>
                    <h4 class="fw-light"><?= htmlspecialchars($etudiant['prenom']) ?></h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>CNE :</strong> <span><?= htmlspecialchars($etudiant['cne']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Classe :</strong> <span class="badge bg-info text-dark"><?= htmlspecialchars($etudiant['classe']) ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong><i class="fas fa-envelope me-2"></i> Email :</strong><br>
                            <?= htmlspecialchars($etudiant['email'] ?? 'Non renseigné') ?>
                        </li>
                        <li class="list-group-item">
                            <strong><i class="fas fa-phone me-2"></i> Téléphone :</strong><br>
                            <?= htmlspecialchars($etudiant['telephone'] ?? 'Non renseigné') ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-users me-2"></i> Contact Parents
                </div>
                <div class="card-body">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nom_parent'] ?? 'N/A') ?></p>
                    <p>
                        <strong><i class="fab fa-whatsapp text-success"></i> WhatsApp :</strong><br>
                        <a href="https://wa.me/<?= str_replace(['+', ' '], '', $etudiant['whatsapp_parent'] ?? '') ?>" target="_blank">
                            <?= htmlspecialchars($etudiant['whatsapp_parent'] ?? 'Non renseigné') ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            
            <div class="row mb-4">
                <div class="col-12">
                    <?php 
                        $color = 'success';
                        $msg = 'Assiduité Exemplaire';
                        if($totalAbsences > 0 && $totalAbsences <= 3) { $color = 'warning'; $msg = 'Attention requise'; }
                        if($totalAbsences > 3) { $color = 'danger'; $msg = 'Seuil Critique Dépassé'; }
                    ?>
                    <div class="alert alert-<?= $color ?> d-flex justify-content-between align-items-center shadow-sm">
                        <div>
                            <h4 class="alert-heading mb-0"><i class="fas fa-exclamation-circle"></i> État : <?= $msg ?></h4>
                        </div>
                        <div class="text-end">
                            <span class="display-4 fw-bold"><?= $totalAbsences ?></span><br>
                            <small>Absences totales</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-primary"><i class="fas fa-history me-2"></i> Historique des absences</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Matière</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($absences) || !is_array($absences)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                                            Aucune absence enregistrée. Bravo !
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($absences as $abs): ?>
                                        <?php 
                                            // 1. SÉCURITÉ : On vérifie que $abs est bien un tableau
                                            if (!is_array($abs)) continue;

                                            // 2. POLYMORPHISME : On cherche la date sous ses deux noms possibles
                                            $dateRaw = $abs['date_seance'] ?? $abs['date_absence'] ?? null;
                                            $heureRaw = $abs['heure_debut'] ?? '00:00';
                                            $matiere = $abs['matiere'] ?? 'Non renseigné';
                                            $justifie = $abs['justifie'] ?? 0;

                                            // 3. DATETIME SÉCURISÉ
                                            $dateAffiche = "Date inconnue";
                                            $heureAffiche = "";
                                            
                                            if ($dateRaw) {
                                                try {
                                                    $d = new DateTime($dateRaw);
                                                    $dateAffiche = $d->format('d/m/Y');
                                                } catch (Exception $e) { $dateAffiche = $dateRaw; }
                                            }

                                            if ($heureRaw) {
                                                try {
                                                    $h = new DateTime($heureRaw);
                                                    $heureAffiche = $h->format('H:i');
                                                } catch (Exception $e) { $heureAffiche = $heureRaw; }
                                            }
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($dateAffiche) ?></strong></td>
                                            <td><?= htmlspecialchars($heureAffiche) ?></td>
                                            <td><?= htmlspecialchars($matiere) ?></td>
                                            <td>
                                                <?php if($justifie): ?>
                                                    <span class="badge bg-success">Justifié</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Injustifié</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>