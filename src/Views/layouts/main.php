<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">EIDIA Absences</a>
    
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/dashboard">Tableau de bord</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/import">Importer CSV</a>
        </li>
      </ul>
      
      <div class="d-flex">
        <span class="navbar-text text-white me-3">
            Bonjour, <?php echo $_SESSION['user_name'] ?? 'Invité'; ?>
        </span>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">Déconnexion</a>
      </div>
    </div>
  </div>
</nav>