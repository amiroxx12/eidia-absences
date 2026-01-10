<?php 
// 1. CHARGEMENT DU HEADER
// Cela inclut : <!DOCTYPE>, <html>, <head> (CSS), <body> et le MENU de navigation intelligent
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Tableau de bord</h1>
            <p class="lead text-muted">Bienvenue sur l'interface de gestion EIDIA Absences.</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6">Année 2025/2026</span>
        </div>
    </div>

    <div class="row g-3">
        
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-primary h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-0">Étudiants</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold mb-0"><?= $stats['total_etudiants'] ?? 0 ?></h2>
                            <small>Inscrits</small>
                        </div>
                        <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-success h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-0">Classes</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold mb-0"><?= $stats['total_classes'] ?? 0 ?></h2>
                            <small>Groupes TD/TP</small>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-danger h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-0">Absences</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold mb-0"><?= $stats['total_absences'] ?? 0 ?></h2>
                            <small>Cumul total</small>
                        </div>
                        <i class="fas fa-user-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-header bg-light fw-bold text-dark">Actions</div>
                <div class="card-body p-2">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/students" class="btn btn-sm btn-outline-primary text-start">
                            <i class="fas fa-list me-2"></i> Liste Étudiants
                        </a>
                        
                        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
                            <a href="<?= BASE_URL ?>/import/absences" class="btn btn-sm btn-outline-danger text-start">
                                <i class="fas fa-calendar-times me-2"></i> Import Absences
                            </a>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                             <a href="<?= BASE_URL ?>/import" class="btn btn-sm btn-outline-dark text-start">
                                <i class="fas fa-file-import me-2"></i> Import CSV Étudiants
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <?php if(isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <?php unset($_SESSION['flash_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// 3. CHARGEMENT DU FOOTER
// C'est CRUCIAL : c'est lui qui contient <script src="bootstrap...js">
// Sans lui, le menu déroulant du Header ne marchera pas.
require_once __DIR__ . '/layouts/footer.php'; 
?>