<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog text-primary me-2"></i>Gestion du Personnel</h2>
        <a href="<?= BASE_URL ?>/users/parents" class="btn btn-info text-white shadow-sm">
            <i class="fas fa-user-shield me-2"></i> Voir les Comptes Parents
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Nouveau compte</h5>
                </div>
                <div class="card-body bg-light">
                    <form action="<?= BASE_URL ?>/users/create" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Nom complet</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Jean Dupont" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="jean@ecole.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="********" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="operateur">Opérateur (Accès standard)</option>
                                <option value="admin">Administrateur (Accès total)</option>
                            </select>
                            <div class="form-text text-muted small mt-1">
                                <i class="fas fa-info-circle"></i> L'admin peut gérer la configuration et les utilisateurs.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 shadow-sm">
                            <i class="fas fa-save me-2"></i> Créer l'utilisateur
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-muted">Liste des accès STAFF</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nom</th>
                                    <th>Email</th>
                                    <th class="text-center">Rôle</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($users) && !empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($u['nom']) ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small></td>
                                        <td class="text-center">
                                            <?php if($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger rounded-pill px-3">ADMIN</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark rounded-pill px-3">OPÉRATEUR</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                                <a href="<?= BASE_URL ?>/users/delete?id=<?= $u['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   title="Supprimer cet utilisateur"
                                                   onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small italic"><i class="fas fa-user-circle"></i> Vous</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            Aucun utilisateur trouvé.
                                        </td>
                                    </tr>
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