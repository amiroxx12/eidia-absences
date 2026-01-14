<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-shield text-info me-2"></i>Comptes Parents</h2>
        <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour à la gestion du personnel
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Parent</th>
                            <th>Email</th>
                            <th>Enfants rattachés</th>
                            <th class="text-center">Statut</th>
                            <th>Dernière connexion</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($accounts) && !empty($accounts)): ?>
                            <?php foreach ($accounts as $acc): ?>
                            <tr>
                                <td class="ps-4 font-weight-bold">
                                    <?php 
                                        $nomParent = $acc['nom_parent'] ?? 'Non renseigné';
                                        echo htmlspecialchars($nomParent);
                                    ?>
                                </td>
                                
                                <td><small><?= htmlspecialchars($acc['email_parent'] ?? '') ?></small></td>
                                
                                <td><span class="badge bg-info text-dark fw-normal"><?= htmlspecialchars($acc['enfants'] ?? '') ?></span></td>
                                
                                <td class="text-center">
                                    <?php if (!empty($acc['parent_active'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> En attente</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <small class="text-muted">
                                        <?= !empty($acc['parent_last_login']) ? date('d/m/Y H:i', strtotime($acc['parent_last_login'])) : 'Jamais' ?>
                                    </small>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <?php $parentId = $acc['id'] ?? ''; ?>
                                    
                                    <?php if($parentId): ?>
                                        <form action="<?= BASE_URL ?>/users/resend-magic-link" method="POST" class="d-inline" onsubmit="return confirm('Renvoyer le lien d\'accès magique par email ?');">
                                            <input type="hidden" name="parent_id" value="<?= htmlspecialchars($parentId) ?>"> 
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Renvoyer le lien d'activation">
                                                <i class="fas fa-paper-plane"></i> Renvoyer Lien
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-danger small">ID Manquant</span>
                                    <?php endif; ?>
                                </td>
                                
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Aucun parent trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>