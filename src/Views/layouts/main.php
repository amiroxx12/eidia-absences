<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">EIDIA Absences</a>
    
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        
        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
            <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/dashboard">Tableau de bord</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/students">Étudiants</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/absences/monthly">
            <i class="fas fa-table"></i> Vue Mensuelle
            </a>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'operateur'])): ?>
             <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/import/absences">
                <i class="fas fa-calendar-times"></i> Saisie/Import Absences
                </a>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                    Administration
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/import">Import CSV Étudiants</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/users">Gestion Utilisateurs</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/settings">Configuration</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/my-child">Mon Enfant</a>
            </li>
        <?php endif; ?>

      </ul>
      
      <div class="d-flex align-items-center">
        <?php if(isset($_SESSION['user_role'])): ?>
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?> 
                <span class="badge bg-light text-primary ms-1"><?= ucfirst($_SESSION['user_role']) ?></span>
            </span>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">Déconnexion</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>