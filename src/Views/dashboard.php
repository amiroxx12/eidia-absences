<?php 
// 1. CHARGEMENT DU HEADER
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="container mt-4">

    <div class="dashboard-header mb-4">
        <div class="header-content d-flex justify-content-between align-items-center">
            <div>
                <h1 class="dashboard-title fw-bold text-dark">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Tableau de bord
                </h1>
                <p class="dashboard-subtitle text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Vue d'ensemble de la gestion des absences EIDIA
                </p>
            </div>
            <div class="header-badge">
                <div class="badge bg-primary shadow-sm rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Année 2025/2026
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Étudiants</h6>
                            <h2 class="fw-bold mb-0 text-dark"><?= $stats['total_etudiants'] ?? 0 ?></h2>
                            <small class="text-muted"><i class="fas fa-users me-1"></i> Inscrits</small>
                        </div>
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Classes</h6>
                            <h2 class="fw-bold mb-0 text-dark"><?= $stats['total_classes'] ?? 0 ?></h2>
                            <small class="text-muted"><i class="fas fa-layer-group me-1"></i> Groupes TD/TP</small>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Absences</h6>
                            <h2 class="fw-bold mb-0 text-dark"><?= $stats['total_absences'] ?? 0 ?></h2>
                            <small class="text-muted"><i class="fas fa-exclamation-triangle me-1"></i> Total enregistré</small>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                            <i class="fas fa-user-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Présence</h6>
                            <h2 class="fw-bold mb-0 text-dark">
                                <?php 
                                $total_etu = $stats['total_etudiants'] ?? 1;
                                $total_abs = $stats['total_absences'] ?? 0;
                                // Petit calcul pour éviter la division par zéro
                                $taux = ($total_etu > 0) ? round((1 - ($total_abs / ($total_etu * 100))) * 100, 1) : 100;
                                echo max(0, min(100, $taux)) . '%';
                                ?>
                            </h2>
                            <small class="text-muted"><i class="fas fa-chart-pie me-1"></i> Taux estimé</small>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark"><i class="fas fa-bolt me-2 text-warning"></i>Actions rapides</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        
                        <a href="<?= BASE_URL ?>/students" class="d-flex align-items-center p-3 border rounded text-decoration-none bg-light hover-shadow transition">
                            <div class="p-3 bg-white text-primary rounded shadow-sm me-3">
                                <i class="fas fa-list fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold text-dark">Liste des étudiants</h6>
                                <small class="text-muted">Consulter et gérer les étudiants</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>

                        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
                        <a href="<?= BASE_URL ?>/import/absences" class="d-flex align-items-center p-3 border rounded text-decoration-none bg-light hover-shadow transition">
                            <div class="p-3 bg-white text-danger rounded shadow-sm me-3">
                                <i class="fas fa-calendar-times fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold text-dark">Importer des absences</h6>
                                <small class="text-muted">Importer le fichier CSV des absences</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/import" class="d-flex align-items-center p-3 border rounded text-decoration-none bg-light hover-shadow transition">
                            <div class="p-3 bg-white text-success rounded shadow-sm me-3">
                                <i class="fas fa-file-import fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold text-dark">Importer des étudiants</h6>
                                <small class="text-muted">Charger la liste CSV des étudiants</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>

                        <a href="<?= BASE_URL ?>/users" class="d-flex align-items-center p-3 border rounded text-decoration-none bg-light hover-shadow transition">
                            <div class="p-3 bg-white text-dark rounded shadow-sm me-3">
                                <i class="fas fa-users-cog fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold text-dark">Gérer les utilisateurs</h6>
                                <small class="text-muted">Administration des accès système</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark"><i class="fas fa-info-circle me-2 text-info"></i>Infos Session</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3 text-center" style="width: 40px;">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Utilisateur</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Invité') ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3 text-center" style="width: 40px;">
                            <i class="fas fa-shield-alt fa-2x text-success"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Rôle</small>
                            <span class="fw-bold text-dark">
                                <?php
                                $role = $_SESSION['user_role'] ?? 'guest';
                                $labels = ['admin' => 'Administrateur', 'operateur' => 'Opérateur', 'viewer' => 'Lecteur'];
                                echo $labels[$role] ?? ucfirst($role);
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="me-3 text-center" style="width: 40px;">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Connecté à</small>
                            <span class="fw-bold text-dark"><?= date('H:i') ?></span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-book-reader fa-3x mb-3 text-white-50"></i>
                    <h5 class="fw-bold">Besoin d'aide ?</h5>
                    <p class="small text-white-50 mb-3">Consultez la documentation du projet pour comprendre toutes les fonctionnalités.</p>
                    <a href="/eidia-absences/public/documentation_projet.html" class="btn btn-light text-primary fw-bold w-100 shadow-sm">
                        Voir la doc
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.hover-shadow:hover {
    background-color: #fff !important;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.05)!important;
    border-color: transparent !important;
    transform: translateY(-2px);
}
.transition {
    transition: all 0.3s ease;
}
</style>

<?php 
// 3. CHARGEMENT DU FOOTER
require_once __DIR__ . '/layouts/footer.php'; 
?>