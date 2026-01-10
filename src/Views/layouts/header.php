<?php 
// On démarre la session ici pour être sûr qu'elle est dispo partout
if (session_status() === PHP_SESSION_NONE) session_start(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EIDIA Absences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/dashboard">
        <i class="fas fa-university me-2"></i>EIDIA Absences
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        
        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/dashboard"><i class="fas fa-tachometer-alt me-1"></i> Tableau de bord</a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="studentDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-graduate me-1"></i> Étudiants
                </a>
                <ul class="dropdown-menu border-0 shadow">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/students"><i class="fas fa-list me-2 text-primary"></i>Liste des étudiants</a></li>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/import"><i class="fas fa-file-import me-2 text-success"></i>Importer (CSV)</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="absenceDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar-check me-1"></i> Absences
                </a>
                <ul class="dropdown-menu border-0 shadow">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/absences/monthly"><i class="fas fa-table me-2 text-primary"></i>Rapport Mensuel</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/import/absences"><i class="fas fa-upload me-2 text-warning"></i>Saisir/Importer Absences</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cogs me-1"></i> Admin
                </a>
                <ul class="dropdown-menu border-0 shadow">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/users"><i class="fas fa-users-cog me-2"></i>Gestion Utilisateurs</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/settings"><i class="fas fa-sliders-h me-2"></i>Configuration</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/my-child"><i class="fas fa-child me-1"></i> Mon Enfant</a>
            </li>
        <?php endif; ?>

      </ul>
      
      <div class="d-flex align-items-center">
        <?php if(isset($_SESSION['user_role'])): ?>
            <div class="text-white me-3 text-end d-none d-lg-block">
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></div>
                <div class="badge bg-light text-primary text-uppercase" style="font-size: 0.7rem;"><?= $_SESSION['user_role'] ?></div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm rounded-circle" title="Déconnexion" style="width: 32px; height: 32px; padding: 4px;">
                <i class="fas fa-power-off"></i>
            </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container flex-grow-1">
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>