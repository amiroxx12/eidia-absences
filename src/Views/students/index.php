<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/students" class="row g-3 align-items-center">
                
                <div class="col-md-3">
                    <select name="classe" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Toutes les classes --</option>
                        <?php if(!empty($classes)): ?>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= (isset($_GET['classe']) && $_GET['classe'] == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/import" class="btn btn-success">
                            <i class="fas fa-file-import"></i> Importer CSV
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-secondary"><i class="fas fa-user-graduate me-2"></i>Liste des Étudiants</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Classe</th>
                            <th>CNE</th>
                            <th>Nom Prénom</th>
                            <th>CIN Parent</th> 
                            <th>Contact Parent</th>
                            <th class="text-center">Absences</th> 
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students) && empty($etudiants)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 text-secondary opacity-50"></i><br>
                                    Aucun étudiant trouvé pour cette recherche.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $liste = !empty($students) ? $students : ($etudiants ?? []); ?>
                            
                            <?php foreach ($liste as $etudiant): ?>
                            <tr>
                                <td class="ps-4"><span class="badge bg-light text-dark border"><?= htmlspecialchars($etudiant['classe'] ?? 'N/A') ?></span></td>
                                <td><code class="text-primary"><?= htmlspecialchars($etudiant['cne']) ?></code></td>
                                <td class="fw-bold">
                                    <a href="<?= BASE_URL ?>/students/details?cne=<?= $etudiant['cne'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($etudiant['nom']) ?> <?= htmlspecialchars($etudiant['prenom']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-uppercase fw-bold text-secondary" style="font-size: 0.9em;">
                                        <?= !empty($etudiant['cin_parent']) ? htmlspecialchars($etudiant['cin_parent']) : '<span class="text-muted fw-normal">-</span>' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($etudiant['telephone_parent'])): ?>
                                        <div class="d-flex flex-column" style="font-size: 0.9rem;">
                                            <span><i class="fas fa-phone-alt text-success me-1"></i> <?= htmlspecialchars($etudiant['telephone_parent']) ?></span>
                                            <?php if(!empty($etudiant['whatsapp_parent'])): ?>
                                                <small class="text-muted"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp OK</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Non renseigné</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php 
                                        $nb = $etudiant['total_absences'] ?? 0;
                                        if ($nb == 0) {
                                            $badgeClass = 'bg-success'; 
                                        } elseif ($nb <= 3) {
                                            $badgeClass = 'bg-warning text-dark'; 
                                        } else {
                                            $badgeClass = 'bg-danger'; 
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill" style="min-width: 30px;">
                                        <?= $nb ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= BASE_URL ?>/students/details?cne=<?= $etudiant['cne'] ?>" class="btn btn-sm btn-outline-primary" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                            <form action="<?= BASE_URL ?>/students/delete" method="POST" class="d-inline" onsubmit="return confirm('⚠️ ALERTE ⚠️\n\nVous êtes sur le point de supprimer l\'étudiant <?= htmlspecialchars($etudiant['nom']) ?>.\n\nCeci va effacer :\n1. Sa fiche étudiant\n2. TOUTES ses absences dans TOUS les mois\n\nÊtes-vous sûr ?');">
                                                <input type="hidden" name="id" value="<?= $etudiant['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer définitivement">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small">
            Affichage des résultats.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>