<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="fas fa-calendar-check text-primary me-2"></i>Rapport Mensuel
            </h2>
            <p class="text-muted mb-0">Gestion et consultation des absences par période</p>
        </div>
        
        <div class="col-md-6">
            <form method="GET" action="<?= BASE_URL ?>/absences/monthly" class="d-flex justify-content-md-end align-items-center">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-white"><i class="fas fa-filter text-muted"></i></span>
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        <?php if (empty($tables)): ?>
                            <option value="">Aucune donnée disponible</option>
                        <?php else: ?>
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= $t['value'] ?>" <?= ($selectedMonth == $t['value']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Voir</button>
                </div>

                <?php if (!empty($absences)): ?>
                <a href="<?= BASE_URL ?>/absences/export?month=<?= $selectedMonth ?>" class="btn btn-outline-success ms-2" title="Exporter en CSV">
                    <i class="fas fa-file-csv fa-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">
                <i class="fas fa-history me-2"></i>
                Registre de : <span class="text-dark fw-bold"><?= htmlspecialchars($monthName ?? $selectedMonth) ?></span>
            </h5>
            <span class="badge rounded-pill bg-primary">
                <?= count($absences) ?> enregistrement(s)
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Heure</th>
                            <th>Étudiant</th>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th class="text-center">Justifié</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($absences)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" style="width: 80px; opacity: 0.3;" class="mb-3"><br>
                                    <span class="text-muted">Aucune absence enregistrée pour cette période.</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($absences as $abs): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-calendar-alt me-2 text-muted"></i>
                                            <?= date('d/m/Y', strtotime($abs['date_seance'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge border text-dark fw-normal">
                                            <i class="far fa-clock me-1 text-primary"></i>
                                            <?= substr($abs['heure_debut'], 0, 5) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            <?= htmlspecialchars($abs['nom_complet']) ?>
                                        </div>
                                        <small class="text-muted">CNE: <?= htmlspecialchars($abs['etudiant_cne']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($abs['classe']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($abs['matiere']) ?></td>
                                    <td class="text-center">
                                        <?php if ($abs['justifie']): ?>
                                            <span class="text-success" title="<?= htmlspecialchars($abs['motif'] ?? '') ?>">
                                                <i class="fas fa-check-circle fa-lg"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times-circle fa-lg"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= BASE_URL ?>/students/details?cne=<?= $abs['etudiant_cne'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Voir fiche étudiant">
                                            <i class="fas fa-user-graduate"></i>
                                        </a>

                                        <a href="<?= BASE_URL ?>/absences/notifyManual?cne=<?= $abs['etudiant_cne'] ?>&month=<?= $selectedMonth ?>" 
                                           class="btn btn-sm btn-warning ms-1"
                                           title="Envoyer un rappel manuel aux parents"
                                           onclick="return confirm('Voulez-vous envoyer un rappel manuel (Email + WhatsApp) aux parents de <?= htmlspecialchars($abs['nom_complet']) ?> ?');">
                                            <i class="fas fa-bell"></i>
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>