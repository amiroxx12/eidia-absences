<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="fas fa-calendar-check text-primary me-2"></i>Rapport Mensuel
            </h2>
            <p class="text-muted mb-0">Gestion et validation des absences (Mode PHP Strict)</p>
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
            </form>
        </div>
    </div>

    <?php 
    $targetAbsence = null;
    if (isset($_GET['check_id']) && !empty($absences)) {
        foreach ($absences as $a) {
            if ($a['id'] == $_GET['check_id']) {
                $targetAbsence = $a;
                break;
            }
        }
    }
    ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">
                <i class="fas fa-history me-2"></i>
                Registre : <span class="text-dark fw-bold"><?= htmlspecialchars($monthName ?? $selectedMonth) ?></span>
            </h5>
            <span class="badge rounded-pill bg-primary">
                <?= $totalAbsences ?? count($absences) ?> enregistrement(s)
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Étudiant</th>
                            <th>Matière</th>
                            <th class="text-center">Statut Justification</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($absences)): ?>
                            <tr><td colspan="5" class="text-center py-5">Rien à signaler.</td></tr>
                        <?php else: ?>
                            <?php foreach ($absences as $abs): ?>
                                <?php $status = $abs['justification_status'] ?? 'NON_JUSTIFIE'; ?>
                                
                                <tr class="<?= (isset($_GET['check_id']) && $_GET['check_id'] == $abs['id']) ? 'table-warning' : '' ?>">
                                    <td class="ps-4">
                                        <div class="fw-bold"><?= date('d/m/Y', strtotime($abs['date_seance'])) ?></div>
                                        <small class="text-muted"><?= substr($abs['heure_debut'], 0, 5) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($abs['nom_complet']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($abs['classe']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($abs['matiere']) ?></td>
                                   
                                    <td class="text-center">
                                        <?php if ($status === 'VALIDE'): ?>
                                            <span class="badge bg-success">Validée</span>
                                        <?php elseif ($status === 'REFUSE'): ?>
                                            <span class="badge bg-danger">Refusée</span>
                                        <?php elseif ($status === 'EN_ATTENTE'): ?>
                                            <a href="?month=<?= $selectedMonth ?>&check_id=<?= $abs['id'] ?>&page=<?= $page ?? 1 ?>" 
                                               class="btn btn-warning btn-sm text-dark fw-bold shadow-sm">
                                                <i class="fas fa-search me-1"></i> Examiner
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border">Non justifiée</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end pe-4">
                                        <?php if (($abs['statut_notification'] ?? '') === 'Notifié' || strpos(($abs['statut_notification'] ?? ''), 'Notifié') !== false): ?>
                                            <span class="badge bg-success me-2" title="Notification envoyée"><i class="fas fa-check"></i> Notifié</span>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>/absences/notifyManual" method="POST" class="d-inline" onsubmit="return confirm('⚠️ Envoyer une alerte au parent ?');">
                                                <input type="hidden" name="id" value="<?= $abs['id'] ?>">
                                                <input type="hidden" name="table" value="absences_<?= $selectedMonth ?>">
                                                <button type="submit" class="btn btn-sm btn-warning text-dark me-2">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/students/details?cne=<?= $abs['etudiant_cne'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-user-graduate"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="card-footer bg-white border-top-0 py-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-sm" href="?month=<?= $selectedMonth ?>&page=<?= $page - 1 ?>">Précédent</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link text-dark fw-bold border-0">Page <?= $page ?> / <?= $totalPages ?></span>
                        </li>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-sm" href="?month=<?= $selectedMonth ?>&page=<?= $page + 1 ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($targetAbsence): ?>
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1040;"></div>
    <div class="modal fade show" style="display: block; z-index: 1050;" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Preuve de : <?= htmlspecialchars($targetAbsence['nom_complet']) ?></h5>
                    <a href="?month=<?= $selectedMonth ?>&page=<?= $page ?>" class="btn-close"></a>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-8 bg-dark text-center py-4 border-end d-flex align-items-center justify-content-center" style="min-height: 60vh;">
                            <?php 
                                // --- FIX CORRIGÉ : UTILISATION DE BASE_URL ---
                                $fileUrl = BASE_URL . '/uploads/justifications/' . $targetAbsence['justification_file'];
                                $ext = pathinfo($targetAbsence['justification_file'], PATHINFO_EXTENSION);
                            ?>
                            <?php if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                <img src="<?= $fileUrl ?>" style="max-width: 95%; max-height: 75vh;" alt="Justificatif">
                            <?php elseif (strtolower($ext) === 'pdf'): ?>
                                <embed src="<?= $fileUrl ?>" width="100%" height="600px" type="application/pdf">
                            <?php else: ?>
                                <div class="text-white">
                                    <i class="fas fa-file-download fa-3x mb-3"></i><br>
                                    Format non prévisualisable.<br>
                                    <a href="<?= $fileUrl ?>" class="btn btn-light mt-2" download>Télécharger</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 p-4 bg-white">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Motif déclaré :</h6>
                            <blockquote class="blockquote bg-light p-3 rounded border-start border-primary border-4">
                                "<?= htmlspecialchars($targetAbsence['justification_motif'] ?? 'Aucun motif') ?>"
                            </blockquote>
                            <form action="<?= BASE_URL ?>/absences/decide-justification" method="POST" class="mt-4">
                                <input type="hidden" name="absence_id" value="<?= $targetAbsence['id'] ?>">
                                <input type="hidden" name="table_name" value="<?= 'absences_' . $selectedMonth ?>">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="decision" value="VALIDE" class="btn btn-success btn-lg">ACCEPTER</button>
                                    <button type="submit" name="decision" value="REFUSE" class="btn btn-outline-danger">REFUSER</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
