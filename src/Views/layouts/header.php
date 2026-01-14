<?php 
// Sécurité session
if (session_status() === PHP_SESSION_NONE) session_start(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EIDIA Absences - Gestion</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- STYLE "APPLE / CLEAN" --- */
        body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; }
        
        /* Navbar Blanche avec effet de flou */
        .navbar { 
            backdrop-filter: blur(10px); 
            background-color: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid #e5e7eb;
        }

        /* Liens de navigation */
        .nav-link { font-weight: 500; color: #4b5563; transition: all 0.2s; font-size: 0.95rem; }
        .nav-link:hover, .nav-link.active { color: #2563eb; }
        .nav-link i { width: 20px; text-align: center; margin-right: 5px; }
        
        /* Menu déroulant propre */
        .dropdown-item { padding: 10px 20px; font-size: 0.9rem; color: #4b5563; }
        .dropdown-item:hover { background-color: #eff6ff; color: #2563eb; }
        .dropdown-menu { border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 12px; margin-top: 10px; }
        
        /* Pillule Utilisateur (Avatar) */
        .user-pill { background: #f3f4f6; padding: 5px 15px; border-radius: 50px; display: flex; align-items: center; gap: 10px; transition: background 0.2s; }
        .user-pill:hover { background: #e5e7eb; }

        /* Ajustements Mobile */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: #ffffff;
                margin-top: 10px;
                padding: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.05);
                border: 1px solid #f3f4f6;
            }
            .user-pill {
                width: 100%;
                justify-content: space-between;
                margin-top: 15px;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container-fluid px-lg-5">
    
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/dashboard">
        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="fw-bold text-dark lh-1">EIDIA</span>
            <span class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Absences</span>
        </div>
    </a>
    
    <button class="navbar-toggler shadow-none border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
        
        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
                    <i class="fas fa-chart-pie"></i>Dashboard
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-users"></i>Étudiants
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/students"><i class="fas fa-list me-2 text-primary"></i>Liste complète</a></li>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/import"><i class="fas fa-file-csv me-2 text-success"></i>Importer CSV</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-clock"></i>Absences
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/absences/monthly"><i class="fas fa-calendar-alt me-2 text-primary"></i>Rapport Mensuel</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/import/absences"><i class="fas fa-plus-circle me-2 text-warning"></i>Saisir / Importer</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-primary fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-shield-alt"></i>Admin
                </a>
                <ul class="dropdown-menu">
                    <li><h6 class="dropdown-header text-uppercase small">Système</h6></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/users"><i class="fas fa-users-cog me-2"></i>Utilisateurs</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/settings"><i class="fas fa-sliders-h me-2"></i>Configuration</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/my-child"><i class="fas fa-child"></i>Mon Enfant</a>
            </li>
        <?php endif; ?>

      </ul>
      
      <?php if(isset($_SESSION['user_role'])): ?>
      <div class="d-flex align-items-center mt-3 mt-lg-0">
          <div class="user-pill dropdown w-100">
             <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle w-100" data-bs-toggle="dropdown">
                 <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="d-block ms-2 text-start">
                        <div class="fw-bold" style="font-size: 0.85rem;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></div>
                        <div class="text-muted lh-1" style="font-size: 0.7rem; text-transform: uppercase;"><?= $_SESSION['user_role'] ?></div>
                    </div>
                 </div>
                 <i class="fas fa-chevron-down ms-auto text-muted small"></i>
             </a>
             <ul class="dropdown-menu dropdown-menu-end">
                 <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile"><i class="far fa-user-circle me-2"></i>Mon Profil</a></li>
                 <li><hr class="dropdown-divider"></li>
                 <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
             </ul>
          </div>
      </div>
      <?php endif; ?>
      
    </div>
  </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
                <div><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fs-4 me-3 text-danger"></i>
                <div><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</div>

<main class="flex-grow-1">